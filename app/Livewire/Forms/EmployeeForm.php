<?php

namespace App\Livewire\Forms;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class EmployeeForm extends Form
{
    use WithFileUploads;

    public ?int $id = null;

    public string $name = '';
    public ?string $address = null;
    public ?string $phone_number = null;
    public ?string $email = null;
    public ?array $photo = [];
    public ?string $salary = null;
    public string $status = '';

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
                Rule::unique('mst_employees', 'email')
                    ->where(fn ($query) => $query->where('branch_id', $branchId))
                    ->ignore($this->id),
            ],
            'photo' => ['nullable', 'array'],
            'salary' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(EmployeeStatus::class)],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama karyawan wajib diisi.',
            'name.string' => 'Nama karyawan harus berupa teks.',
            'name.max' => 'Nama karyawan maksimal 32 karakter.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 128 karakter.',
            'phone_number.string' => 'No. telepon harus berupa teks.',
            'phone_number.max' => 'No. telepon maksimal 16 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 64 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'photo.array' => 'Foto tidak valid.',
            'salary.integer' => 'Gaji harus berupa angka bulat.',
            'salary.min' => 'Gaji minimal 0.',
            'status.required' => 'Status wajib dipilih.',
            'status.enum' => 'Status tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama karyawan',
            'address' => 'Alamat',
            'phone_number' => 'No. telepon',
            'email' => 'Email',
            'photo' => 'Foto',
            'salary' => 'Gaji',
            'status' => 'Status',
        ];
    }

    public function fillFromModel(Employee $employee): void
    {
        $this->id = $employee->id;
        $this->name = $employee->name;
        $this->address = $employee->address;
        $this->phone_number = $employee->phone_number;
        $this->email = $employee->email;
        $this->photo = [];
        $this->salary = $employee->salary !== null ? (string) $employee->salary : null;
        $this->status = $employee->status?->value ?? '';
    }

    public function store(EmployeeService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(EmployeeService $service): void
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
            'photo' => $this->resolvedPhoto(),
            'salary' => $this->salary,
            'status' => $this->status,
        ];
    }

    protected function resolvedPhoto(): ?TemporaryUploadedFile
    {
        if (empty($this->photo)) {
            return null;
        }

        $firstPhoto = $this->photo[0] ?? null;
        if (!is_array($firstPhoto)) {
            return null;
        }

        $tmpFilename = $firstPhoto['tmpFilename'] ?? $firstPhoto['path'] ?? null;
        if (empty($tmpFilename)) {
            return null;
        }

        return TemporaryUploadedFile::createFromLivewire($tmpFilename);
    }
}
