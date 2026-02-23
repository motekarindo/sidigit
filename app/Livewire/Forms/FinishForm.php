<?php

namespace App\Livewire\Forms;

use App\Models\Finish;
use App\Services\FinishService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class FinishForm extends Form
{
    public ?int $id = null;

    #[Validate('required|string|max:128')]
    public string $name = '';

    #[Validate('required|integer|min:0')]
    public ?string $price = null;

    #[Validate('nullable|integer|exists:mst_units,id')]
    public ?int $unit_id = null;

    #[Validate('boolean')]
    public bool $is_active = true;

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama finishing wajib diisi.',
            'name.string' => 'Nama finishing harus berupa teks.',
            'name.max' => 'Nama finishing maksimal 128 karakter.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka bulat.',
            'price.min' => 'Harga minimal 0.',
            'unit_id.integer' => 'Satuan tidak valid.',
            'unit_id.exists' => 'Satuan yang dipilih tidak valid.',
            'is_active.boolean' => 'Status aktif tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama finishing',
            'price' => 'Harga',
            'unit_id' => 'Satuan',
            'is_active' => 'Status aktif',
        ];
    }

    public function fillFromModel(Finish $finish): void
    {
        $this->id = $finish->id;
        $this->name = $finish->name;
        $this->price = $finish->price !== null ? (string) (int) $finish->price : null;
        $this->unit_id = $finish->unit_id;
        $this->is_active = (bool) $finish->is_active;
    }

    public function store(FinishService $service): void
    {
        $this->validate();
        $service->store($this->payload());
    }

    public function update(FinishService $service): void
    {
        $this->validate();
        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'unit_id' => $this->unit_id,
            'is_active' => $this->is_active,
        ];
    }
}
