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

    #[Validate('nullable|numeric|min:0')]
    public ?string $price = null;

    #[Validate('nullable|integer|exists:mst_units,id')]
    public ?int $unit_id = null;

    #[Validate('boolean')]
    public bool $is_active = true;

    public function fillFromModel(Finish $finish): void
    {
        $this->id = $finish->id;
        $this->name = $finish->name;
        $this->price = $finish->price !== null ? (string) $finish->price : null;
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
