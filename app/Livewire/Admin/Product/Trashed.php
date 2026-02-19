<?php

namespace App\Livewire\Admin\Product;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Produk Terhapus')]
class Trashed extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('product.view');
        $this->setPageMeta(
            'Produk Terhapus',
            'Kelola produk yang telah dihapus.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Produk', 'url' => route('products.index')],
                ['label' => 'Terhapus', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.product.trashed');
    }
}
