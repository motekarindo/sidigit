<?php

namespace App\Livewire\Forms;

use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Validation\Rule;
use Livewire\Form;

class MenuForm extends Form
{
    public ?int $id = null;
    public string $name = '';
    public ?int $parent_id = null;
    public ?string $route_name = null;
    public ?string $icon = null;
    public int $order = 0;

    protected function rules(): array
    {
        $uniqueRoute = Rule::unique(Menu::class, 'route_name');
        if (! empty($this->id)) {
            $uniqueRoute = $uniqueRoute->ignore($this->id);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'route_name' => ['nullable', 'string', 'max:255', $uniqueRoute],
            'icon' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama menu wajib diisi.',
            'name.string' => 'Nama menu harus berupa teks.',
            'name.max' => 'Nama menu maksimal 255 karakter.',
            'parent_id.integer' => 'Menu induk tidak valid.',
            'parent_id.exists' => 'Menu induk yang dipilih tidak valid.',
            'route_name.max' => 'Route name maksimal 255 karakter.',
            'route_name.unique' => 'Route name sudah digunakan.',
            'icon.max' => 'Ikon maksimal 255 karakter.',
            'order.required' => 'Urutan wajib diisi.',
            'order.integer' => 'Urutan harus berupa angka.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama menu',
            'parent_id' => 'Menu induk',
            'route_name' => 'Route name',
            'icon' => 'Ikon',
            'order' => 'Urutan',
        ];
    }

    public function fillFromModel(Menu $menu): void
    {
        $this->id = $menu->id;
        $this->name = $menu->name;
        $this->parent_id = $menu->parent_id;
        $this->route_name = $menu->route_name;
        $this->icon = $menu->icon;
        $this->order = $menu->order;
    }

    public function store(MenuService $service): void
    {
        $this->validate();

        $service->store([
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'route_name' => $this->route_name,
            'icon' => $this->icon,
            'order' => $this->order,
        ]);
    }

    public function update(MenuService $service): void
    {
        $this->validate();

        $service->update($this->id, [
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'route_name' => $this->route_name,
            'icon' => $this->icon,
            'order' => $this->order,
        ]);
    }
}
