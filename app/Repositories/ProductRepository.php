<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function query(): Builder
    {
        return Product::query();
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'unit', 'productMaterials.material.unit'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Product
    {
        return Product::query()
            ->with(['category', 'unit', 'productMaterials.material.unit'])
            ->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function attachMaterials(Product $product, array $materials): Collection
    {
        $product->productMaterials()->delete();

        if (empty($materials)) {
            return collect();
        }

        $payload = collect($materials)->map(function ($material) {
            return [
                'material_id' => $material['material_id'],
                'quantity' => $material['quantity'] ?? 1,
            ];
        })->toArray();

        return $product->productMaterials()->createMany($payload);
    }

    public function materialIdsByProduct(): array
    {
        return \DB::table('mst_product_materials')
            ->select('product_id', 'material_id')
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('product_id')
            ->map(function ($rows) {
                return $rows->pluck('material_id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
            })
            ->toArray();
    }
}
