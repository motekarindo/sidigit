<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductionJob;
use App\Models\ProductionJobLog;
use App\Models\Role;
use App\Repositories\ProductionJobRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductionJobService
{
    protected ProductionJobRepository $repository;

    public function __construct(ProductionJobRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query()
            ->with([
                'order:id,order_no,status',
                'orderItem:id,order_id,product_id,qty',
                'orderItem.product:id,name',
                'assignedRole:id,name,slug',
            ]);
    }

    public function find(int $id): ProductionJob
    {
        return $this->query()->findOrFail($id);
    }

    public function syncByOrderStatus(Order $order): void
    {
        if ((string) $order->status !== 'produksi') {
            return;
        }

        $order->loadMissing(['items.product']);

        foreach ($order->items as $item) {
            $job = $this->repository->query()
                ->where('order_item_id', $item->id)
                ->first();

            if ($job) {
                continue;
            }

            $job = $this->repository->create([
                'branch_id' => $order->branch_id,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'status' => ProductionJob::STATUS_ANTRIAN,
                'notes' => 'Job produksi otomatis dibuat dari order status Produksi.',
            ]);

            $this->createLog($job, 'created', null, ProductionJob::STATUS_ANTRIAN, 'Job produksi dibuat otomatis.');
        }
    }

    public function assignRole(int $jobId, ?int $roleId): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        if ($roleId !== null) {
            $roleExists = Role::query()->where('id', $roleId)->exists();
            if (! $roleExists) {
                throw ValidationException::withMessages([
                    'assign_role_id' => 'Role penanggung jawab tidak valid.',
                ]);
            }
        }

        $this->repository->update($job, [
            'assigned_role_id' => $roleId,
        ]);

        $roleName = $roleId ? (Role::query()->find($roleId)?->name ?? 'Role') : 'Tanpa role';
        $this->createLog($job, 'assigned', $job->status, $job->status, 'Assign role: ' . $roleName);

        return $job->fresh(['assignedRole']);
    }

    public function markInProgress(int $jobId, ?string $note = null): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        $this->ensureTransitionAllowed($job->status, [
            ProductionJob::STATUS_ANTRIAN,
            ProductionJob::STATUS_IN_PROGRESS,
        ]);

        return $this->transition($job, ProductionJob::STATUS_IN_PROGRESS, 'in_progress', $note ?: 'Job diproses.');
    }

    public function markSelesai(int $jobId, ?string $note = null): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        $this->ensureTransitionAllowed($job->status, [ProductionJob::STATUS_IN_PROGRESS]);

        return $this->transition($job, ProductionJob::STATUS_SELESAI, 'finished', $note ?: 'Produksi item selesai.');
    }

    public function moveToQc(int $jobId, ?string $note = null): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        $this->ensureTransitionAllowed($job->status, [ProductionJob::STATUS_SELESAI]);

        return $this->transition($job, ProductionJob::STATUS_QC, 'to_qc', $note ?: 'Item masuk tahap QC.');
    }

    public function qcPass(int $jobId, ?string $note = null): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        $this->ensureTransitionAllowed($job->status, [ProductionJob::STATUS_QC]);

        return $this->transition($job, ProductionJob::STATUS_SIAP_DIAMBIL, 'qc_pass', $note ?: 'QC lulus.');
    }

    public function qcFail(int $jobId, ?string $note = null): ProductionJob
    {
        $job = $this->repository->findOrFail($jobId);

        $this->ensureTransitionAllowed($job->status, [ProductionJob::STATUS_QC]);

        return $this->transition($job, ProductionJob::STATUS_IN_PROGRESS, 'qc_fail', $note ?: 'QC gagal. Kembali ke produksi.');
    }

    public function recentLogsForJob(int $jobId, int $limit = 15): Collection
    {
        return ProductionJobLog::query()
            ->with('changedByUser:id,name')
            ->where('production_job_id', $jobId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    protected function transition(ProductionJob $job, string $toStatus, string $event, ?string $note = null): ProductionJob
    {
        $fromStatus = (string) $job->status;

        $this->repository->update($job, [
            'status' => $toStatus,
            'notes' => $note ?: $job->notes,
        ]);

        $job->refresh();

        $this->createLog($job, $event, $fromStatus, $toStatus, $note);
        $this->syncOrderStatusFromJobs($job->order()->firstOrFail());

        return $job->fresh(['order', 'assignedRole', 'orderItem.product']);
    }

    protected function syncOrderStatusFromJobs(Order $order): void
    {
        $statuses = $this->repository->query()
            ->where('order_id', $order->id)
            ->pluck('status');

        if ($statuses->isEmpty()) {
            return;
        }

        $targetStatus = 'produksi';

        if ($statuses->every(fn ($status) => $status === ProductionJob::STATUS_SIAP_DIAMBIL)) {
            $targetStatus = 'siap';
        } elseif ($statuses->every(fn ($status) => in_array($status, [ProductionJob::STATUS_QC, ProductionJob::STATUS_SIAP_DIAMBIL], true))) {
            $targetStatus = 'qc';
        }

        if ((string) $order->status === $targetStatus) {
            return;
        }

        $oldStatus = (string) $order->status;
        $order->update(['status' => $targetStatus]);

        $order->statusLogs()->create([
            'status' => $targetStatus,
            'changed_by' => auth()->id(),
            'note' => "Status sinkron dari progres produksi ({$oldStatus} -> {$targetStatus}).",
        ]);
    }

    protected function createLog(ProductionJob $job, string $event, ?string $fromStatus, ?string $toStatus, ?string $note = null): void
    {
        ProductionJobLog::query()->create([
            'branch_id' => $job->branch_id,
            'production_job_id' => $job->id,
            'order_id' => $job->order_id,
            'event' => $event,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
            'changed_by' => auth()->id(),
        ]);
    }

    protected function ensureTransitionAllowed(string $currentStatus, array $allowedStatuses): void
    {
        if (in_array($currentStatus, $allowedStatuses, true)) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => 'Transisi status produksi tidak valid untuk kondisi saat ini.',
        ]);
    }
}
