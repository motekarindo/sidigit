<?php

namespace App\Livewire\Admin\Orders\Concerns;

use App\Models\Customer;
use App\Models\Finish;
use App\Models\Material;
use App\Models\Product;
use App\Models\Unit;

trait HandlesOrderForm
{
    public array $customers = [];
    public array $products = [];
    public array $materialsAll = [];
    public array $materialsByCategory = [];
    public array $units = [];
    public array $finishes = [];
    public array $dimensionUnitIds = [];

    protected function loadReferenceData(): void
    {
        $this->customers = Customer::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
            ])
            ->toArray();

        $this->products = Product::orderBy('name')
            ->get(['id', 'name', 'category_id', 'unit_id', 'sale_price', 'base_price'])
            ->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'unit_id' => $product->unit_id,
                'sale_price' => (float) $product->sale_price,
                'base_price' => (float) $product->base_price,
            ])
            ->toArray();

        $materials = Material::orderBy('name')
            ->get(['id', 'name', 'category_id', 'unit_id', 'cost_price']);

        $this->materialsAll = $materials->map(fn ($material) => [
            'id' => $material->id,
            'name' => $material->name,
            'category_id' => $material->category_id,
            'unit_id' => $material->unit_id,
            'cost_price' => (float) $material->cost_price,
        ])->toArray();

        $this->materialsByCategory = $materials
            ->groupBy('category_id')
            ->map(fn ($items) => $items->map(fn ($material) => [
                'id' => $material->id,
                'name' => $material->name,
                'category_id' => $material->category_id,
                'unit_id' => $material->unit_id,
                'cost_price' => (float) $material->cost_price,
            ])->values()->toArray())
            ->toArray();

        $this->units = Unit::orderBy('name')
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

        $this->finishes = Finish::query()
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
            'category_id' => null,
            'material_id' => null,
            'unit_id' => null,
            'qty' => 1,
            'length_cm' => null,
            'width_cm' => null,
            'price' => null,
            'discount' => 0,
            'finish_ids' => [],
            'allow_all_materials' => false,
        ];
    }

    public function addItem(): void
    {
        $this->items[] = $this->newItem();
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
            $this->items[$index]['category_id'] = null;
            $this->items[$index]['unit_id'] = null;
            $this->items[$index]['material_id'] = null;
            return;
        }

        $this->items[$index]['product_id'] = (int) $productId;
        $this->items[$index]['category_id'] = $product['category_id'];
        $this->items[$index]['unit_id'] = $product['unit_id'];

        if (!in_array((int) $product['unit_id'], $this->dimensionUnitIds, true)) {
            $this->items[$index]['length_cm'] = null;
            $this->items[$index]['width_cm'] = null;
        }
    }

    public function toggleMaterialScope(int $index): void
    {
        $this->items[$index]['allow_all_materials'] = !empty($this->items[$index]['allow_all_materials']) ? false : true;
        $this->items[$index]['material_id'] = null;
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
                $hppMaterial = ($lengthCm && $widthCm)
                    ? ($cost * ($lengthCm / 100) * ($widthCm / 100) * $qty)
                    : ($cost * $qty);
            }
        }

        $hpp = $hppMaterial + $finishTotal;
        if ($hpp <= 0 && $product && !empty($product['base_price'])) {
            $hpp = (float) $product['base_price'];
        }

        $price = $item['price'] !== null && $item['price'] !== ''
            ? (float) $item['price']
            : ($product && !empty($product['sale_price']) ? (float) $product['sale_price'] : ($hpp > 0 ? $hpp * 1.3 : 0));

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
}
