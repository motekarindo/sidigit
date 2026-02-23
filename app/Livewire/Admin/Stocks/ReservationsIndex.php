<?php

namespace App\Livewire\Admin\Stocks;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Reservasi Stok')]
class ReservationsIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('stock-reservation.view');

        $this->setPageMeta(
            'Reservasi Stok',
            'Daftar reservasi stok dari order yang sudah disetujui.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Reservasi Stok', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.stocks.reservations');
    }
}
