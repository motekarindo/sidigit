<?php

namespace App\Services;

use App\Models\Menu;
use App\Repositories\MenuRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MenuService
{
    protected MenuRepository $repository;

    public function __construct(MenuRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query()->with('parent');
    }

    public function tree(): Collection
    {
        return $this->repository->tree();
    }

    public function treeWithPermissions(): Collection
    {
        return $this->repository->query()
            ->whereNull('parent_id')
            ->with(['children.permissions', 'permissions'])
            ->orderBy('order')
            ->get();
    }

    public function find(int $id): Menu
    {
        return $this->repository->findOrFail($id);
    }

    public function store(array $data): Menu
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Menu
    {
        $menu = $this->repository->findOrFail($id);
        return $this->repository->update($menu, $data);
    }

    public function destroy(int $id): void
    {
        $menu = $this->repository->findOrFail($id);
        $this->repository->delete($menu);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->repository->query()->whereIn('id', $ids)->delete();
    }

    public function parentOptions(?int $excludeId = null)
    {
        $query = $this->repository->query()->orderBy('name');
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }
}
