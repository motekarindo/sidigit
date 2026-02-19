<?php

namespace App\Livewire\Admin\Suppliers;

use App\Livewire\BaseTable;
use App\Services\SupplierService;

class TrashedTable extends BaseTable
{
    protected SupplierService $service;

    public function boot(SupplierService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch(
            $this->service->queryTrashed(),
            ['name', 'industry', 'phone_number', 'email', 'address']
        );
    }

    protected function resetForm(): void
    {
        // No modal form for trashed suppliers.
    }

    protected function loadForm(int $id): void
    {
        // No modal form for trashed suppliers.
    }

    public function restore(int $id): void
    {
        try {
            $this->service->restore($id);
            $this->dispatch('toast', message: 'Supplier berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan supplier.', type: 'error');
        }
    }

    public function bulkRestore(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Pilih minimal 1 data.', type: 'warning');
            return;
        }

        try {
            $this->service->restoreMany($this->selected);
            $this->selected = [];
            $this->selectAll = false;
            $this->dispatch('toast', message: 'Supplier terpilih berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan supplier terpilih.', type: 'error');
        }
    }

    public function goIndex(): void
    {
        $this->redirectRoute('suppliers.index');
    }

    protected function formView(): ?string
    {
        return null;
    }

    protected function rowActions(): array
    {
        return [
            ['label' => 'Restore', 'method' => 'restore', 'class' => 'text-success-600', 'icon' => 'rotate-ccw'],
        ];
    }

    protected function tableActions(): array
    {
        return [
            ['label' => 'Kembali', 'method' => 'goIndex', 'class' => 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700', 'icon' => 'arrow-left'],
        ];
    }

    protected function bulkActions(): array
    {
        return [
            'restore' => ['label' => 'Restore selected', 'method' => 'bulkRestore'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Nama', 'field' => 'name', 'sortable' => true],
            ['label' => 'Industri', 'field' => 'industry', 'sortable' => false],
            ['label' => 'Telepon', 'field' => 'phone_number', 'sortable' => false],
            ['label' => 'Email', 'field' => 'email', 'sortable' => false],
            ['label' => 'Dihapus pada', 'field' => 'deleted_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
