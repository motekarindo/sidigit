<?php

namespace App\Livewire\Admin\Employees;

use App\Livewire\Forms\EmployeeForm;
use App\Services\EmployeeService;
use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

#[Layout('layouts.app')]
#[Title('Tambah Karyawan')]
class EmployeesCreate extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;
    use WithFilePond;

    public EmployeeForm $form;
    public array $statuses = [];

    protected EmployeeService $service;

    public function boot(EmployeeService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('employee.create');
        $this->statuses = $this->service->statuses();
        $this->setPageMeta(
            'Tambah Karyawan',
            'Lengkapi informasi karyawan baru.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Karyawan', 'url' => route('employees.index')],
                ['label' => 'Tambah', 'current' => true],
            ]
        );
    }

    public function save(): void
    {
        try {
            $this->form->store($this->service);

            session()->flash('toast', ['message' => 'Karyawan berhasil ditambahkan.', 'type' => 'success']);
            $this->redirectRoute('employees.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Terjadi kesalahan saat menambahkan karyawan.');
        }
    }

    protected function toastValidation(ValidationException $e, ?string $fallback = null): void
    {
        $errors = $e->validator->errors()->all();
        if (!empty($errors)) {
            $message = "Periksa input:\n• " . implode("\n• ", $errors);
        } else {
            $message = $fallback ?: 'Periksa kembali input. Ada data yang belum sesuai.';
        }

        $this->dispatch('toast', message: $message, type: 'warning');
    }

    public function render()
    {
        return view('livewire.admin.employees.create');
    }
}
