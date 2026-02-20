<?php

namespace App\Livewire\Admin\EmployeeLoans;

use App\Livewire\BaseTable;
use App\Livewire\Forms\EmployeeLoanForm;
use App\Models\Employee;
use App\Services\EmployeeLoanService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected EmployeeLoanService $service;

    public EmployeeLoanForm $form;

    public function boot(EmployeeLoanService $service): void
    {
        $this->service = $service;
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
        return Employee::orderBy('name')->get();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
        $this->form->loan_date = now()->format('Y-m-d');
        $this->form->status = 'open';
    }

    protected function loadForm(int $id): void
    {
        $loan = $this->service->find($id);
        $this->form->fillFromModel($loan);
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Kasbon berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat kasbon.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Kasbon berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui kasbon.');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Kasbon berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus kasbon.');
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
            $this->dispatch('toast', message: 'Kasbon terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus kasbon terpilih.');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.employee-loans.form';
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
            ['label' => 'Tambah Kasbon', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            [
                'label' => 'Jumlah',
                'field' => 'amount',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->amount, 0, ',', '.'),
            ],
            ['label' => 'Tanggal', 'field' => 'loan_date', 'sortable' => false],
            ['label' => 'Status', 'field' => 'status', 'sortable' => false],
            ['label' => 'Lunas Pada', 'field' => 'paid_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
