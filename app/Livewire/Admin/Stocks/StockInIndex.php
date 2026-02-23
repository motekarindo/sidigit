<?php

namespace App\Livewire\Admin\Stocks;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Stok Masuk')]
class StockInIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public string $type = 'in';

    public function mount(): void
    {
        $this->authorize('stock-in.view');

        $this->setPageMeta(
            'Stok Masuk',
            'Catat penambahan stok bahan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Stok Masuk', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.stocks.index');
    }
}
