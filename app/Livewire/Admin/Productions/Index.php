<?php

namespace App\Livewire\Admin\Productions;

use App\Models\ProductionJob;
use App\Models\User;
use App\Services\ProductionJobService;
use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Produksi Kanban')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;

    public string $stage = ProductionJob::STAGE_PRODUKSI;
    public string $search = '';

    public bool $showQcFailModal = false;
    public ?int $qcFailJobId = null;
    public ?string $qcFailNote = null;

    public array $userRoleSlugs = [];
    protected ProductionJobService $service;

    public function boot(ProductionJobService $service): void
    {
        $this->service = $service;
    }

    public function mount(?string $stage = null): void
    {
        $this->authorize('production.view');

        $routeStage = request()->route('stage');
        $this->stage = $this->normalizeStage(
            $stage
                ?: (is_string($routeStage) ? $routeStage : null)
                ?: ($this->isDesainRoute() ? ProductionJob::STAGE_DESAIN : ProductionJob::STAGE_PRODUKSI)
        );

        $this->userRoleSlugs = auth()->user()?->roles()->pluck('slug')->all() ?? [];

        $this->setPageMeta(
            'Produksi Kanban',
            'Kelola task produksi per item order dalam board Kanban.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Produksi', 'url' => route('productions.produksi')],
                ['label' => $this->stageLabel, 'current' => true],
            ]
        );
    }

    public function getStageLabelProperty(): string
    {
        $labels = ProductionJob::stageOptions();

        return $labels[$this->stage] ?? ucfirst($this->stage);
    }

    public function getColumnsProperty(): array
    {
        return [
            ProductionJob::STATUS_ANTRIAN => ['label' => 'Antrian', 'accent' => 'border-gray-200 dark:border-gray-800'],
            ProductionJob::STATUS_IN_PROGRESS => ['label' => 'In Progress', 'accent' => 'border-blue-200 dark:border-blue-900/50'],
            ProductionJob::STATUS_SELESAI => ['label' => 'Selesai', 'accent' => 'border-emerald-200 dark:border-emerald-900/50'],
            ProductionJob::STATUS_QC => ['label' => 'QC', 'accent' => 'border-amber-200 dark:border-amber-900/50'],
            ProductionJob::STATUS_SIAP_DIAMBIL => ['label' => 'Siap Diambil', 'accent' => 'border-violet-200 dark:border-violet-900/50'],
        ];
    }

    public function getGroupedJobsProperty(): array
    {
        $jobs = $this->service->kanbanQuery($this->stage, $this->search)->get();
        $grouped = [];

        foreach (array_keys($this->columns) as $status) {
            $grouped[$status] = $jobs->where('status', $status)->values();
        }

        return $grouped;
    }

    public function goStage(string $stage): void
    {
        $target = $this->normalizeStage($stage);

        $this->redirectRoute(
            $target === ProductionJob::STAGE_DESAIN ? 'productions.desain' : 'productions.produksi'
        );
    }

    public function claim(int $jobId): void
    {
        $this->authorize('production.edit');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->claimJob($jobId, $actor);
            $this->dispatch('toast', message: 'Task berhasil diambil.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal mengambil task.');
        }
    }

    public function release(int $jobId): void
    {
        $this->authorize('production.edit');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->releaseJob($jobId, $actor);
            $this->dispatch('toast', message: 'Task berhasil dilepas ke antrian role.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal melepas task.');
        }
    }

    public function markInProgress(int $jobId): void
    {
        $this->authorize('production.edit');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->markInProgress($jobId, null, $actor);
            $this->dispatch('toast', message: 'Task dipindahkan ke In Progress.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memulai task.');
        }
    }

    public function markSelesai(int $jobId): void
    {
        $this->authorize('production.edit');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->markSelesai($jobId, null, $actor);
            $this->dispatch('toast', message: 'Task dipindahkan ke Selesai.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal mengubah status task.');
        }
    }

    public function moveToQc(int $jobId): void
    {
        $this->authorize('production.qc');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->moveToQc($jobId, null, $actor);
            $this->dispatch('toast', message: 'Task dipindahkan ke QC.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memindahkan task ke QC.');
        }
    }

    public function qcPass(int $jobId): void
    {
        $this->authorize('production.qc');

        try {
            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->qcPass($jobId, null, $actor);
            $this->dispatch('toast', message: 'QC lulus. Task siap diambil.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menyimpan hasil QC.');
        }
    }

    public function moveCard(int $jobId, string $toStatus): void
    {
        $actor = auth()->user();
        if (!$actor instanceof User) {
            abort(403);
        }

        $allowedStatuses = [
            ProductionJob::STATUS_ANTRIAN,
            ProductionJob::STATUS_IN_PROGRESS,
            ProductionJob::STATUS_SELESAI,
            ProductionJob::STATUS_QC,
            ProductionJob::STATUS_SIAP_DIAMBIL,
        ];

        if (!in_array($toStatus, $allowedStatuses, true)) {
            $this->dispatch('toast', message: 'Target kolom tidak valid.', type: 'warning');
            return;
        }

        $job = $this->service->find($jobId);
        $fromStatus = (string) $job->status;
        if ($fromStatus === $toStatus) {
            return;
        }

        try {
            if (in_array($toStatus, [ProductionJob::STATUS_QC, ProductionJob::STATUS_SIAP_DIAMBIL], true)) {
                $this->authorize('production.qc');
            } else {
                $this->authorize('production.edit');
            }

            if ($toStatus === ProductionJob::STATUS_IN_PROGRESS) {
                $this->service->markInProgress($jobId, null, $actor);
                $this->dispatch('toast', message: 'Task dipindahkan ke In Progress.', type: 'success');
                return;
            }

            if ($toStatus === ProductionJob::STATUS_SELESAI) {
                $this->service->markSelesai($jobId, null, $actor);
                $this->dispatch('toast', message: 'Task dipindahkan ke Selesai.', type: 'success');
                return;
            }

            if ($toStatus === ProductionJob::STATUS_QC) {
                $this->service->moveToQc($jobId, null, $actor);
                $this->dispatch('toast', message: 'Task dipindahkan ke QC.', type: 'success');
                return;
            }

            if ($toStatus === ProductionJob::STATUS_SIAP_DIAMBIL) {
                $this->service->qcPass($jobId, null, $actor);
                $this->dispatch('toast', message: 'Task dipindahkan ke Siap Diambil.', type: 'success');
                return;
            }

            $this->dispatch('toast', message: 'Drag ke kolom ini belum didukung.', type: 'warning');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memindahkan task via drag.');
        }
    }

    public function openQcFail(int $jobId): void
    {
        $this->authorize('production.qc');

        $this->qcFailJobId = $jobId;
        $this->qcFailNote = null;
        $this->showQcFailModal = true;
    }

    public function saveQcFail(): void
    {
        $this->authorize('production.qc');

        try {
            $validated = $this->validate([
                'qcFailNote' => ['required', 'string', 'min:5', 'max:500'],
            ], [
                'qcFailNote.required' => 'Catatan QC gagal wajib diisi.',
                'qcFailNote.min' => 'Catatan QC gagal minimal :min karakter.',
                'qcFailNote.max' => 'Catatan QC gagal maksimal :max karakter.',
            ], [
                'qcFailNote' => 'catatan QC gagal',
            ]);

            if (!$this->qcFailJobId) {
                return;
            }

            $actor = auth()->user();
            if (!$actor instanceof User) {
                abort(403);
            }

            $this->service->qcFail($this->qcFailJobId, $validated['qcFailNote'], $actor);
            $this->dispatch('toast', message: 'QC gagal, task kembali ke In Progress.', type: 'warning');
            $this->closeModal();
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal memproses QC gagal.');
        }
    }

    public function closeModal(): void
    {
        $this->showQcFailModal = false;
        $this->qcFailJobId = null;
        $this->qcFailNote = null;
        $this->resetValidation();
    }

    public function isMine(ProductionJob $job): bool
    {
        return (int) ($job->claimed_by ?? 0) === (int) (auth()->id() ?? 0);
    }

    public function canManageAll(): bool
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            return false;
        }

        return $user->hasRoleSlug(['owner', 'superadmin', 'administrator', 'admin']) || $user->can('production.assign');
    }

    public function canHandleRole(ProductionJob $job): bool
    {
        if ($this->canManageAll()) {
            return true;
        }

        $slug = $job->assignedRole?->slug;
        if (!filled($slug)) {
            return true;
        }

        return in_array($slug, $this->userRoleSlugs, true);
    }

    public function canClaim(ProductionJob $job): bool
    {
        if (!$this->canHandleRole($job)) {
            return false;
        }

        return empty($job->claimed_by) || $this->isMine($job);
    }

    public function canRelease(ProductionJob $job): bool
    {
        if (empty($job->claimed_by)) {
            return false;
        }

        return $this->isMine($job) || $this->canManageAll();
    }

    public function canMove(ProductionJob $job): bool
    {
        return $this->isMine($job) || $this->canManageAll();
    }

    public function render()
    {
        return view('livewire.admin.productions.index');
    }

    protected function isDesainRoute(): bool
    {
        return request()->route()?->getName() === 'productions.desain';
    }

    protected function normalizeStage(?string $stage): string
    {
        return in_array($stage, [ProductionJob::STAGE_DESAIN, ProductionJob::STAGE_PRODUKSI], true)
            ? (string) $stage
            : ProductionJob::STAGE_PRODUKSI;
    }
}
