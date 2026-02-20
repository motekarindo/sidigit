<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class OrderRepository
{
    public function query(): Builder
    {
        return Order::query();
    }

    public function queryTrashed(): Builder
    {
        return Order::onlyTrashed();
    }

    public function findOrFail(int $id): Order
    {
        return Order::query()->findOrFail($id);
    }

    public function findTrashed(int $id): Order
    {
        return Order::withTrashed()->findOrFail($id);
    }

    public function create(array $data): Order
    {
        return Order::query()->create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }
}
