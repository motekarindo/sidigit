<?php

namespace App\Livewire\Forms;

use App\Models\Unit;
use App\Services\UnitService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UnitForm extends Form
{
    public ?int $id = null;

    #[Validate('required|string|min:3')]
    public string $name = '';

    #[Validate('boolean')]
    public bool $is_dimension = false;

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.string' => 'Nama satuan harus berupa teks.',
            'name.min' => 'Nama satuan minimal 3 karakter.',
            'is_dimension.boolean' => 'Pilihan ukuran tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama satuan',
            'is_dimension' => 'Ukuran',
        ];
    }

    public function fillFromModel(Unit $unit): void
    {
        $this->id = $unit->id;
        $this->name = $unit->name;
        $this->is_dimension = (bool) $unit->is_dimension;
    }

    public function store(UnitService $service): void
    {
        $this->validate();

        $service->store([
            'name' => $this->name,
            'is_dimension' => $this->is_dimension,
        ]);
    }

    public function update(UnitService $service): void
    {
        $this->validate();

        $service->update($this->id, [
            'name' => $this->name,
            'is_dimension' => $this->is_dimension,
        ]);
    }
}
