<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Scopes\BranchScope;
use App\Traits\WithPageMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Per Cabang')]
class BranchReport extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public string $start_date;
    public string $end_date;
    public ?int $branch_id = null;
    public bool $isSuperAdmin = false;
    public array $allowedBranchIds = [];

    public function mount(): void
    {
        $this->authorize('report.branch.view');

        $this->start_date = now()->subDays(30)->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');

        $this->resolveBranchAccess();

        $this->setPageMeta(
            'Laporan Per Cabang',
            'Ringkasan performa order, pembayaran, pengeluaran, dan laba per cabang.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Laporan Per Cabang', 'current' => true],
            ]
        );
    }

    protected function resolveBranchAccess(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->isSuperAdmin = method_exists($user, 'isBranchSuperAdmin') && $user->isBranchSuperAdmin();

        if ($this->isSuperAdmin) {
            $this->allowedBranchIds = Branch::query()
                ->orderBy('name')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $sessionBranchId = session('active_branch_id');
            if (! empty($sessionBranchId) && in_array((int) $sessionBranchId, $this->allowedBranchIds, true)) {
                $this->branch_id = (int) $sessionBranchId;
            }

            return;
        }

        $branchIds = $user->branches()
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (! empty($user->branch_id)) {
            $branchIds[] = (int) $user->branch_id;
        }

        $this->allowedBranchIds = collect($branchIds)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->branch_id = $this->allowedBranchIds[0] ?? null;
    }

    public function updatedBranchId($value): void
    {
        $value = is_numeric($value) ? (int) $value : null;

        if (empty($value)) {
            $this->branch_id = $this->isSuperAdmin ? null : ($this->allowedBranchIds[0] ?? null);
            return;
        }

        if (! in_array($value, $this->allowedBranchIds, true)) {
            $this->branch_id = $this->isSuperAdmin ? null : ($this->allowedBranchIds[0] ?? null);
            return;
        }

        $this->branch_id = $value;
    }

    public function getBranchOptionsProperty(): array
    {
        return Branch::query()
            ->whereIn('id', $this->allowedBranchIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Branch $branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
            ])
            ->values()
            ->all();
    }

    public function getSelectedBranchLabelProperty(): string
    {
        if (empty($this->branch_id)) {
            return 'Semua Cabang';
        }

        return Branch::query()->whereKey($this->branch_id)->value('name') ?? 'Cabang';
    }

    protected function selectedBranchIds(): array
    {
        if (! empty($this->branch_id) && in_array((int) $this->branch_id, $this->allowedBranchIds, true)) {
            return [(int) $this->branch_id];
        }

        return $this->allowedBranchIds;
    }

    protected function orderQuery(): Builder
    {
        return Order::query()
            ->withoutGlobalScope(BranchScope::class)
            ->whereIn('branch_id', $this->selectedBranchIds())
            ->whereBetween('order_date', [$this->start_date, $this->end_date]);
    }

    protected function expenseQuery(): Builder
    {
        return Expense::query()
            ->withoutGlobalScope(BranchScope::class)
            ->whereIn('branch_id', $this->selectedBranchIds())
            ->whereBetween('expense_date', [$this->start_date, $this->end_date]);
    }

    protected function paymentQuery(): Builder
    {
        return Payment::query()
            ->withoutGlobalScope(BranchScope::class)
            ->whereIn('branch_id', $this->selectedBranchIds())
            ->whereDate('paid_at', '>=', $this->start_date)
            ->whereDate('paid_at', '<=', $this->end_date);
    }

    public function getSummaryProperty(): array
    {
        $orders = $this->orderQuery();

        $totalOrders = (int) (clone $orders)->count();
        $totalRevenue = (float) (clone $orders)->sum('grand_total');
        $totalHpp = (float) (clone $orders)->sum('total_hpp');
        $totalPaid = (float) $this->paymentQuery()->sum('amount');
        $totalExpense = (float) $this->expenseQuery()->sum('amount');
        $grossProfit = $totalRevenue - $totalHpp;

        return [
            'orders' => $totalOrders,
            'revenue' => $totalRevenue,
            'hpp' => $totalHpp,
            'gross_profit' => $grossProfit,
            'paid' => $totalPaid,
            'outstanding' => max(0, $totalRevenue - $totalPaid),
            'expenses' => $totalExpense,
            'net_profit' => $grossProfit - $totalExpense,
        ];
    }

    public function getBranchBreakdownProperty()
    {
        $branchIds = $this->selectedBranchIds();
        if (empty($branchIds)) {
            return collect();
        }

        $ordersAgg = Order::query()
            ->withoutGlobalScope(BranchScope::class)
            ->selectRaw('branch_id, COUNT(*) as total_orders, COALESCE(SUM(grand_total), 0) as total_revenue, COALESCE(SUM(total_hpp), 0) as total_hpp')
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('order_date', [$this->start_date, $this->end_date])
            ->groupBy('branch_id');

        $paymentsAgg = Payment::query()
            ->withoutGlobalScope(BranchScope::class)
            ->selectRaw('branch_id, COALESCE(SUM(amount), 0) as total_paid')
            ->whereIn('branch_id', $branchIds)
            ->whereDate('paid_at', '>=', $this->start_date)
            ->whereDate('paid_at', '<=', $this->end_date)
            ->groupBy('branch_id');

        $expensesAgg = Expense::query()
            ->withoutGlobalScope(BranchScope::class)
            ->selectRaw('branch_id, COALESCE(SUM(amount), 0) as total_expense')
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('expense_date', [$this->start_date, $this->end_date])
            ->groupBy('branch_id');

        return Branch::query()
            ->whereIn('branches.id', $branchIds)
            ->leftJoinSub($ordersAgg, 'orders_agg', fn ($join) => $join->on('orders_agg.branch_id', '=', 'branches.id'))
            ->leftJoinSub($paymentsAgg, 'payments_agg', fn ($join) => $join->on('payments_agg.branch_id', '=', 'branches.id'))
            ->leftJoinSub($expensesAgg, 'expenses_agg', fn ($join) => $join->on('expenses_agg.branch_id', '=', 'branches.id'))
            ->select([
                'branches.id',
                'branches.name',
                DB::raw('COALESCE(orders_agg.total_orders, 0) as total_orders'),
                DB::raw('COALESCE(orders_agg.total_revenue, 0) as total_revenue'),
                DB::raw('COALESCE(orders_agg.total_hpp, 0) as total_hpp'),
                DB::raw('COALESCE(payments_agg.total_paid, 0) as total_paid'),
                DB::raw('COALESCE(expenses_agg.total_expense, 0) as total_expense'),
            ])
            ->orderBy('branches.name')
            ->get()
            ->map(function ($row) {
                $revenue = (float) $row->total_revenue;
                $hpp = (float) $row->total_hpp;
                $paid = (float) $row->total_paid;
                $expense = (float) $row->total_expense;
                $grossProfit = $revenue - $hpp;

                return [
                    'branch_id' => (int) $row->id,
                    'branch_name' => $row->name,
                    'orders' => (int) $row->total_orders,
                    'revenue' => $revenue,
                    'hpp' => $hpp,
                    'gross_profit' => $grossProfit,
                    'paid' => $paid,
                    'outstanding' => max(0, $revenue - $paid),
                    'expenses' => $expense,
                    'net_profit' => $grossProfit - $expense,
                ];
            });
    }

    public function render()
    {
        return view('livewire.admin.reports.branches');
    }
}
