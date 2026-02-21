<?php

namespace App\Livewire\Admin\Branches;

use App\Livewire\BaseTable;
use App\Livewire\Forms\BranchForm;
use App\Services\BranchService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected BranchService $service;

    public BranchForm $form;
    public ?string $currentLogo = null;
    public ?string $currentQris = null;

    public function boot(BranchService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch($this->service->query(), ['name', 'phone', 'email']);
    }

    protected function resetForm(): void
    {
        $this->form->reset();
        $this->form->is_main = false;
        $this->currentLogo = null;
        $this->currentQris = null;
    }

    protected function loadForm(int $id): void
    {
        $branch = $this->service->find($id);
        $this->form->fillFromModel($branch);
        $this->currentLogo = $branch->logo_path;
        $this->currentQris = $branch->qris_path;
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Cabang berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat cabang.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Cabang berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui cabang.');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Cabang berhasil dihapus.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, $e->getMessage() ?: 'Gagal menghapus cabang.');
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
            $this->dispatch('toast', message: 'Cabang terpilih berhasil dihapus.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, $e->getMessage() ?: 'Gagal menghapus cabang terpilih.');
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

    protected function formView(): ?string
    {
        return 'livewire.admin.branches.form';
    }

    protected function rowActions(): array
    {
        return [
            ['label' => 'Edit', 'method' => 'openEdit', 'class' => 'text-brand-500', 'icon' => 'pencil'],
            [
                'label' => 'Delete',
                'method' => 'confirmDelete',
                'class' => 'text-red-600',
                'icon' => 'trash-2',
                'visible' => fn ($row) => !$row->is_main,
            ],
        ];
    }

    protected function tableActions(): array
    {
        return [
            ['label' => 'Tambah Cabang', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Nama Cabang', 'field' => 'name', 'sortable' => true],
            ['label' => 'Telepon', 'field' => 'phone', 'sortable' => false],
            ['label' => 'Email', 'field' => 'email', 'sortable' => false],
            [
                'label' => 'Tipe',
                'field' => 'is_main',
                'sortable' => true,
                'format' => function ($row) {
                    return $row->is_main ? 'Induk' : 'Cabang';
                },
            ],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    protected function createModalWidth(): string
    {
        return '3xl';
    }

    protected function editModalWidth(): string
    {
        return '3xl';
    }

    protected function deleteModalWidth(): string
    {
        return '2xl';
    }

    protected function createModalTitle(): string
    {
        return 'Tambah Cabang';
    }

    protected function editModalTitle(): string
    {
        return 'Edit Cabang';
    }
}
