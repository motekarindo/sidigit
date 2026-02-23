<?php

namespace App\Livewire\Admin\Stocks;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Stok Keluar')]
class StockOutIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public string $type = 'out';

    public function mount(): void
    {
        $this->authorize('stock-out.view');

        $this->setPageMeta(
            'Stok Keluar',
            'Catat pengurangan stok bahan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Stok Keluar', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.stocks.index');
    }
}
