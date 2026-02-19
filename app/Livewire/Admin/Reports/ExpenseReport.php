<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Expense;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Pengeluaran')]
class ExpenseReport extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public string $start_date;
    public string $end_date;

    public function mount(): void
    {
        $this->authorize('report.expense.view');

        $this->start_date = now()->subDays(30)->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');

        $this->setPageMeta(
            'Laporan Pengeluaran',
            'Ringkasan pengeluaran operasional dan bahan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Laporan Pengeluaran', 'current' => true],
            ]
        );
    }

    public function getSummaryProperty(): array
    {
        $base = Expense::query()
            ->whereBetween('expense_date', [$this->start_date, $this->end_date]);

        $total = (float) (clone $base)->sum('amount');
        $material = (float) (clone $base)->where('type', 'material')->sum('amount');
        $general = (float) (clone $base)->where('type', 'general')->sum('amount');

        return [
            'total' => $total,
            'material' => $material,
            'general' => $general,
        ];
    }

    public function getTopMaterialsProperty()
    {
        return Expense::query()
            ->select('material_id', DB::raw('sum(amount) as total_amount'))
            ->where('type', 'material')
            ->whereBetween('expense_date', [$this->start_date, $this->end_date])
            ->groupBy('material_id')
            ->with('material')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.reports.expenses');
    }
}
