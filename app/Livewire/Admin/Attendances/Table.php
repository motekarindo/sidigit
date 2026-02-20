<?php

namespace App\Livewire\Admin\Attendances;

use App\Livewire\BaseTable;
use App\Livewire\Forms\EmployeeAttendanceForm;
use App\Services\EmployeeAttendanceService;
use App\Services\EmployeeService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected EmployeeAttendanceService $service;
    protected EmployeeService $employeeService;

    public EmployeeAttendanceForm $form;

    public function boot(EmployeeAttendanceService $service, EmployeeService $employeeService): void
    {
        $this->service = $service;
        $this->employeeService = $employeeService;
    }

    protected function query()
    {
        $query = $this->service->query();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    public function getEmployeeOptionsProperty()
    {
        return $this->employeeService->query()->orderBy('name')->get();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
        $this->form->attendance_date = now()->format('Y-m-d');
        $this->form->status = 'present';
    }

    protected function loadForm(int $id): void
    {
        $attendance = $this->service->find($id);
        $this->form->fillFromModel($attendance);
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Absensi berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat absensi.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Absensi berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui absensi.');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Absensi berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus absensi.');
        }
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Pilih minimal 1 data.', type: 'warning');
            return;
        }

        try {
            $this->service->destroyMany($this->selected);
            $this->selected = [];
            $this->selectAll = false;
            $this->closeModal();
            $this->dispatch('toast', message: 'Absensi terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus absensi terpilih.');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.attendances.form';
    }

    protected function rowActions(): array
    {
        return [
            ['label' => 'Edit', 'method' => 'openEdit', 'class' => 'text-brand-500', 'icon' => 'pencil'],
            ['label' => 'Delete', 'method' => 'confirmDelete', 'class' => 'text-red-600', 'icon' => 'trash-2'],
        ];
    }

    protected function tableActions(): array
    {
        return [
            ['label' => 'Tambah Absensi', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
        ];
    }

    protected function bulkActions(): array
    {
        return [
            'delete' => ['label' => 'Delete selected', 'method' => 'confirmBulkDelete'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Karyawan', 'field' => 'employee.name', 'sortable' => false],
            ['label' => 'Tanggal', 'field' => 'attendance_date', 'sortable' => false],
            ['label' => 'Check In', 'field' => 'check_in', 'sortable' => false],
            ['label' => 'Check Out', 'field' => 'check_out', 'sortable' => false],
            ['label' => 'Status', 'field' => 'status', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
