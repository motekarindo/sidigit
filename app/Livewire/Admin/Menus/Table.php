<?php

namespace App\Livewire\Admin\Menus;

use App\Helpers\IconHelper;
use App\Livewire\BaseTable;
use App\Livewire\Forms\MenuForm;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    protected MenuService $service;

    public MenuForm $form;
    
    public string $iconSearch = '';

    public function boot(MenuService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        // Get only parent menus (where parent_id is null)
        return $this->applySearch(
            Menu::query()->whereNull('parent_id')->with('children'),
            ['name', 'route_name']
        )->orderBy('order');
    }

    public function getParentMenuOptionsProperty()
    {
        return Menu::query()
            ->when($this->activeId, fn($q) => $q->where('id', '!=', $this->activeId))
            ->orderBy('name')
            ->get();
    }

    public function getIconOptionsProperty(): array
    {
        return IconHelper::getIcons();
    }

    public function getFilteredIconOptionsProperty(): array
    {
        $icons = $this->iconOptions;
        $search = strtolower($this->iconSearch ?? '');
        
        if (empty($search)) {
            return array_slice($icons, 0, 50);
        }
        
        $filtered = [];
        foreach ($icons as $key => $label) {
            if (str_contains(strtolower($key), $search) || str_contains(strtolower($label), $search)) {
                $filtered[$key] = $label;
            }
        }
        
        return array_slice($filtered, 0, 50);
    }

    public function selectIcon(string $iconName): void
    {
        $this->form->icon = $iconName;
    }

    protected function resetForm(): void
    {
        $this->form->reset();
    }

    protected function loadForm(int $id): void
    {
        $menu = $this->service->find($id);
        $this->form->fillFromModel($menu);
    }

    public function create(): void
    {
        try {
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Menu berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal membuat menu.', type: 'error');
        }
    }

    public function update(): void
    {
        try {
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Menu berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui menu.', type: 'error');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Menu berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus menu.', type: 'error');
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
            $this->dispatch('toast', message: 'Menu terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus menu terpilih.', type: 'error');
        }
    }

    public function moveUp(int $id): void
    {
        try {
            $this->service->moveUp($id);
            $this->dispatch('toast', message: 'Urutan menu berhasil dinaikkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal mengubah urutan menu.', type: 'error');
        }
    }

    public function moveDown(int $id): void
    {
        try {
            $this->service->moveDown($id);
            $this->dispatch('toast', message: 'Urutan menu berhasil diturunkan.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal mengubah urutan menu.', type: 'error');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.menus.form';
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
            ['label' => 'Tambah Menu', 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
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
            ['label' => 'Icon', 'field' => 'icon', 'sortable' => false],
            ['label' => 'Route', 'field' => 'route_name', 'sortable' => false],
            ['label' => 'Urutan', 'field' => 'order', 'sortable' => true],
            ['label' => '', 'field' => 'actions', 'sortable' => false, 'view' => 'livewire.admin.menus.columns.actions'],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    protected function createModalWidth(): string
    {
        return '2xl';
    }

    protected function editModalWidth(): string
    {
        return '2xl';
    }

    protected function deleteModalWidth(): string
    {
        return '2xl';
    }

    protected function createModalTitle(): string
    {
        return 'Tambah Menu';
    }

    protected function editModalTitle(): string
    {
        return 'Edit Menu';
    }

    public function render()
    {
        $rows = $this->applySorting($this->query())->paginate($this->perPage);

        return view('livewire.admin.menus.hierarchical-table', [
            'rows' => $rows,
            'columns' => $this->columns(),
            'tableActions' => $this->tableActions(),
            'rowActions' => $this->rowActions(),
            'bulkActions' => $this->bulkActions(),
            'formView' => $this->formView(),
            'filtersView' => $this->filtersView(),
        ]);
    }
}
