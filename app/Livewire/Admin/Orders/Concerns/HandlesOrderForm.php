<?php

namespace App\Livewire\Admin\Orders\Concerns;

use App\Services\CustomerService;
use App\Services\FinishService;
use App\Services\MaterialService;
use App\Services\OrderMaterialUsageService;
use App\Services\ProductService;
use App\Services\UnitService;
use Illuminate\Validation\ValidationException;

trait HandlesOrderForm
{
    public array $customers = [];
    public array $products = [];
    public array $materialsAll = [];
    public array $productMaterialMap = [];
    public array $units = [];
    public array $finishes = [];
    public array $dimensionUnitIds = [];

    protected function loadReferenceData(): void
    {
        $this->customers = app(CustomerService::class)->query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
            ])
            ->toArray();

        $this->productMaterialMap = app(ProductService::class)->getMaterialIdsByProduct();

        $this->products = app(ProductService::class)->query()
            ->with([
                'unit:id,name',
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'unit_id', 'sale_price', 'base_price', 'product_type'])
            ->map(function ($product) {
                $unitName = $product->unit?->name ?? 'Tanpa satuan';

                return [
                'id' => $product->id,
                'name' => $product->name,
                'label' => "{$product->name} - {$unitName}",
                'product_type' => (string) ($product->product_type ?: 'goods'),
                'category_id' => $product->category_id,
                'unit_id' => $product->unit_id,
                'sale_price' => (float) $product->sale_price,
                'base_price' => (float) $product->base_price,
                'material_ids' => $this->productMaterialMap[$product->id] ?? [],
            ];
            })
            ->toArray();

        $materials = app(MaterialService::class)->query()
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'unit_id', 'cost_price', 'roll_width_cm', 'roll_waste_percent']);

        $this->materialsAll = $materials->map(fn ($material) => [
            'id' => $material->id,
            'name' => $material->name,
            'category_id' => $material->category_id,
            'unit_id' => $material->unit_id,
            'cost_price' => (float) $material->cost_price,
            'roll_width_cm' => $material->roll_width_cm !== null ? (float) $material->roll_width_cm : null,
            'roll_waste_percent' => $material->roll_waste_percent !== null ? (float) $material->roll_waste_percent : 0,
        ])->toArray();

        $this->units = app(UnitService::class)->query()
            ->orderBy('name')
            ->get(['id', 'name', 'is_dimension'])
            ->map(fn ($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'is_dimension' => (bool) $unit->is_dimension,
            ])
            ->toArray();

        $this->dimensionUnitIds = collect($this->units)
            ->filter(fn ($unit) => !empty($unit['is_dimension']))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        $this->finishes = app(FinishService::class)->query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price'])
            ->map(fn ($finish) => [
                'id' => $finish->id,
                'name' => $finish->name,
                'price' => (float) $finish->price,
            ])
            ->toArray();
    }

    protected function newItem(): array
    {
        return [
            'product_id' => null,
            'material_id' => null,
            'material_ids' => [],
            'unit_id' => null,
            'qty' => 1,
            'length_cm' => null,
            'width_cm' => null,
            'price' => null,
            'price_source' => 'auto',
            'discount' => 0,
            'finish_ids' => [],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = $this->newItem();
    }

    public function updatedItems($value, $name): void
    {
        if (!is_string($name) || !str_contains($name, '.')) {
            return;
        }

        [$index, $field] = explode('.', $name, 2);
        $index = (int) $index;
        if ($field === 'product_id') {
            $this->handleProductChange($index, $value);
            $this->autoSetItemPrice($index);
            return;
        }

        if (in_array($field, ['length_cm', 'width_cm'], true)) {
            $this->autoSetItemPrice($index);
            return;
        }

        if ($field === 'price') {
            if ($value === null || $value === '') {
                $this->items[$index]['price_source'] = 'auto';
                $this->autoSetItemPrice($index);
            } else {
                $this->items[$index]['price_source'] = 'manual';
            }
            return;
        }
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function handleProductChange(int $index, $productId): void
    {
        $product = collect($this->products)->firstWhere('id', (int) $productId);
        if (!$product) {
            $this->items[$index]['product_id'] = null;
            $this->items[$index]['unit_id'] = null;
            $this->items[$index]['material_id'] = null;
            $this->items[$index]['material_ids'] = [];
            $this->items[$index]['price'] = null;
            $this->items[$index]['price_source'] = 'auto';
            return;
        }

        $this->items[$index]['product_id'] = (int) $productId;
        $this->items[$index]['unit_id'] = $product['unit_id'];
        $materialIds = $this->productMaterialMap[(int) $productId] ?? [];
        $this->items[$index]['material_ids'] = $materialIds;
        $this->items[$index]['material_id'] = !empty($materialIds) ? (int) $materialIds[0] : null;
        $this->items[$index]['price_source'] = $this->items[$index]['price_source'] ?? 'auto';

        if (!in_array((int) $product['unit_id'], $this->dimensionUnitIds, true)) {
            $this->items[$index]['length_cm'] = null;
            $this->items[$index]['width_cm'] = null;
        }
    }

    protected function autoSetItemPrice(int $index): void
    {
        if (!isset($this->items[$index])) {
            return;
        }

        $source = $this->items[$index]['price_source'] ?? 'auto';
        if ($source === 'manual') {
            return;
        }

        $price = $this->defaultItemPrice($this->items[$index]);
        $this->items[$index]['price'] = $price !== null ? (int) round($price) : null;
        $this->items[$index]['price_source'] = 'auto';
    }

    protected function defaultItemPrice(array $item): ?float
    {
        $product = collect($this->products)->firstWhere('id', (int) ($item['product_id'] ?? 0));
        if (!$product || empty($product['sale_price'])) {
            return null;
        }

        $basePrice = (float) $product['sale_price'];
        $lengthCm = $item['length_cm'] !== null ? (float) $item['length_cm'] : null;
        $widthCm = $item['width_cm'] !== null ? (float) $item['width_cm'] : null;

        if ($lengthCm && $widthCm) {
            $areaM2 = ($lengthCm / 100) * ($widthCm / 100);
            $billableAreaM2 = max(1, $areaM2);
            return $basePrice * $billableAreaM2;
        }

        return $basePrice;
    }

    public function addPayment(): void
    {
        $this->payments[] = [
            'amount' => 0,
            'method' => 'cash',
            'paid_at' => now()->format('Y-m-d H:i'),
            'notes' => null,
        ];
    }

    public function removePayment(int $index): void
    {
        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);
    }

    public function calculateItemPreview(array $item): array
    {
        $product = collect($this->products)->firstWhere('id', (int) ($item['product_id'] ?? 0));
        $material = collect($this->materialsAll)->firstWhere('id', (int) ($item['material_id'] ?? 0));
        $finishIds = $item['finish_ids'] ?? [];

        $finishTotal = collect($this->finishes)
            ->whereIn('id', $finishIds)
            ->sum('price');

        $qty = (float) ($item['qty'] ?? 1);
        $lengthCm = $item['length_cm'] !== null ? (float) $item['length_cm'] : null;
        $widthCm = $item['width_cm'] !== null ? (float) $item['width_cm'] : null;
        $discount = (float) ($item['discount'] ?? 0);

        $hppMaterial = 0;
        if ($material) {
            $cost = (float) ($material['cost_price'] ?? 0);
            if ($cost > 0) {
                $usageQty = app(OrderMaterialUsageService::class)->calculate(
                    $qty,
                    $lengthCm,
                    $widthCm,
                    isset($material['roll_width_cm']) ? (float) $material['roll_width_cm'] : null,
                    isset($material['roll_waste_percent']) ? (float) $material['roll_waste_percent'] : 0
                );
                $hppMaterial = $cost * $usageQty;
            }
        }

        $hpp = $hppMaterial + $finishTotal;
        if ($hpp <= 0 && $product && !empty($product['base_price'])) {
            $hpp = (float) $product['base_price'];
        }

        $price = $item['price'] !== null && $item['price'] !== ''
            ? (float) $item['price']
            : ($this->defaultItemPrice($item) ?? ($hpp > 0 ? $hpp * 1.3 : 0));

        $total = max(0, ($price * $qty) - $discount);

        return [
            'hpp' => $hpp,
            'price' => $price,
            'total' => $total,
        ];
    }

    public function totalsPreview(): array
    {
        $totalHpp = 0;
        $totalPrice = 0;
        $totalDiscount = 0;
        $grandTotal = 0;

        foreach ($this->items as $item) {
            $calc = $this->calculateItemPreview($item);
            $qty = (float) ($item['qty'] ?? 1);
            $discount = (float) ($item['discount'] ?? 0);

            $totalHpp += $calc['hpp'];
            $totalPrice += $calc['price'] * $qty;
            $totalDiscount += $discount;
            $grandTotal += $calc['total'];
        }

        return [
            'total_hpp' => $totalHpp,
            'total_price' => $totalPrice,
            'total_discount' => $totalDiscount,
            'grand_total' => $grandTotal,
        ];
    }

    protected function validateItemMaterialRequirements(array $items): void
    {
        $errors = [];

        foreach ($items as $index => $item) {
            $product = collect($this->products)->firstWhere('id', (int) ($item['product_id'] ?? 0));
            if (!$product) {
                continue;
            }

            $productType = (string) ($product['product_type'] ?? 'goods');
            $allowedMaterialIds = collect($product['material_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
            $materialId = isset($item['material_id']) && $item['material_id'] !== ''
                ? (int) $item['material_id']
                : null;

            if ($productType === 'goods' && !empty($allowedMaterialIds) && !$materialId) {
                $errors["items.$index.material_id"] = 'Bahan wajib dipilih untuk produk barang ini.';
                continue;
            }

            if ($materialId && !empty($allowedMaterialIds) && !in_array($materialId, $allowedMaterialIds, true)) {
                $errors["items.$index.material_id"] = 'Bahan tidak sesuai dengan mapping bahan produk yang dipilih.';
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}
