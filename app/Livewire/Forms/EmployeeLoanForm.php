<?php

namespace App\Livewire\Forms;

use App\Models\EmployeeLoan;
use App\Services\EmployeeLoanService;
use Livewire\Form;

class EmployeeLoanForm extends Form
{
    public ?int $id = null;
    public ?int $employee_id = null;
    public $amount = null;
    public ?string $loan_date = null;
    public string $status = 'open';
    public ?string $paid_at = null;
    public ?string $notes = null;

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:mst_employees,id'],
            'amount' => ['required', 'integer', 'min:0'],
            'loan_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:16'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.integer' => 'Karyawan tidak valid.',
            'employee_id.exists' => 'Karyawan yang dipilih tidak valid.',
            'amount.required' => 'Jumlah kasbon wajib diisi.',
            'amount.integer' => 'Jumlah kasbon harus berupa angka bulat.',
            'amount.min' => 'Jumlah kasbon minimal 0.',
            'loan_date.required' => 'Tanggal kasbon wajib diisi.',
            'loan_date.date' => 'Tanggal kasbon tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.max' => 'Status maksimal 16 karakter.',
            'paid_at.date' => 'Tanggal lunas tidak valid.',
            'notes.string' => 'Catatan harus berupa teks.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'employee_id' => 'Karyawan',
            'amount' => 'Jumlah kasbon',
            'loan_date' => 'Tanggal kasbon',
            'status' => 'Status',
            'paid_at' => 'Tanggal lunas',
            'notes' => 'Catatan',
        ];
    }

    public function fillFromModel(EmployeeLoan $loan): void
    {
        $this->id = $loan->id;
        $this->employee_id = $loan->employee_id;
        $this->amount = $loan->amount;
        $this->loan_date = $loan->loan_date?->format('Y-m-d');
        $this->status = $loan->status;
        $this->paid_at = $loan->paid_at?->format('Y-m-d');
        $this->notes = $loan->notes;
    }

    public function store(EmployeeLoanService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(EmployeeLoanService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'employee_id' => $this->employee_id,
            'amount' => $this->amount,
            'loan_date' => $this->loan_date,
            'status' => $this->status,
            'paid_at' => $this->paid_at,
            'notes' => $this->notes,
        ];
    }
}
