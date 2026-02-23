<?php

namespace App\Livewire\Admin\Roles;

use App\Services\MenuService;
use App\Services\RoleService;
use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Role')]
class RolesCreate extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;

    protected array $messages = [
        'name.required' => 'Nama role wajib diisi.',
        'name.string' => 'Nama role harus berupa teks.',
        'name.max' => 'Nama role maksimal 255 karakter.',
        'name.unique' => 'Nama role sudah digunakan.',
        'permissions.array' => 'Izin harus berupa daftar.',
        'permissions.*.exists' => 'Izin yang dipilih tidak valid.',
        'menus.array' => 'Menu harus berupa daftar.',
        'menus.*.exists' => 'Menu yang dipilih tidak valid.',
    ];

    protected array $validationAttributes = [
        'name' => 'Nama role',
        'permissions' => 'Izin',
        'menus' => 'Menu',
    ];

    protected MenuService $menuService;
    protected RoleService $roleService;

    public string $name = '';
    public array $permissions = [];
    public array $menus = [];
    public bool $menuOptionsReady = false;
    public array $menuPermissions = [];
    public array $childPermissions = [];
    public array $menuPermissionsLoaded = [];
    public bool $selectAllMenus = false;
    public bool $selectAllPermissions = false;

    public function boot(MenuService $menuService, RoleService $roleService): void
    {
        $this->menuService = $menuService;
        $this->roleService = $roleService;
    }

    public function mount(): void
    {
        $this->authorize('role.create');
        $this->setPageMeta(
            'Tambah Role Baru',
            'Tentukan nama peran, dan hak aksesnya.',
            [
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => true],
                ['label' => 'Role', 'url' => route('roles.index')],
                ['label' => 'Tambah', 'current' => true],
            ]
        );
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'menus' => ['nullable', 'array'],
            'menus.*' => ['exists:menus,id'],
        ];
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

    protected function loadMenus()
    {
        return Cache::remember('roles.menu-options', 600, function () {
            return $this->menuService->tree();
        });
    }

    public function loadMenuOptions(): void
    {
        $this->menuOptionsReady = true;
    }

    protected function loadMenusWithPermissions()
    {
        return Cache::remember('roles.menu-options-with-permissions', 600, function () {
            return $this->menuService->treeWithPermissions();
        });
    }

    public function loadMenuPermissions(int $menuId): void
    {
        if ($this->menuPermissionsLoaded[$menuId] ?? false) {
            return;
        }

        $menu = $this->menuService->query()->with(['permissions', 'children.permissions'])->find($menuId);
        if (! $menu) {
            return;
        }

        $this->menuPermissions[$menuId] = $menu->permissions
            ->map(fn($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ])
            ->values()
            ->all();

        foreach ($menu->children as $child) {
            $this->childPermissions[$child->id] = $child->permissions
                ->map(fn($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ])
                ->values()
                ->all();
        }

        $this->menuPermissionsLoaded[$menuId] = true;
    }

    public function updatedSelectAllMenus(bool $value): void
    {
        if (! $this->menuOptionsReady) {
            return;
        }

        if ($value) {
            $ids = $this->loadMenus()
                ->flatMap(fn($menu) => collect([$menu->id])->merge($menu->children->pluck('id')))
                ->values()
                ->all();
            $this->menus = $ids;
        } else {
            $this->menus = [];
        }
    }

    public function updatedSelectAllPermissions(bool $value): void
    {
        if (! $this->menuOptionsReady) {
            return;
        }

        if ($value) {
            $menus = $this->loadMenusWithPermissions();

            $ids = $menus->flatMap(function ($menu) {
                return $menu->permissions->pluck('id')
                    ->merge($menu->children->flatMap(fn($child) => $child->permissions->pluck('id')));
            })
                ->unique()
                ->values()
                ->all();

            $this->menuPermissionsLoaded = [];
            $this->menuPermissions = [];
            $this->childPermissions = [];
            foreach ($menus as $menu) {
                $this->menuPermissions[$menu->id] = $menu->permissions
                    ->map(fn($permission) => [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ])
                    ->values()
                    ->all();

                foreach ($menu->children as $child) {
                    $this->childPermissions[$child->id] = $child->permissions
                        ->map(fn($permission) => [
                            'id' => $permission->id,
                            'name' => $permission->name,
                        ])
                        ->values()
                        ->all();
                }

                $this->menuPermissionsLoaded[$menu->id] = true;
            }

            $this->permissions = $ids;
        } else {
            $this->permissions = [];
        }
    }

    public function updatedMenus(): void
    {
        if (! $this->menuOptionsReady) {
            return;
        }

        $total = $this->loadMenus()
            ->flatMap(fn($menu) => collect([$menu->id])->merge($menu->children->pluck('id')))
            ->unique()
            ->count();

        $this->selectAllMenus = $total > 0 && count(array_unique($this->menus)) === $total;
    }

    public function updatedPermissions(): void
    {
        if (! $this->menuOptionsReady) {
            return;
        }

        $total = $this->loadMenusWithPermissions()
            ->flatMap(function ($menu) {
                return $menu->permissions->pluck('id')
                    ->merge($menu->children->flatMap(fn($child) => $child->permissions->pluck('id')));
            })
            ->unique()
            ->count();

        $this->selectAllPermissions = $total > 0 && count(array_unique($this->permissions)) === $total;
    }

    public function save(): void
    {
        try {
            $data = $this->validate();

            $role = $this->roleService->store([
                'name' => $data['name'],
            ]);

            $this->roleService->syncPermissionsMenus($role->id, $this->permissions, $this->menus);

            session()->flash('toast', [
                'message' => 'Role baru berhasil ditambahkan.',
                'type' => 'success',
            ]);

            $this->redirectRoute('roles.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat menambahkan role.');
        }
    }

    public function render()
    {
        return view('livewire.admin.roles.create', [
            'menuOptions' => $this->menuOptionsReady ? $this->loadMenus() : collect(),
        ]);
    }
}
