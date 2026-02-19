<?php

namespace App\Livewire\Forms;

use App\Models\StockMovement;
use App\Services\StockMovementService;
use Livewire\Form;

class StockMovementForm extends Form
{
    public ?int $id = null;
    public ?int $material_id = null;
    public string $type = 'in';
    public $qty = null;
    public ?string $notes = null;

    public function rules(): array
    {
        return [
            'material_id' => ['required', 'integer', 'exists:mst_materials,id'],
            'type' => ['required', 'string', 'in:in,out,opname'],
            'qty' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function fillFromModel(StockMovement $movement): void
    {
        $this->id = $movement->id;
        $this->material_id = $movement->material_id;
        $this->type = $movement->type;
        $this->qty = $movement->qty;
        $this->notes = $movement->notes;
    }

    public function store(StockMovementService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(StockMovementService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'material_id' => $this->material_id,
            'type' => $this->type,
            'qty' => $this->qty,
            'notes' => $this->notes,
        ];
    }
}
