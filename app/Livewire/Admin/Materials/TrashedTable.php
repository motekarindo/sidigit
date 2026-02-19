<?php

namespace App\Livewire\Admin\Materials;

use App\Livewire\BaseTable;
use App\Services\MaterialService;

class TrashedTable extends BaseTable
{
    protected MaterialService $service;

    public function boot(MaterialService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch($this->service->queryTrashed(), ['name', 'description']);
    }

    protected function resetForm(): void
    {
        // No modal form for trashed materials.
    }

    protected function loadForm(int $id): void
    {
        // No modal form for trashed materials.
    }

    public function restore(int $id): void
    {
        try {
            $this->service->restore($id);
            $this->dispatch('toast', message: 'Bahan berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan bahan.', type: 'error');
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
            $this->dispatch('toast', message: 'Bahan terpilih berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan bahan terpilih.', type: 'error');
        }
    }

    public function goIndex(): void
    {
        $this->redirectRoute('materials.index');
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
            ['label' => 'Material', 'field' => 'name', 'sortable' => true],
            ['label' => 'Kategori', 'field' => 'category.name', 'sortable' => false],
            ['label' => 'Satuan', 'field' => 'unit.name', 'sortable' => false],
            ['label' => 'Reorder Level', 'field' => 'reorder_level', 'sortable' => false],
            [
                'label' => 'Deskripsi',
                'field' => 'description',
                'sortable' => false,
                'format' => fn($row) => $row->description ? \Illuminate\Support\Str::limit($row->description, 60) : '–',
            ],
            ['label' => 'Dihapus pada', 'field' => 'deleted_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
