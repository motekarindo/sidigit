<?php

namespace App\Livewire\Admin\Branches;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Cabang')]
class BranchesIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('branch.view');

        $this->setPageMeta(
            'Daftar Cabang',
            'Kelola cabang dan akses operasional.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Cabang', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.branches.index');
    }
}
