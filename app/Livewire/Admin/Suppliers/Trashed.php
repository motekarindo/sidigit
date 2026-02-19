<?php

namespace App\Livewire\Admin\Suppliers;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Supplier Terhapus')]
class Trashed extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('supplier.view');
        $this->setPageMeta(
            'Supplier Terhapus',
            'Kelola supplier yang telah dihapus.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Supplier', 'url' => route('suppliers.index')],
                ['label' => 'Terhapus', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.suppliers.trashed');
    }
}
