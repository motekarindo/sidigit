<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MaterialRequest;
use App\Services\CategoryService;
use App\Services\MaterialService;
use App\Services\UnitService;
use App\Support\ErrorReporter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MaterialController extends Controller
{
    use AuthorizesRequests;
    protected $service;
    protected CategoryService $categoryService;
    protected UnitService $unitService;

    public function __construct(MaterialService $service, CategoryService $categoryService, UnitService $unitService)
    {
        $this->service = $service;
        $this->categoryService = $categoryService;
        $this->unitService = $unitService;
    }

    public function index()
    {
        $this->authorize('material.view');

        $materials = $this->service->getPaginated();

        return view('admin.material.index', compact('materials'));
    }

    public function create()
    {
        $this->authorize('material.create');

        $categories = $this->categoryService->query()->orderBy('name')->get();
        $units = $this->unitService->query()->orderBy('name')->get();

        return view('admin.material.create', compact('categories', 'units'));
    }

    public function store(MaterialRequest $request)
    {
        $this->authorize('material.create');

        try {
            $material = $this->service->store($request->validated());

            return redirect()
                ->route('materials.index')
                ->with('success', "Material {$material->name} berhasil ditambahkan.");
        } catch (\Throwable $th) {

            return back()
                ->withInput()
                ->with('error', ErrorReporter::report($th, 'Terjadi kesalahan saat menambahkan material.')['message']);
        }
    }

    public function edit(int $material)
    {
        $this->authorize('material.edit');

        try {
            $material = $this->service->find($material);
            $categories = $this->categoryService->query()->orderBy('name')->get();
            $units = $this->unitService->query()->orderBy('name')->get();

            return view('admin.material.edit', compact('material', 'categories', 'units'));
        } catch (\Throwable $th) {

            return redirect()
                ->route('materials.index')
                ->with('error', 'Data material tidak ditemukan atau tidak dapat diakses.');
        }
    }

    public function update(MaterialRequest $request, int $material)
    {
        $this->authorize('material.edit');

        try {
            $materialModel = $this->service->update($material, $request->validated());

            return redirect()
                ->route('materials.index')
                ->with('success', "Material {$materialModel->name} berhasil diperbarui.");
        } catch (\Throwable $th) {

            return back()
                ->withInput()
                ->with('error', ErrorReporter::report($th, 'Terjadi kesalahan saat memperbarui data material.')['message']);
        }
    }

    public function destroy(int $material)
    {
        $this->authorize('material.delete');

        try {
            $materialModel = $this->service->find($material);
            $this->service->destroy($material);

            return redirect()
                ->route('materials.index')
                ->with('success', "Material {$materialModel->name} berhasil dihapus.");
        } catch (\Throwable $th) {

            return redirect()
                ->route('materials.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data material. Silakan coba lagi.');
        }
    }
}
