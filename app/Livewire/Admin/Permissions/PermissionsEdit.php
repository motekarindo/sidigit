<?php

namespace App\Livewire\Admin\Permissions;

use App\Models\Menu;
use App\Models\Permission;
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

    public Permission $permission;
    public string $name = '';
    public string $slug = '';
    public ?int $menu_id = null;

    public function mount(Permission $permission): void
    {
        $this->authorize('permission.edit');

        $this->permission = $permission;

        $this->name = $permission->name;
        $this->slug = $permission->slug;
        $this->menu_id = $permission->menu_id;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique(Permission::class, 'slug')->ignore($this->permission->id)],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ];
    }

    public function update(): void
    {
        $data = $this->validate();

        try {
            $this->permission->update($data);

            session()->flash('success', 'Permission berhasil diperbarui.');

            $this->redirectRoute('permissions.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat memperbarui permission.');
        }
    }

    public function render()
    {
        return view('livewire.admin.permissions.edit', [
            'menus' => Menu::orderBy('name')->get(),
        ])->layoutData([
            'title' => 'Edit Permission',
        ]);
    }
}
