<?php

namespace App\Livewire\Admin\Orders;

use App\Livewire\BaseTable;
use App\Services\OrderService;

class TrashedTable extends BaseTable
{
    protected OrderService $service;

    public function boot(OrderService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch(
            $this->service->queryTrashed(),
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

    public function restore(int $id): void
    {
        try {
            $this->service->restore($id);
            $this->dispatch('toast', message: 'Order berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan order.', type: 'error');
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
            $this->dispatch('toast', message: 'Order terpilih berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan order terpilih.', type: 'error');
        }
    }

    public function goIndex(): void
    {
        $this->redirectRoute('orders.index');
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
            ['label' => 'Dihapus pada', 'field' => 'deleted_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
