<?php

namespace App\Livewire\Admin\Stocks;

use App\Livewire\BaseTable;
use App\Livewire\Forms\StockMovementForm;
use App\Models\Material;
use App\Models\Unit;
use App\Services\StockMovementService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected StockMovementService $service;

    public StockMovementForm $form;

    public string $type = 'in';

    public array $filters = [
        'source' => 'all', // all | manual | order | expense
    ];

    public function boot(StockMovementService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        $query = $this->applySearch(
            $this->service->queryByType($this->type, false),
            ['notes']
        );

        $source = $this->filters['source'] ?? 'all';
        if ($source === 'manual') {
            $query->where(function ($q) {
                $q->whereNull('ref_type')->orWhere('ref_type', 'manual');
            });
        } elseif (in_array($source, ['order', 'expense'], true)) {
            $query->where('ref_type', $source);
        }

        return $query;
    }

    public function getMaterialOptionsProperty()
    {
        return Material::orderBy('name')->get();
    }

    public function getUnitOptionsProperty()
    {
        $materialId = $this->form->material_id;
        if (!$materialId) {
            return Unit::orderBy('name')->get();
        }

        $material = Material::find($materialId);
        if (!$material) {
            return Unit::orderBy('name')->get();
        }

        $ids = array_filter([$material->unit_id, $material->purchase_unit_id]);
        if (empty($ids)) {
            return Unit::orderBy('name')->get();
        }

        return Unit::query()->whereIn('id', $ids)->orderBy('name')->get();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
        $this->form->type = $this->type;
    }

    public function updatedFormMaterialId($value): void
    {
        $material = Material::find($value);
        if ($material) {
            $this->form->unit_id = $material->purchase_unit_id ?: $material->unit_id;
        }
    }

    protected function loadForm(int $id): void
    {
        $movement = $this->service->find($id);
        $this->form->fillFromModel($movement);
    }

    public function create(): void
    {
        try {
            $this->form->type = $this->type;
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Stok berhasil dicatat.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal mencatat stok.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->type = $this->type;
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Stok berhasil diperbarui.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'warning');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui stok.');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Stok berhasil dihapus.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'warning');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus stok.');
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
            $this->dispatch('toast', message: 'Stok manual terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus stok terpilih.');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.stocks.form';
    }

    protected function filtersView(): ?string
    {
        return 'livewire.admin.stocks.filters';
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
            ['label' => $this->actionLabel(), 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Bahan', 'field' => 'material.name', 'sortable' => false],
            ['label' => 'Qty', 'field' => 'qty', 'sortable' => false],
            [
                'label' => 'Sumber',
                'view' => 'livewire.admin.stocks.columns.source',
                'sortable' => false,
            ],
            ['label' => 'Catatan', 'field' => 'notes', 'sortable' => false],
            ['label' => 'Dibuat Pada', 'field' => 'created_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    protected function createModalTitle(): string
    {
        return $this->actionLabel();
    }

    protected function actionLabel(): string
    {
        return match ($this->type) {
            'out' => 'Tambah Stok Keluar',
            'opname' => 'Tambah Stok Opname',
            default => 'Tambah Stok Masuk',
        };
    }

    public function openEdit(int $id)
    {
        if (!$this->allowManualAction($id)) {
            return;
        }

        $this->activeId = $id;
        $this->loadForm($id);

        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->showFormModal = true;

        $this->modalTitle = $this->editModalTitle();
        $this->modalActionLabel = $this->editModalActionLabel();
        $this->modalActionMethod = $this->editModalActionMethod();
        $this->modalCancelLabel = $this->modalCancelLabel();
        $this->modalMaxWidth = $this->editModalWidth();
    }

    public function confirmDelete(int $id)
    {
        if (!$this->allowManualAction($id)) {
            return;
        }

        $this->activeId = $id;
        $this->showDeleteModal = true;
        $this->modalMaxWidth = $this->deleteModalWidth();
    }

    protected function allowManualAction(int $id): bool
    {
        $movement = $this->service->find($id);

        if (!empty($movement->ref_type) && $movement->ref_type !== 'manual') {
            $this->dispatch('toast', message: 'Pergerakan stok otomatis tidak bisa diubah dari modul ini.', type: 'warning');
            return false;
        }

        return true;
    }
}
