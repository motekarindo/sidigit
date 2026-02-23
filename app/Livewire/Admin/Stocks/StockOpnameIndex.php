<?php

namespace App\Livewire\Admin\Stocks;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Stok Opname')]
class StockOpnameIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public string $type = 'opname';

    public function mount(): void
    {
        $this->authorize('stock-opname.view');

        $this->setPageMeta(
            'Stok Opname',
            'Catat penyesuaian stok hasil opname.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Stok Opname', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.stocks.index');
    }
}
