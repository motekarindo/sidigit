<?php

namespace App\Livewire\Forms;

use App\Models\BankAccount;
use App\Services\BankAccountService;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BankAccountForm extends Form
{
    public ?int $id = null;

    public string $rekening_number = '';
    public string $account_name = '';
    public string $bank_name = '';

    public function rules(): array
    {
        $branchId = auth()->user()?->branch_id;

        return [
            'rekening_number' => [
                'required',
                'string',
                'max:18',
                Rule::unique('mst_bank_accounts', 'rekening_number')
                    ->where(fn ($query) => $query->where('branch_id', $branchId))
                    ->ignore($this->id),
            ],
            'account_name' => ['required', 'string', 'max:64'],
            'bank_name' => ['required', 'string', 'max:64'],
        ];
    }

    protected function messages(): array
    {
        return [
            'rekening_number.required' => 'Nomor rekening wajib diisi.',
            'rekening_number.string' => 'Nomor rekening harus berupa teks.',
            'rekening_number.max' => 'Nomor rekening maksimal 18 karakter.',
            'rekening_number.unique' => 'Nomor rekening sudah digunakan.',
            'account_name.required' => 'Nama pemilik rekening wajib diisi.',
            'account_name.string' => 'Nama pemilik rekening harus berupa teks.',
            'account_name.max' => 'Nama pemilik rekening maksimal 64 karakter.',
            'bank_name.required' => 'Nama bank wajib diisi.',
            'bank_name.string' => 'Nama bank harus berupa teks.',
            'bank_name.max' => 'Nama bank maksimal 64 karakter.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'rekening_number' => 'Nomor rekening',
            'account_name' => 'Nama pemilik',
            'bank_name' => 'Nama bank',
        ];
    }

    public function fillFromModel(BankAccount $bankAccount): void
    {
        $this->id = $bankAccount->id;
        $this->rekening_number = $bankAccount->rekening_number;
        $this->account_name = $bankAccount->account_name;
        $this->bank_name = $bankAccount->bank_name;
    }

    public function store(BankAccountService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(BankAccountService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'rekening_number' => $this->rekening_number,
            'account_name' => $this->account_name,
            'bank_name' => $this->bank_name,
        ];
    }
}
