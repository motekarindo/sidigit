<?php

namespace App\Livewire\Admin\Permissions;

use App\Services\MenuService;
use App\Services\PermissionService;
use App\Traits\WithErrorToast;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Permission')]
class PermissionsEdit extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;

    public int $permissionId;
    protected MenuService $menuService;
    protected PermissionService $permissionService;
    public string $name = '';
    public string $slug = '';
    public ?int $menu_id = null;

    public function boot(MenuService $menuService, PermissionService $permissionService): void
    {
        $this->menuService = $menuService;
        $this->permissionService = $permissionService;
    }

    public function mount(int $permission): void
    {
        $this->authorize('permission.edit');

        $this->permissionId = $permission;
        $permissionModel = $this->permissionService->find($this->permissionId);

        $this->name = $permissionModel->name;
        $this->slug = $permissionModel->slug;
        $this->menu_id = $permissionModel->menu_id;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('permissions', 'slug')->ignore($this->permissionId)],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ];
    }

    public function update(): void
    {
        $data = $this->validate();

        try {
            $this->permissionService->update($this->permissionId, $data);

            session()->flash('success', 'Permission berhasil diperbarui.');

            $this->redirectRoute('permissions.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat memperbarui permission.');
        }
    }

    public function render()
    {
        return view('livewire.admin.permissions.edit', [
            'menus' => $this->menuService->parentOptions(),
        ])->layoutData([
            'title' => 'Edit Permission',
        ]);
    }
}
