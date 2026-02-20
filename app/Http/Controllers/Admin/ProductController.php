<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Services\CategoryService;
use App\Services\MaterialService;
use App\Services\ProductService;
use App\Services\UnitService;
use App\Support\ErrorReporter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;

    protected $service;
    protected CategoryService $categoryService;
    protected MaterialService $materialService;
    protected UnitService $unitService;

    public function __construct(
        ProductService $service,
        CategoryService $categoryService,
        MaterialService $materialService,
        UnitService $unitService
    )
    {
        $this->service = $service;
        $this->categoryService = $categoryService;
        $this->materialService = $materialService;
        $this->unitService = $unitService;
    }

    public function index()
    {
        $this->authorize('product.view');

        $products = $this->service->getPaginated();

        return view('admin.product.index', compact('products'));
    }

    public function create()
    {
        $this->authorize('product.create');

        $categories = $this->categoryService->query()->orderBy('name')->get();
        $materialsAll = $this->getMaterialsAll();
        $units = $this->unitService->query()->orderBy('name')->get();

        return view('admin.product.create', compact('categories', 'materialsAll', 'units'));
    }

    public function store(ProductRequest $request)
    {
        $this->authorize('product.create');

        try {
            $product = $this->service->store($request->validated());

            return redirect()
                ->route('products.index')
                ->with('success', "Produk {$product->name} berhasil ditambahkan.");
        } catch (\Throwable $th) {

            return back()
                ->withInput()
                ->with('error', ErrorReporter::report($th, 'Terjadi kesalahan saat menambahkan produk.')['message']);
        }
    }

    public function edit(int $product)
    {
        $this->authorize('product.edit');

        try {
            $productModel = $this->service->find($product);
            $categories = $this->categoryService->query()->orderBy('name')->get();
            $materialsAll = $this->getMaterialsAll();
            $units = $this->unitService->query()->orderBy('name')->get();

            return view('admin.product.edit', [
                'product' => $productModel,
                'categories' => $categories,
                'materialsAll' => $materialsAll,
                'units' => $units,
            ]);
        } catch (\Throwable $th) {

            return redirect()
                ->route('products.index')
                ->with('error', 'Data produk tidak ditemukan atau tidak dapat diakses.');
        }
    }

    public function update(ProductRequest $request, int $product)
    {
        $this->authorize('product.edit');

        try {
            $updatedProduct = $this->service->update($product, $request->validated());

            return redirect()
                ->route('products.index')
                ->with('success', "Produk {$updatedProduct->name} berhasil diperbarui.");
        } catch (\Throwable $th) {

            return back()
                ->withInput()
                ->with('error', ErrorReporter::report($th, 'Terjadi kesalahan saat memperbarui data produk.')['message']);
        }
    }

    public function destroy(int $product)
    {
        $this->authorize('product.delete');

        try {
            $productModel = $this->service->find($product);
            $this->service->destroy($product);

            return redirect()
                ->route('products.index')
                ->with('success', "Produk {$productModel->name} berhasil dihapus.");
        } catch (\Throwable $th) {

            return redirect()
                ->route('products.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data produk. Silakan coba lagi.');
        }
    }

    protected function getMaterialsAll(): array
    {
        return $this->materialService->query()
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get()
            ->map(function ($material) {
                return [
                    'id' => (string) $material->id,
                    'name' => $material->name,
                    'unit' => optional($material->unit)->name,
                    'category' => optional($material->category)->name,
                ];
            })
            ->values()
            ->toArray();
    }
}
