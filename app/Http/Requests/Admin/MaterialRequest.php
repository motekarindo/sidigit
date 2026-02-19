<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'category_id' => ['required', 'exists:mst_categories,id'],
            'unit_id' => ['required', 'exists:mst_units,id'],
            'purchase_unit_id' => ['nullable', 'exists:mst_units,id'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'conversion_qty' => ['nullable', 'numeric', 'min:0.01'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Material',
            'category_id' => 'Kategori',
            'unit_id' => 'Satuan',
            'purchase_unit_id' => 'Satuan Pembelian',
            'description' => 'Deskripsi',
            'cost_price' => 'Harga Pokok',
            'conversion_qty' => 'Konversi Satuan',
            'reorder_level' => 'Batas Minimum',
        ];
    }
}
