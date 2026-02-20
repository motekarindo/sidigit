<?php

namespace App\Livewire\Forms;

use App\Models\Supplier;
use App\Services\SupplierService;
use Livewire\Form;

class SupplierForm extends Form
{
    public ?int $id = null;

    public string $name = '';
    public ?string $on_behalf = null;
    public ?string $address = null;
    public string $industry = '';
    public string $phone_number = '';
    public ?string $email = null;
    public ?string $rekening_number = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'on_behalf' => ['nullable', 'string', 'max:128'],
            'address' => ['nullable', 'string', 'max:255'],
            'industry' => ['required', 'string', 'max:128'],
            'phone_number' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:128'],
            'rekening_number' => ['nullable', 'string', 'max:128'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama supplier wajib diisi.',
            'name.string' => 'Nama supplier harus berupa teks.',
            'name.max' => 'Nama supplier maksimal 128 karakter.',
            'on_behalf.string' => 'Atas nama harus berupa teks.',
            'on_behalf.max' => 'Atas nama maksimal 128 karakter.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 255 karakter.',
            'industry.required' => 'Bidang usaha wajib diisi.',
            'industry.string' => 'Bidang usaha harus berupa teks.',
            'industry.max' => 'Bidang usaha maksimal 128 karakter.',
            'phone_number.required' => 'No. telepon wajib diisi.',
            'phone_number.string' => 'No. telepon harus berupa teks.',
            'phone_number.max' => 'No. telepon maksimal 32 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 128 karakter.',
            'rekening_number.string' => 'No. rekening harus berupa teks.',
            'rekening_number.max' => 'No. rekening maksimal 128 karakter.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama supplier',
            'on_behalf' => 'Atas nama',
            'address' => 'Alamat',
            'industry' => 'Bidang usaha',
            'phone_number' => 'No. telepon',
            'email' => 'Email',
            'rekening_number' => 'No. rekening',
        ];
    }

    public function fillFromModel(Supplier $supplier): void
    {
        $this->id = $supplier->id;
        $this->name = $supplier->name;
        $this->on_behalf = $supplier->on_behalf;
        $this->address = $supplier->address;
        $this->industry = $supplier->industry;
        $this->phone_number = $supplier->phone_number;
        $this->email = $supplier->email;
        $this->rekening_number = $supplier->rekening_number;
    }

    public function store(SupplierService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(SupplierService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'on_behalf' => $this->on_behalf,
            'address' => $this->address,
            'industry' => $this->industry,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'rekening_number' => $this->rekening_number,
        ];
    }
}
