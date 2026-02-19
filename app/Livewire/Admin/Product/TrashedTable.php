<?php

namespace App\Livewire\Admin\Product;

use App\Livewire\BaseTable;
use App\Services\ProductService;

class TrashedTable extends BaseTable
{
    protected ProductService $service;

    public function boot(ProductService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch(
            $this->service->queryTrashed(),
            ['sku', 'name']
        );
    }

    protected function resetForm(): void
    {
        // No modal form for products.
    }

    protected function loadForm(int $id): void
    {
        // No modal form for products.
    }

    public function restore(int $id): void
    {
        try {
            $this->service->restore($id);
            $this->dispatch('toast', message: 'Produk berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan produk.', type: 'error');
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
            $this->dispatch('toast', message: 'Produk terpilih berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan produk terpilih.', type: 'error');
        }
    }

    public function goIndex(): void
    {
        $this->redirectRoute('products.index');
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
            ['label' => 'SKU', 'field' => 'sku', 'sortable' => true],
            ['label' => 'Nama', 'field' => 'name', 'sortable' => true],
            ['label' => 'Kategori', 'field' => 'category.name', 'sortable' => false],
            ['label' => 'Satuan', 'field' => 'unit.name', 'sortable' => false],
            [
                'label' => 'Harga Pokok',
                'field' => 'base_price',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->base_price, 2, ',', '.'),
            ],
            [
                'label' => 'Harga Jual',
                'field' => 'sale_price',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->sale_price, 2, ',', '.'),
            ],
            ['label' => 'Dihapus pada', 'field' => 'deleted_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
