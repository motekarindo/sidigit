<?php

namespace App\Livewire\Admin\Orders;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Order')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('order.view');
        $this->setPageMeta(
            'Daftar Order',
            'Kelola order dan status produksinya.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Order', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.orders.index');
    }
}
