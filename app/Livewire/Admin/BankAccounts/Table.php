<?php

namespace App\Livewire\Admin\BankAccounts;

use App\Livewire\BaseTable;
use App\Livewire\Forms\BankAccountForm;
use App\Services\BankAccountService;

class Table extends BaseTable
{
    protected BankAccountService $service;

    public BankAccountForm $form;

    public function boot(BankAccountService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch($this->service->query(), ['rekening_number', 'account_name', 'bank_name']);
    }

    protected function resetForm(): void
    {
        $this->form->reset();
    }

    protected function loadForm(int $id): void
    {
        $bankAccount = $this->service->find($id);
        $this->form->fillFromModel($bankAccount);
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Rekening berhasil dibuat.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat rekening.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Rekening berhasil diperbarui.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui rekening.');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Rekening berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus rekening.');
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
            $this->dispatch('toast', message: 'Rekening terpilih berhasil dihapus.', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus rekening terpilih.');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.bank-accounts.form';
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
            ['label' => 'Tambah Rekening', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Nomor Rekening', 'field' => 'rekening_number', 'sortable' => true],
            ['label' => 'Nama Pemilik', 'field' => 'account_name', 'sortable' => true],
            ['label' => 'Nama Bank', 'field' => 'bank_name', 'sortable' => true],
            ['label' => 'Diubah pada', 'field' => 'updated_at', 'sortable' => false],
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
        return '3xl';
    }

    protected function createModalTitle(): string
    {
        return 'Tambah Rekening Bank';
    }

    protected function editModalTitle(): string
    {
        return 'Edit Rekening Bank';
    }
}
