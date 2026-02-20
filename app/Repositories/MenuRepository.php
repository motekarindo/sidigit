<?php

namespace App\Repositories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

final class MenuRepository
{
    public function query(): Builder
    {
        return Menu::query();
    }

    public function all(): Collection
    {
        return Menu::query()->with('children')
            ->select(['id', 'name', 'route_name', 'parent_id', 'icon'])
            ->orderBy('order')
            ->get();
    }

    public function tree(): Collection
    {
        return Menu::query()->whereNull('parent_id')
            ->with('children.children')->orderBy('order')->get();
    }

    public function findOrFail(int $id): Menu
    {
        return Menu::query()->findOrFail($id);
    }

    public function create(array $data): Menu
    {
        return Menu::query()->create($data);
    }

    public function update(Menu $menu, array $data): Menu
    {
        $menu->update($data);

        return $menu;
    }

    public function delete(Menu $menu): void
    {
        $menu->delete();
    }
}
