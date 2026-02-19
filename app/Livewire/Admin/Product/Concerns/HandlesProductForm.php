<?php

namespace App\Livewire\Admin\Product\Concerns;

use App\Models\Category;
use App\Models\Material;
use App\Models\Unit;

trait HandlesProductForm
{
    public array $categories = [];
    public array $units = [];
    public array $materialsByCategory = [];
    public array $materialsForSelectedCategory = [];
    public array $dimensionUnitIds = [];
    public bool $showDimensionFields = false;

    protected function loadReferenceData(): void
    {
        $this->categories = Category::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->toArray();

        $this->units = Unit::orderBy('name')
            ->get(['id', 'name', 'is_dimension'])
            ->map(fn ($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'is_dimension' => (bool) $unit->is_dimension,
            ])
            ->toArray();

        $this->materialsByCategory = $this->getMaterialsByCategory();

        $this->dimensionUnitIds = collect($this->units)
            ->filter(fn ($unit) => !empty($unit['is_dimension']))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();
    }

    protected function refreshMaterialsForCategory(?int $categoryId = null): void
    {
        $categoryKey = $categoryId ?? $this->category_id;
        $categoryKey = $categoryKey ? 'cat_' . (string) $categoryKey : null;

        $this->materialsForSelectedCategory = $categoryKey && isset($this->materialsByCategory[$categoryKey])
            ? $this->materialsByCategory[$categoryKey]
            : [];
    }

    public function reloadMaterials(): void
    {
        $this->materialsByCategory = $this->getMaterialsByCategory();
        $this->materials = [];
        $this->refreshMaterialsForCategory($this->category_id ? (int) $this->category_id : null);
    }

    protected function syncDimensionFields(bool $resetValues = false): void
    {
        $unitId = $this->unit_id ? (int) $this->unit_id : null;
        $shouldShow = $unitId && in_array($unitId, $this->dimensionUnitIds, true);

        $this->showDimensionFields = (bool) $shouldShow;

        if (!$shouldShow && $resetValues) {
            $this->length_cm = null;
            $this->width_cm = null;
        }
    }

    public function handleUnitChange($value): void
    {
        $this->unit_id = $value ? (int) $value : null;
        $this->syncDimensionFields(true);
    }

    protected function getMaterialsByCategory(): array
    {
        return Material::query()
            ->with('unit')
            ->orderBy('name')
            ->get()
            ->groupBy('category_id')
            ->mapWithKeys(function ($materials, $categoryId) {
                return [
                    'cat_' . (string) $categoryId => $materials->map(function (Material $material) {
                        return [
                            'id' => (string) $material->id,
                            'name' => $material->name,
                            'unit' => optional($material->unit)->name,
                        ];
                    })->values()->toArray(),
                ];
            })
            ->toArray();
    }
}
