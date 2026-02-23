<?php

namespace App\Livewire\Forms;

use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Validation\Rule;
use Livewire\Form;

class PermissionForm extends Form
{
    public ?int $id = null;
    public string $name = '';
    public string $slug = '';
    public ?int $menu_id = null;

    protected function rules(): array
    {
        $uniqueName = Rule::unique(Permission::class, 'name');
        $uniqueSlug = Rule::unique(Permission::class, 'slug');

        if (!empty($this->id)) {
            $uniqueName = $uniqueName->ignore($this->id);
            $uniqueSlug = $uniqueSlug->ignore($this->id);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'slug' => ['required', 'string', 'max:255', $uniqueSlug],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama permission wajib diisi.',
            'name.string' => 'Nama permission harus berupa teks.',
            'name.max' => 'Nama permission maksimal 255 karakter.',
            'name.unique' => 'Nama permission sudah digunakan.',
            'slug.required' => 'Slug wajib diisi.',
            'slug.string' => 'Slug harus berupa teks.',
            'slug.max' => 'Slug maksimal 255 karakter.',
            'slug.unique' => 'Slug sudah digunakan.',
            'menu_id.required' => 'Menu wajib dipilih.',
            'menu_id.exists' => 'Menu yang dipilih tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama permission',
            'slug' => 'Slug',
            'menu_id' => 'Menu',
        ];
    }

    public function fillFromModel(Permission $permission): void
    {
        $this->id = $permission->id;
        $this->name = $permission->name;
        $this->slug = $permission->slug;
        $this->menu_id = $permission->menu_id;
    }

    public function store(PermissionService $service): void
    {
        $this->validate();

        $service->store([
            'name' => $this->name,
            'slug' => $this->slug,
            'menu_id' => $this->menu_id,
        ]);

        $this->reset();
    }

    public function update(PermissionService $service): void
    {
        $this->validate();

        $service->update($this->id, [
            'name' => $this->name,
            'slug' => $this->slug,
            'menu_id' => $this->menu_id,
        ]);

        $this->reset();
    }
}
