<?php

namespace App\Services;

use App\Models\Finish;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemFinish;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderRepository $repository
    ) {}

    public function query(): Builder
    {
        return $this->repository->query()->with(['customer']);
    }

    public function queryTrashed(): Builder
    {
        return $this->repository->queryTrashed()->with(['customer']);
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): Order
    {
        $items = $data['items'] ?? [];
        $payments = $data['payments'] ?? [];
        unset($data['items'], $data['payments']);

        return DB::transaction(function () use ($data, $items, $payments) {
            $data['order_no'] = $data['order_no'] ?? $this->generateOrderNo();
            $order = $this->repository->create($data);

            $this->syncItems($order, $items);
            $this->syncPayments($order, $payments);
            $this->recalculateTotals($order);
            $order->statusLogs()->create([
                'status' => $order->status,
                'changed_by' => auth()->id(),
                'note' => 'Order dibuat.',
            ]);

            return $order->fresh(['customer', 'items']);
        });
    }

    public function update(int $id, array $data): Order
    {
        $items = $data['items'] ?? [];
        $payments = $data['payments'] ?? [];
        unset($data['items'], $data['payments']);

        return DB::transaction(function () use ($id, $data, $items, $payments) {
            $order = $this->repository->findOrFail($id);
            $oldStatus = $order->status;
            $this->repository->update($order, $data);

            $this->clearItems($order);
            $this->syncItems($order, $items);

            $this->clearPayments($order);
            $this->syncPayments($order, $payments);

            $this->recalculateTotals($order);

            if ($oldStatus !== $order->status) {
                $order->statusLogs()->create([
                    'status' => $order->status,
                    'changed_by' => auth()->id(),
                    'note' => 'Status diperbarui.',
                ]);
            }

            return $order->fresh(['customer', 'items']);
        });
    }

    public function destroy(int $id): void
    {
        $order = $this->repository->findOrFail($id);
        $order->items()->delete();
        $order->payments()->delete();
        $this->repository->delete($order);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $orders = $this->repository->query()->whereIn('id', $ids)->get();
        foreach ($orders as $order) {
            $order->items()->delete();
            $order->payments()->delete();
            $this->repository->delete($order);
        }
    }

    public function restore(int $id): void
    {
        $order = $this->repository->findTrashed($id);
        $order->restore();
        $order->items()->withTrashed()->restore();
        $order->payments()->withTrashed()->restore();
    }

    public function restoreMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $orders = $this->repository->queryTrashed()->whereIn('id', $ids)->get();
        foreach ($orders as $order) {
            $order->restore();
            $order->items()->withTrashed()->restore();
            $order->payments()->withTrashed()->restore();
        }
    }

    public function find(int $id): Order
    {
        return $this->repository->query()
            ->with(['customer', 'items', 'items.product', 'items.material', 'items.finishes', 'payments'])
            ->findOrFail($id);
    }

    protected function syncItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id'] ?? null);
            if (!$product) {
                continue;
            }

            $material = !empty($item['material_id']) ? Material::find($item['material_id']) : null;
            $finishIds = $item['finish_ids'] ?? [];
            $finishTotal = $this->finishTotal($finishIds);

            [$hpp, $price, $total] = $this->calculateItemTotals($product, $material, $item, $finishTotal);

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'material_id' => $material?->id,
                'unit_id' => $item['unit_id'] ?? $product->unit_id,
                'qty' => $item['qty'] ?? 1,
                'length_cm' => $item['length_cm'] ?? null,
                'width_cm' => $item['width_cm'] ?? null,
                'hpp' => $hpp,
                'price' => $price,
                'discount' => $item['discount'] ?? 0,
                'total' => $total,
            ]);

            $this->syncFinishes($orderItem, $finishIds);
            $this->createStockMovement($orderItem, $material);
        }
    }

    protected function clearItems(Order $order): void
    {
        $order->items()->delete();
        StockMovement::query()
            ->where('ref_type', 'order')
            ->where('ref_id', $order->id)
            ->delete();
    }

    protected function syncFinishes(OrderItem $orderItem, array $finishIds): void
    {
        if (empty($finishIds)) {
            return;
        }

        $finishes = Finish::query()->whereIn('id', $finishIds)->get();
        foreach ($finishes as $finish) {
            OrderItemFinish::create([
                'order_item_id' => $orderItem->id,
                'finish_id' => $finish->id,
                'price' => $finish->price,
            ]);
        }
    }

    protected function syncPayments(Order $order, array $payments): void
    {
        foreach ($payments as $payment) {
            if (empty($payment['amount'])) {
                continue;
            }

            Payment::create([
                'order_id' => $order->id,
                'amount' => $payment['amount'],
                'method' => $payment['method'] ?? 'cash',
                'paid_at' => $payment['paid_at'] ?? now(),
                'notes' => $payment['notes'] ?? null,
            ]);
        }
    }

    protected function clearPayments(Order $order): void
    {
        $order->payments()->delete();
    }

    protected function calculateItemTotals(Product $product, ?Material $material, array $item, float $finishTotal): array
    {
        $qty = (float) ($item['qty'] ?? 1);
        $lengthCm = $item['length_cm'] !== null ? (float) $item['length_cm'] : null;
        $widthCm = $item['width_cm'] !== null ? (float) $item['width_cm'] : null;
        $discount = (float) ($item['discount'] ?? 0);

        $hppMaterial = $this->calculateMaterialCost($material, $qty, $lengthCm, $widthCm);
        $hpp = $hppMaterial + $finishTotal;

        if ($hpp <= 0 && $product->base_price > 0) {
            $hpp = (float) $product->base_price;
        }

        $price = $item['price'] !== null && $item['price'] !== ''
            ? (float) $item['price']
            : $this->defaultPrice($product, $hpp);

        $total = max(0, ($price * $qty) - $discount);

        return [$hpp, $price, $total];
    }

    protected function calculateMaterialCost(?Material $material, float $qty, ?float $lengthCm, ?float $widthCm): float
    {
        if (!$material) {
            return 0;
        }

        $cost = (float) $material->cost_price;
        if ($cost <= 0) {
            return 0;
        }

        if ($lengthCm && $widthCm) {
            $areaM2 = ($lengthCm / 100) * ($widthCm / 100);
            return $cost * $areaM2 * $qty;
        }

        return $cost * $qty;
    }

    protected function finishTotal(array $finishIds): float
    {
        if (empty($finishIds)) {
            return 0;
        }

        return (float) Finish::query()->whereIn('id', $finishIds)->sum('price');
    }

    protected function defaultPrice(Product $product, float $hpp): float
    {
        if ($product->sale_price > 0) {
            return (float) $product->sale_price;
        }

        if ($hpp > 0) {
            return $hpp * 1.3;
        }

        return 0;
    }

    protected function createStockMovement(OrderItem $orderItem, ?Material $material): void
    {
        if (!$material) {
            return;
        }

        $qty = (float) $orderItem->qty;
        $lengthCm = $orderItem->length_cm !== null ? (float) $orderItem->length_cm : null;
        $widthCm = $orderItem->width_cm !== null ? (float) $orderItem->width_cm : null;

        $usage = $lengthCm && $widthCm
            ? ($lengthCm / 100) * ($widthCm / 100) * $qty
            : $qty;

        StockMovement::create([
            'material_id' => $material->id,
            'type' => 'out',
            'qty' => $usage,
            'ref_type' => 'order',
            'ref_id' => $orderItem->order_id,
            'notes' => 'Pemakaian bahan untuk order',
        ]);
    }

    protected function recalculateTotals(Order $order): void
    {
        $order->refresh();

        $items = $order->items()->get();
        $totalHpp = (float) $items->sum('hpp');
        $totalPrice = (float) $items->sum(fn ($item) => (float) $item->price * (float) $item->qty);
        $totalDiscount = (float) $items->sum('discount');
        $grandTotal = (float) $items->sum('total');
        $paidAmount = (float) $order->payments()->sum('amount');

        $paymentStatus = $paidAmount <= 0 ? 'unpaid' : ($paidAmount < $grandTotal ? 'partial' : 'paid');

        $order->update([
            'total_hpp' => $totalHpp,
            'total_price' => $totalPrice,
            'total_discount' => $totalDiscount,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
        ]);
    }

    protected function generateOrderNo(): string
    {
        $date = now()->format('Ymd');
        $count = Order::query()->whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('ORD-%s-%04d', $date, $count);
    }
}
