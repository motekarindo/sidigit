<?php

namespace App\Livewire\Layout;

use App\Models\Branch;
use Illuminate\Support\Collection;
use Livewire\Component;

class BranchSwitcher extends Component
{
    public ?int $activeBranchId = null;
    public array $branches = [];
    public bool $isSuperAdmin = false;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->isSuperAdmin = method_exists($user, 'isBranchSuperAdmin') && $user->isBranchSuperAdmin();
        $branches = $this->resolveBranches($user);
        $this->branches = $branches
            ->map(fn (Branch $branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'is_main' => (bool) $branch->is_main,
            ])
            ->values()
            ->all();

        $this->activeBranchId = $this->resolveActiveBranchId($branches, $user);
    }

    protected function resolveBranches($user): Collection
    {
        if (method_exists($user, 'isBranchSuperAdmin') && $user->isBranchSuperAdmin()) {
            $branches = Branch::query()->orderBy('name')->get();
        } else {
            $branches = $user->branches()->orderBy('name')->get();
        }

        if ($user->branch_id && $branches->where('id', $user->branch_id)->isEmpty()) {
            $defaultBranch = Branch::query()->whereKey($user->branch_id)->first();
            if ($defaultBranch) {
                $branches->push($defaultBranch);
            }
        }

        return $branches->unique('id')->values();
    }

    protected function resolveActiveBranchId(Collection $branches, $user): ?int
    {
        $sessionId = session('active_branch_id');
        $allowedIds = $branches->pluck('id')->map(fn ($id) => (int) $id)->all();
        $fallback = $user?->branch_id ?: ($branches->first()?->id ?? null);

        if ($this->isSuperAdmin) {
            if ($sessionId && in_array((int) $sessionId, $allowedIds, true)) {
                return (int) $sessionId;
            }
            return null;
        }

        $activeId = $sessionId && in_array((int) $sessionId, $allowedIds, true)
            ? (int) $sessionId
            : ($fallback ? (int) $fallback : null);

        if ($activeId) {
            session(['active_branch_id' => $activeId]);
        }

        return $activeId;
    }

    protected function allowedBranchIds(): array
    {
        return collect($this->branches)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function updatedActiveBranchId($value): void
    {
        $value = is_numeric($value) ? (int) $value : null;

        if (empty($value)) {
            if ($this->isSuperAdmin) {
                session()->forget('active_branch_id');
                $this->activeBranchId = null;
                $this->dispatch('toast', message: 'Menampilkan semua cabang.', type: 'success');
                $this->redirect(url()->current(), navigate: false);
            }
            return;
        }

        if (! in_array($value, $this->allowedBranchIds(), true)) {
            session()->flash('toast', [
                'message' => 'Cabang tidak tersedia untuk akun ini.',
                'type' => 'warning',
            ]);
            $fallback = session('active_branch_id') ?? ($this->allowedBranchIds()[0] ?? null);
            $this->activeBranchId = $fallback ? (int) $fallback : null;
            return;
        }

        session(['active_branch_id' => $value]);
        $this->dispatch('toast', message: 'Cabang aktif berhasil diubah.', type: 'success');
        $this->redirect(url()->current(), navigate: false);
    }

    public function render()
    {
        return view('livewire.layout.branch-switcher');
    }
}
