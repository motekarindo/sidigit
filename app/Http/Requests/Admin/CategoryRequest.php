<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category');
        $branchId = auth()->user()?->branch_id;

        return [
            'name' => [
                'required',
                'string',
                'max:64',
                Rule::unique('mst_categories', 'name')
                    ->where(fn ($query) => $query->where('branch_id', $branchId))
                    ->ignore($categoryId),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Kategori',
        ];
    }
}
