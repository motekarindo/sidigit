<?php

namespace App\Livewire\Forms;

use App\Models\Material;
use App\Services\MaterialService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class MaterialForm extends Form
{
    public ?int $id = null;

    #[Validate('required|string|max:128')]
    public string $name = '';

    #[Validate('required|integer|exists:mst_categories,id')]
    public ?int $category_id = null;

    #[Validate('required|integer|exists:mst_units,id')]
    public ?int $unit_id = null;

    #[Validate('nullable|integer|exists:mst_units,id')]
    public ?int $purchase_unit_id = null;

    #[Validate('nullable|string')]
    public ?string $description = null;

    #[Validate('nullable|integer|min:0')]
    public ?string $cost_price = null;

    #[Validate('nullable|numeric|min:0.01')]
    public ?string $conversion_qty = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $reorder_level = null;

    public function fillFromModel(Material $material): void
    {
        $this->id = $material->id;
        $this->name = $material->name;
        $this->category_id = $material->category_id;
        $this->unit_id = $material->unit_id;
        $this->purchase_unit_id = $material->purchase_unit_id;
        $this->description = $material->description;
        $this->cost_price = $material->cost_price !== null
            ? (string) $material->cost_price
            : null;
        $this->conversion_qty = $material->conversion_qty !== null
            ? (string) $material->conversion_qty
            : null;
        $this->reorder_level = $material->reorder_level !== null
            ? (string) $material->reorder_level
            : null;
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama material wajib diisi.',
            'name.string' => 'Nama material harus berupa teks.',
            'name.max' => 'Nama material maksimal 128 karakter.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.integer' => 'Kategori tidak valid.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'unit_id.required' => 'Satuan dasar wajib dipilih.',
            'unit_id.integer' => 'Satuan dasar tidak valid.',
            'unit_id.exists' => 'Satuan dasar yang dipilih tidak valid.',
            'purchase_unit_id.integer' => 'Satuan pembelian tidak valid.',
            'purchase_unit_id.exists' => 'Satuan pembelian yang dipilih tidak valid.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'cost_price.integer' => 'Harga pokok harus berupa angka bulat.',
            'cost_price.min' => 'Harga pokok minimal 0.',
            'conversion_qty.numeric' => 'Konversi harus berupa angka.',
            'conversion_qty.min' => 'Konversi minimal 0,01.',
            'reorder_level.numeric' => 'Batas minimum harus berupa angka.',
            'reorder_level.min' => 'Batas minimum minimal 0.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama material',
            'category_id' => 'Kategori',
            'unit_id' => 'Satuan dasar',
            'purchase_unit_id' => 'Satuan pembelian',
            'description' => 'Deskripsi',
            'cost_price' => 'Harga pokok',
            'conversion_qty' => 'Konversi',
            'reorder_level' => 'Batas minimum',
        ];
    }

    public function store(MaterialService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(MaterialService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'purchase_unit_id' => $this->purchase_unit_id,
            'description' => $this->description,
            'cost_price' => $this->cost_price,
            'conversion_qty' => $this->conversion_qty ?: 1,
            'reorder_level' => $this->reorder_level,
        ];
    }
}
