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
#[Title('Tambah Permission')]
class PermissionsCreate extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;

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

    public function mount(): void
    {
        $this->authorize('permission.create');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('permissions', 'slug')],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        try {
            $this->permissionService->store($data);

            session()->flash('success', 'Permission berhasil dibuat.');

            $this->redirectRoute('permissions.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat membuat permission.');
        }
    }

    public function render()
    {
        return view('livewire.admin.permissions.create', [
            'menus' => $this->menuService->parentOptions(),
        ])->layoutData([
            'title' => 'Tambah Permission',
        ]);
    }
}
