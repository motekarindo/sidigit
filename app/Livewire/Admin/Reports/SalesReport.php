<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Order;
use App\Models\OrderItem;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Penjualan')]
class SalesReport extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public string $start_date;
    public string $end_date;

    public function mount(): void
    {
        $this->authorize('report.sales.view');

        $this->start_date = now()->subDays(30)->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');

        $this->setPageMeta(
            'Laporan Penjualan',
            'Ringkasan penjualan berdasarkan periode.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Laporan Penjualan', 'current' => true],
            ]
        );
    }

    public function getSummaryProperty(): array
    {
        $orders = Order::query()
            ->whereBetween('order_date', [$this->start_date, $this->end_date]);

        $totalOrders = (clone $orders)->count();
        $totalRevenue = (float) (clone $orders)->sum('grand_total');
        $totalHpp = (float) (clone $orders)->sum('total_hpp');
        $totalPaid = (float) (clone $orders)->sum('paid_amount');

        return [
            'orders' => $totalOrders,
            'revenue' => $totalRevenue,
            'hpp' => $totalHpp,
            'profit' => $totalRevenue - $totalHpp,
            'paid' => $totalPaid,
            'outstanding' => max(0, $totalRevenue - $totalPaid),
        ];
    }

    public function getTopProductsProperty()
    {
        return OrderItem::query()
            ->select('product_id', DB::raw('sum(qty) as total_qty'), DB::raw('sum(total) as total_amount'))
            ->whereHas('order', function ($query) {
                $query->whereBetween('order_date', [$this->start_date, $this->end_date]);
            })
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.reports.sales');
    }
}
