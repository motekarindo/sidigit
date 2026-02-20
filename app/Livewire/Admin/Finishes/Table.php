<?php

namespace App\Livewire\Admin\Finishes;

use App\Livewire\BaseTable;
use App\Livewire\Forms\FinishForm;
use App\Services\FinishService;
use App\Services\UnitService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected FinishService $service;
    protected UnitService $unitService;

    public FinishForm $form;

    public function boot(FinishService $service, UnitService $unitService): void
    {
        $this->service = $service;
        $this->unitService = $unitService;
    }

    protected function query()
    {
        return $this->applySearch($this->service->query(), ['name']);
    }

    public function getUnitOptionsProperty()
    {
        return $this->unitService->query()->orderBy('name')->get();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
        $this->form->is_active = true;
    }

    protected function loadForm(int $id): void
    {
        $finish = $this->service->find($id);
        $this->form->fillFromModel($finish);
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Finishing berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat finishing.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Finishing berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui finishing.');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Finishing berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus finishing.');
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
            $this->dispatch('toast', message: 'Finishing terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus finishing terpilih.');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.finishes.form';
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
            ['label' => 'Tambah Finishing', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Nama', 'field' => 'name', 'sortable' => true],
            ['label' => 'Harga', 'field' => 'price', 'sortable' => false],
            ['label' => 'Satuan', 'field' => 'unit.name', 'sortable' => false],
            [
                'label' => 'Aktif',
                'field' => 'is_active',
                'sortable' => false,
                'format' => fn ($row) => $row->is_active ? 'Ya' : 'Tidak',
            ],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
