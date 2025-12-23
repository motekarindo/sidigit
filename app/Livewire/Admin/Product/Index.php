<?php

namespace App\Livewire\Admin\Product;

use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen Produk')]
class Index extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    protected $paginationTheme = 'tailwind';
    protected ProductService $service;

    public function mount(ProductService $service): void
    {
        $this->service = $service;

        $this->authorize('product.view');
    }

    public function deleteProduct(int $productId): void
    {
        $this->authorize('product.delete');

        try {
            $product = $this->service->find($productId);
            $this->service->destroy($productId);

            session()->flash('success', "Produk {$product->name} berhasil dihapus.");
        } catch (\Throwable $th) {
            report($th);

            session()->flash('error', 'Terjadi kesalahan saat menghapus data produk. Silakan coba lagi.');
        }
    }

    public function getProductsProperty()
    {
        return $this->service->getPaginated();
    }

    public function render()
    {
        return view('livewire.admin.product.index', [
            'products' => $this->products,
        ])->layoutData([
            'title' => 'Manajemen Produk',
        ]);
    }
}
