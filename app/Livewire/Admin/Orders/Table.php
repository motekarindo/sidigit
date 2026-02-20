<?php

namespace App\Livewire\Admin\Orders;

use App\Livewire\BaseTable;
use App\Services\OrderService;

class Table extends BaseTable
{
    protected OrderService $service;

    public function boot(OrderService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch(
            $this->service->query(),
            ['order_no']
        );
    }

    protected function resetForm(): void
    {
        // No modal form.
    }

    protected function loadForm(int $id): void
    {
        // No modal form.
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Order berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus order.');
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
            $this->dispatch('toast', message: 'Order terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus order terpilih.');
        }
    }

    public function goCreate(): void
    {
        $this->redirectRoute('orders.create');
    }

    public function goEdit(int $id): void
    {
        $this->redirectRoute('orders.edit', ['order' => $id]);
    }

    public function goTrashed(): void
    {
        $this->redirectRoute('orders.trashed');
    }

    protected function formView(): ?string
    {
        return null;
    }

    protected function rowActions(): array
    {
        return [
            ['label' => 'Edit', 'method' => 'goEdit', 'class' => 'text-brand-500', 'icon' => 'pencil'],
            ['label' => 'Delete', 'method' => 'confirmDelete', 'class' => 'text-red-600', 'icon' => 'trash-2'],
        ];
    }

    protected function tableActions(): array
    {
        return [
            ['label' => 'Tambah Order', 'method' => 'goCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
            ['label' => 'Trashed', 'method' => 'goTrashed', 'class' => 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700', 'icon' => 'archive'],
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
            ['label' => 'Order No', 'field' => 'order_no', 'sortable' => true],
            ['label' => 'Customer', 'field' => 'customer.name', 'sortable' => false],
            ['label' => 'Status', 'field' => 'status', 'sortable' => false],
            [
                'label' => 'Total',
                'field' => 'grand_total',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->grand_total, 0, ',', '.'),
            ],
            ['label' => 'Pembayaran', 'field' => 'payment_status', 'sortable' => false],
            ['label' => 'Diubah pada', 'field' => 'updated_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
