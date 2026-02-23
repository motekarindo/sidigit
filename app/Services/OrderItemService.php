<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Repositories\OrderItemRepository;
use Illuminate\Database\Eloquent\Builder;

class OrderItemService
{
    protected OrderItemRepository $repository;

    public function __construct(OrderItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query();
    }

    public function find(int $id): OrderItem
    {
        return $this->repository->findOrFail($id);
    }
}
