<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unitId = $this->route('unit');
        $branchId = auth()->user()?->branch_id;

        return [
            'name' => [
                'required',
                'string',
                'max:64',
                Rule::unique('mst_units', 'name')
                    ->where(fn ($query) => $query->where('branch_id', $branchId))
                    ->ignore($unitId),
            ],
            'is_dimension' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Satuan',
            'is_dimension' => 'Ukuran',
        ];
    }
}
