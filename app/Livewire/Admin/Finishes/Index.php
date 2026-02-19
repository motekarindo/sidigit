<?php

namespace App\Livewire\Admin\Finishes;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Finishing')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('finish.view');
        $this->setPageMeta(
            'Daftar Finishing',
            'Kelola daftar finishing dan biaya tambahan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Finishing', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.finishes.index');
    }
}
