<?php

namespace App\Livewire\Admin\Materials;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Bahan Terhapus')]
class Trashed extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('material.view');
        $this->setPageMeta(
            'Bahan Terhapus',
            'Kelola bahan yang telah dihapus.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Bahan', 'url' => route('materials.index')],
                ['label' => 'Terhapus', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.materials.trashed');
    }
}
