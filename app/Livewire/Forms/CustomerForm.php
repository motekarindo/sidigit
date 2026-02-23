<?php

namespace App\Livewire\Forms;

use App\Enums\CustomerMemberType;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CustomerForm extends Form
{
    public ?int $id = null;

    public string $name = '';
    public ?string $address = null;
    public ?string $phone_number = null;
    public ?string $email = null;
    public string $member_type = '';

    public function rules(): array
    {
        $branchId = auth()->user()?->branch_id;

        return [
            'name' => ['required', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:128'],
            'phone_number' => ['nullable', 'string', 'max:16'],
            'email' => [
                'nullable',
                'email',
                'max:64',
                Rule::unique('mst_customers', 'email')
                    ->where(fn ($query) => $query->where('branch_id', $branchId))
                    ->ignore($this->id),
            ],
            'member_type' => ['required', Rule::enum(CustomerMemberType::class)],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'name.string' => 'Nama pelanggan harus berupa teks.',
            'name.max' => 'Nama pelanggan maksimal 32 karakter.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 128 karakter.',
            'phone_number.string' => 'No. telepon harus berupa teks.',
            'phone_number.max' => 'No. telepon maksimal 16 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 64 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'member_type.required' => 'Tipe member wajib dipilih.',
            'member_type.enum' => 'Tipe member tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama pelanggan',
            'address' => 'Alamat',
            'phone_number' => 'No. telepon',
            'email' => 'Email',
            'member_type' => 'Tipe member',
        ];
    }

    public function fillFromModel(Customer $customer): void
    {
        $this->id = $customer->id;
        $this->name = $customer->name;
        $this->address = $customer->address;
        $this->phone_number = $customer->phone_number;
        $this->email = $customer->email;
        $this->member_type = $customer->member_type?->value ?? '';
    }

    public function store(CustomerService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(CustomerService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'member_type' => $this->member_type,
        ];
    }
}
