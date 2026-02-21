<?php

namespace App\Livewire\Forms;

use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class BranchForm extends Form
{
    use WithFileUploads;

    public ?int $id = null;
    public string $name = '';
    public string $address = '';
    public string $phone = '';
    public string $email = '';
    public bool $is_main = false;
    public ?array $logo = [];
    public ?array $qris = [];

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('branches', 'name')->ignore($this->id),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'logo' => ['nullable', 'array'],
            'qris' => ['nullable', 'array'],
            'is_main' => ['nullable', 'boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama cabang wajib diisi.',
            'name.string' => 'Nama cabang harus berupa teks.',
            'name.max' => 'Nama cabang maksimal 150 karakter.',
            'name.unique' => 'Nama cabang sudah digunakan.',
            'address.string' => 'Alamat cabang harus berupa teks.',
            'address.max' => 'Alamat cabang maksimal 500 karakter.',
            'phone.string' => 'No telepon harus berupa teks.',
            'phone.max' => 'No telepon maksimal 30 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'logo.array' => 'Logo tidak valid.',
            'qris.array' => 'QRIS tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama Cabang',
            'address' => 'Alamat Cabang',
            'phone' => 'No Telepon',
            'email' => 'Email',
            'logo' => 'Logo Cabang',
            'qris' => 'QRIS',
            'is_main' => 'Cabang Induk',
        ];
    }

    public function fillFromModel(Branch $branch): void
    {
        $this->id = $branch->id;
        $this->name = $branch->name ?? '';
        $this->address = $branch->address ?? '';
        $this->phone = $branch->phone ?? '';
        $this->email = $branch->email ?? '';
        $this->is_main = (bool) $branch->is_main;
        $this->logo = [];
        $this->qris = [];
    }

    public function store(BranchService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(BranchService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'is_main' => $this->is_main,
            'logo' => $this->resolvedLogo(),
            'qris' => $this->resolvedQris(),
        ];
    }

    protected function resolvedLogo(): ?TemporaryUploadedFile
    {
        if (empty($this->logo)) {
            return null;
        }

        $firstFile = $this->logo[0] ?? null;
        if (!is_array($firstFile)) {
            return null;
        }

        $tmpFilename = $firstFile['tmpFilename'] ?? $firstFile['path'] ?? null;
        if (empty($tmpFilename)) {
            return null;
        }

        return TemporaryUploadedFile::createFromLivewire($tmpFilename);
    }

    protected function resolvedQris(): ?TemporaryUploadedFile
    {
        if (empty($this->qris)) {
            return null;
        }

        $firstFile = $this->qris[0] ?? null;
        if (!is_array($firstFile)) {
            return null;
        }

        $tmpFilename = $firstFile['tmpFilename'] ?? $firstFile['path'] ?? null;
        if (empty($tmpFilename)) {
            return null;
        }

        return TemporaryUploadedFile::createFromLivewire($tmpFilename);
    }
}
