<?php

namespace App\Livewire\Forms;

use App\Models\Expense;
use App\Services\ExpenseService;
use Livewire\Form;

class ExpenseForm extends Form
{
    public ?int $id = null;
    public string $type = 'general';
    public ?int $material_id = null;
    public ?int $supplier_id = null;
    public $qty = null;
    public $unit_cost = null;
    public $amount = null;
    public string $payment_method = 'cash';
    public ?string $expense_date = null;
    public ?string $notes = null;

    public function rules(): array
    {
        $rules = [
            'type' => ['required', 'string', 'in:material,general'],
            'payment_method' => ['required', 'string', 'max:32'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];

        if ($this->type === 'material') {
            $rules['material_id'] = ['required', 'integer', 'exists:mst_materials,id'];
            $rules['supplier_id'] = ['nullable', 'integer', 'exists:mst_suppliers,id'];
            $rules['qty'] = ['required', 'numeric', 'min:0.01'];
            $rules['unit_cost'] = ['required', 'numeric', 'min:0'];
            $rules['amount'] = ['nullable', 'numeric', 'min:0'];
        } else {
            $rules['amount'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function fillFromModel(Expense $expense): void
    {
        $this->id = $expense->id;
        $this->type = $expense->type;
        $this->material_id = $expense->material_id;
        $this->supplier_id = $expense->supplier_id;
        $this->qty = $expense->qty;
        $this->unit_cost = $expense->unit_cost;
        $this->amount = $expense->amount;
        $this->payment_method = $expense->payment_method;
        $this->expense_date = $expense->expense_date?->format('Y-m-d');
        $this->notes = $expense->notes;
    }

    public function store(ExpenseService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(ExpenseService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'type' => $this->type,
            'material_id' => $this->material_id,
            'supplier_id' => $this->supplier_id,
            'qty' => $this->qty,
            'unit_cost' => $this->unit_cost,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'expense_date' => $this->expense_date,
            'notes' => $this->notes,
        ];
    }
}
