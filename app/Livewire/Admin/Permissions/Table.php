<?php

namespace App\Livewire\Admin\Permissions;

use App\Livewire\BaseTable;
use App\Livewire\Forms\PermissionForm;
use App\Services\PermissionService;
use App\Services\MenuService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected PermissionService $service;
    protected MenuService $menuService;

    public PermissionForm $form;

    public array $filters = [
        'menu_id' => null,
        'created_at' => 'desc',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function boot(PermissionService $service, MenuService $menuService): void
    {
        $this->service = $service;
        $this->menuService = $menuService;
    }

    protected function query()
    {
        $query = $this->applySearch($this->service->query(), ['name', 'slug']);

        if (! empty($this->filters['menu_id'])) {
            $query->where('menu_id', $this->filters['menu_id']);
        }

        return $query;
    }

    public function getMenuOptionsProperty()
    {
        return $this->menuService->parentOptions();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
    }

    protected function loadForm(int $id): void
    {
        $permission = $this->service->find($id);
        $this->form->fillFromModel($permission);
    }


    public function create(): void
    {
        try {
            $this->form->store($this->service);

            $this->closeModal();
            $this->dispatch('toast', message: 'Permission berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal membuat permission.');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);

            $this->closeModal();
            $this->dispatch('toast', message: 'Permission berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memperbarui permission.');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Permission berhasil dihapus.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus permission.');
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
            $this->dispatch('toast', message: 'Permission terpilih berhasil dihapus.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menghapus permission terpilih.');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.permissions.form';
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
            ['label' => 'Tambah Permission', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Name', 'field' => 'name', 'sortable' => true],
            ['label' => 'Menu', 'field' => 'menu.name', 'sortable' => false],
            ['label' => 'Diubah pada', 'field' => 'updated_at', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    protected function filtersView(): ?string
    {
        return 'livewire.admin.permissions.filters';
    }

    public function updatedFilters(): void
    {
        $direction = $this->filters['created_at'] ?? 'desc';
        $this->sortField = 'created_at';
        $this->sortDirection = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'menu_id' => null,
            'created_at' => 'desc',
        ];
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    protected function createModalWidth(): string
    {
        return 'xl';
    }

    protected function editModalWidth(): string
    {
        return 'xl';
    }

    protected function deleteModalWidth(): string
    {
        return 'xl';
    }

    protected function createModalTitle(): string
    {
        return 'Tambah Permission';
    }

    protected function editModalTitle(): string
    {
        return 'Edit Permission';
    }
}
