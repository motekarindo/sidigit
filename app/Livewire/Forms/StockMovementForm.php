<?php

namespace App\Livewire\Forms;

use App\Models\StockMovement;
use App\Services\StockMovementService;
use Livewire\Form;

class StockMovementForm extends Form
{
    public ?int $id = null;
    public ?int $material_id = null;
    public ?int $unit_id = null;
    public string $type = 'in';
    public $qty = null;
    public ?string $notes = null;

    public function rules(): array
    {
        $qtyRules = $this->type === 'opname'
            ? ['required', 'numeric', 'not_in:0']
            : ['required', 'numeric', 'min:0.01'];

        return [
            'material_id' => ['required', 'integer', 'exists:mst_materials,id'],
            'unit_id' => ['required', 'integer', 'exists:mst_units,id'],
            'type' => ['required', 'string', 'in:in,out,opname'],
            'qty' => $qtyRules,
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'material_id.required' => 'Bahan wajib dipilih.',
            'material_id.integer' => 'Bahan tidak valid.',
            'material_id.exists' => 'Bahan yang dipilih tidak valid.',
            'unit_id.required' => 'Satuan wajib dipilih.',
            'unit_id.integer' => 'Satuan tidak valid.',
            'unit_id.exists' => 'Satuan yang dipilih tidak valid.',
            'type.required' => 'Tipe stok wajib diisi.',
            'type.in' => 'Tipe stok tidak valid.',
            'qty.required' => 'Qty wajib diisi.',
            'qty.numeric' => 'Qty harus berupa angka.',
            'qty.min' => 'Qty minimal 0,01.',
            'qty.not_in' => 'Qty opname tidak boleh 0.',
            'notes.string' => 'Catatan harus berupa teks.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'material_id' => 'Bahan',
            'unit_id' => 'Satuan',
            'type' => 'Tipe stok',
            'qty' => 'Qty',
            'notes' => 'Catatan',
        ];
    }

    public function fillFromModel(StockMovement $movement, ?int $materialUnitId = null): void
    {
        $this->id = $movement->id;
        $this->material_id = $movement->material_id;
        $this->unit_id = $materialUnitId;
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
            'unit_id' => $this->unit_id,
            'type' => $this->type,
            'qty' => $this->qty,
            'notes' => $this->notes,
        ];
    }
}
