<?php

namespace App\Livewire\Admin\Stocks;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Saldo Stok')]
class BalanceIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('stock-balance.view');

        $this->setPageMeta(
            'Saldo Stok',
            'Ringkasan saldo stok per bahan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Saldo Stok', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.stocks.balance');
    }
}
