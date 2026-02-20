<?php

namespace App\Repositories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;

class OrderItemRepository
{
    public function query(): Builder
    {
        return OrderItem::query();
    }

    public function findOrFail(int $id): OrderItem
    {
        return OrderItem::query()->findOrFail($id);
    }

    public function create(array $data): OrderItem
    {
        return OrderItem::query()->create($data);
    }

    public function update(OrderItem $item, array $data): OrderItem
    {
        $item->update($data);

        return $item;
    }

    public function delete(OrderItem $item): void
    {
        $item->delete();
    }
}
