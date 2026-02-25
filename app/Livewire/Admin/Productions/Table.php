<?php

namespace App\Livewire\Admin\Productions;

use App\Livewire\BaseTable;
use App\Models\ProductionJob;
use App\Models\Role;
use App\Services\ProductionJobService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;

class Table extends BaseTable
{
    use AuthorizesRequests;

    protected ProductionJobService $service;

    public string $sortField = 'production_jobs.updated_at';
    public string $sortDirection = 'desc';

    public array $filters = [
        'status' => 'all',
        'assigned_role_id' => null,
    ];

    public string $modalMode = 'assign'; // assign | qc_fail | history
    public ?int $assign_role_id = null;
    public ?string $qc_fail_note = null;
    public array $historyLogs = [];

    public function boot(ProductionJobService $service): void
    {
        $this->service = $service;
    }

    protected function query()
    {
        $query = $this->service->query()
            ->leftJoin('orders', 'orders.id', '=', 'production_jobs.order_id')
            ->leftJoin('order_items', 'order_items.id', '=', 'production_jobs.order_item_id')
            ->leftJoin('mst_products', 'mst_products.id', '=', 'order_items.product_id')
            ->leftJoin('roles', 'roles.id', '=', 'production_jobs.assigned_role_id')
            ->select('production_jobs.*')
            ->selectRaw('orders.order_no as order_no')
            ->selectRaw('mst_products.name as product_name')
            ->selectRaw('order_items.qty as item_qty')
            ->selectRaw('roles.name as assigned_role_name');

        $query = $this->applySearch($query, ['orders.order_no', 'mst_products.name', 'roles.name']);

        $status = $this->filters['status'] ?? 'all';
        if ($status !== 'all') {
            $query->where('production_jobs.status', $status);
        }

        $assignedRoleId = $this->filters['assigned_role_id'] ?? null;
        if (!empty($assignedRoleId)) {
            $query->where('production_jobs.assigned_role_id', (int) $assignedRoleId);
        }

        return $query;
    }

    protected function applySorting($query)
    {
        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function getRoleOptionsProperty(): array
    {
        return Role::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($role) => [
                'id' => $role->id,
                'label' => $role->name,
            ])
            ->all();
    }

    public function getStatusOptionsProperty(): array
    {
        $options = [['value' => 'all', 'label' => 'Semua Status']];

        foreach (ProductionJob::statusOptions() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    protected function resetForm(): void
    {
        $this->modalMode = 'assign';
        $this->assign_role_id = null;
        $this->qc_fail_note = null;
        $this->historyLogs = [];
    }

    protected function loadForm(int $id): void
    {
        $job = $this->service->find($id);
        $this->assign_role_id = $job->assigned_role_id ? (int) $job->assigned_role_id : null;
        $this->qc_fail_note = null;
    }

    public function openAssignModal(int $id): void
    {
        $this->authorize('production.assign');

        $this->activeId = $id;
        $this->loadForm($id);
        $this->modalMode = 'assign';
        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->showFormModal = true;
        $this->modalTitle = 'Assign Role Produksi';
        $this->modalActionLabel = 'Simpan Assign';
        $this->modalActionMethod = 'saveModal';
        $this->modalCancelLabel = 'Kembali';
        $this->modalMaxWidth = 'md';
    }

    public function openQcFailModal(int $id): void
    {
        $this->authorize('production.qc');

        $this->activeId = $id;
        $this->loadForm($id);
        $this->modalMode = 'qc_fail';
        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->showFormModal = true;
        $this->modalTitle = 'QC Gagal';
        $this->modalActionLabel = 'Kembalikan ke Produksi';
        $this->modalActionMethod = 'saveModal';
        $this->modalCancelLabel = 'Kembali';
        $this->modalMaxWidth = 'md';
    }

    public function openHistoryModal(int $id): void
    {
        $this->authorize('production.view');

        $this->activeId = $id;
        $this->modalMode = 'history';
        $this->historyLogs = $this->service
            ->recentLogsForJob($id, 20)
            ->map(fn ($log) => [
                'event' => $log->event,
                'from_status' => $log->from_status,
                'to_status' => $log->to_status,
                'note' => $log->note,
                'changed_by' => $log->changedByUser?->name,
                'created_at' => $log->created_at?->format('d M Y H:i'),
            ])
            ->all();

        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->showFormModal = true;
        $this->modalTitle = 'Riwayat Produksi';
        $this->modalActionLabel = 'Tutup';
        $this->modalActionMethod = 'closeModal';
        $this->modalCancelLabel = 'Kembali';
        $this->modalMaxWidth = '2xl';
    }

    public function saveModal(): void
    {
        try {
            if (!$this->activeId) {
                return;
            }

            if ($this->modalMode === 'assign') {
                $this->authorize('production.assign');
                $this->service->assignRole($this->activeId, $this->assign_role_id ?: null);
                $this->dispatch('toast', message: 'Assign role berhasil disimpan.', type: 'success');
                $this->closeModal();
                return;
            }

            if ($this->modalMode === 'qc_fail') {
                $this->authorize('production.qc');
                $this->service->qcFail($this->activeId, $this->qc_fail_note);
                $this->dispatch('toast', message: 'QC gagal. Job kembali ke Produksi.', type: 'warning');
                $this->closeModal();
                return;
            }
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menyimpan perubahan produksi.');
        }
    }

    public function markInProgress(int $id): void
    {
        $this->authorize('production.edit');

        try {
            $this->service->markInProgress($id);
            $this->dispatch('toast', message: 'Job dipindahkan ke In Progress.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal mengubah status job.');
        }
    }

    public function markSelesai(int $id): void
    {
        $this->authorize('production.edit');

        try {
            $this->service->markSelesai($id);
            $this->dispatch('toast', message: 'Job dipindahkan ke Selesai.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal mengubah status job.');
        }
    }

    public function moveToQc(int $id): void
    {
        $this->authorize('production.qc');

        try {
            $this->service->moveToQc($id);
            $this->dispatch('toast', message: 'Job dipindahkan ke tahap QC.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memindahkan job ke QC.');
        }
    }

    public function qcPass(int $id): void
    {
        $this->authorize('production.qc');

        try {
            $this->service->qcPass($id);
            $this->dispatch('toast', message: 'QC lulus. Job siap diambil.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal update hasil QC.');
        }
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

    public function resetFilters(): void
    {
        $this->filters = [
            'status' => 'all',
            'assigned_role_id' => null,
        ];
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.productions.form';
    }

    protected function filtersView(): ?string
    {
        return 'livewire.admin.productions.filters';
    }

    protected function rowActions(): array
    {
        return [
            [
                'label' => 'Assign Role',
                'method' => 'openAssignModal',
                'class' => 'text-brand-600',
                'icon' => 'user-cog',
                'visible' => fn ($row) => auth()->user()?->can('production.assign') ?? false,
            ],
            [
                'label' => 'Start',
                'method' => 'markInProgress',
                'class' => 'text-indigo-600',
                'icon' => 'play',
                'visible' => fn ($row) => ($row->status === ProductionJob::STATUS_ANTRIAN)
                    && (auth()->user()?->can('production.edit') ?? false),
            ],
            [
                'label' => 'Selesai',
                'method' => 'markSelesai',
                'class' => 'text-emerald-600',
                'icon' => 'check-check',
                'visible' => fn ($row) => ($row->status === ProductionJob::STATUS_IN_PROGRESS)
                    && (auth()->user()?->can('production.edit') ?? false),
            ],
            [
                'label' => 'Kirim QC',
                'method' => 'moveToQc',
                'class' => 'text-sky-600',
                'icon' => 'shield-check',
                'visible' => fn ($row) => ($row->status === ProductionJob::STATUS_SELESAI)
                    && (auth()->user()?->can('production.qc') ?? false),
            ],
            [
                'label' => 'QC Lulus',
                'method' => 'qcPass',
                'class' => 'text-emerald-600',
                'icon' => 'check',
                'visible' => fn ($row) => ($row->status === ProductionJob::STATUS_QC)
                    && (auth()->user()?->can('production.qc') ?? false),
            ],
            [
                'label' => 'QC Gagal',
                'method' => 'openQcFailModal',
                'class' => 'text-amber-600',
                'icon' => 'rotate-ccw',
                'visible' => fn ($row) => ($row->status === ProductionJob::STATUS_QC)
                    && (auth()->user()?->can('production.qc') ?? false),
            ],
            [
                'label' => 'Riwayat',
                'method' => 'openHistoryModal',
                'class' => 'text-gray-700 dark:text-gray-200',
                'icon' => 'history',
                'visible' => fn () => auth()->user()?->can('production.view') ?? false,
            ],
            [
                'label' => 'Lihat Order',
                'url' => fn ($row) => route('orders.edit', ['order' => $row->order_id]),
                'class' => 'text-brand-500',
                'icon' => 'eye',
            ],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Order No', 'view' => 'livewire.admin.productions.columns.order-link', 'sortable' => false],
            [
                'label' => 'Item',
                'field' => 'product_name',
                'sortable' => false,
                'format' => fn ($row) => ($row->product_name ?: '-') . ' (Qty: ' . number_format((float) ($row->item_qty ?? 0), 0, ',', '.') . ')',
            ],
            ['label' => 'Status', 'view' => 'livewire.admin.productions.columns.status', 'sortable' => false],
            [
                'label' => 'Penanggung Jawab',
                'field' => 'assigned_role_name',
                'sortable' => false,
                'format' => fn ($row) => $row->assigned_role_name ?: '-',
            ],
            [
                'label' => 'Update Terakhir',
                'field' => 'updated_at',
                'sortable' => false,
                'format' => fn ($row) => $row->updated_at?->format('d M Y H:i') ?: '-',
            ],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return false;
    }
}
