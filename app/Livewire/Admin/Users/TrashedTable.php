<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\BaseTable;
use App\Services\UserService;

class TrashedTable extends BaseTable
{
    protected UserService $service;

    public function boot(UserService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        return $this->applySearch($this->service->queryTrashed(), ['name', 'email', 'username']);
    }

    protected function resetForm(): void
    {
        // No modal form for trashed users.
    }

    protected function loadForm(int $id): void
    {
        // No modal form for trashed users.
    }

    public function restore(int $id): void
    {
        try {
            $this->service->restore($id);
            $this->dispatch('toast', message: 'User berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan user.', type: 'error');
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
            $this->dispatch('toast', message: 'User terpilih berhasil dipulihkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memulihkan user terpilih.', type: 'error');
        }
    }

    public function goIndex(): void
    {
        $this->redirectRoute('users.index');
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
            ['label' => 'Name', 'field' => 'name', 'sortable' => true],
            ['label' => 'Username', 'field' => 'username', 'sortable' => true],
            ['label' => 'Email', 'field' => 'email', 'sortable' => true],
            [
                'label' => 'Role',
                'field' => 'roles',
                'sortable' => false,
                'format' => function ($row) {
                    $roles = $row->roles->pluck('name')->join(', ');
                    return $roles !== '' ? $roles : '-';
                },
            ],
            ['label' => 'Dihapus pada', 'field' => 'deleted_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }
}
