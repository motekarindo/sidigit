<?php

namespace App\Livewire\Admin\Users;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('User Terhapus')]
class Trashed extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('users.view');
        $this->setPageMeta(
            'User Terhapus',
            'Kelola user yang telah dihapus.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'User', 'url' => route('users.index')],
                ['label' => 'Terhapus', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.users.trashed');
    }
}
