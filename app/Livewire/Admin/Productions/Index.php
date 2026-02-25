<?php

namespace App\Livewire\Admin\Productions;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Produksi')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('production.view');

        $this->setPageMeta(
            'Produksi',
            'Kelola job produksi per item order: Antrian -> In Progress -> Selesai -> QC -> Siap Diambil.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Produksi', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.productions.index');
    }
}
