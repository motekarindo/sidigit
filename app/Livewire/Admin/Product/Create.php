<?php

namespace App\Livewire\Admin\Product;

use App\Livewire\Admin\Product\Concerns\HandlesProductForm;
use App\Services\ProductService;
use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Produk')]
class Create extends Component
{
    use AuthorizesRequests;
    use HandlesProductForm;
    use WithErrorToast;
    use WithPageMeta;

    public string $sku = '';
    public string $name = '';
    public $base_price = null;
    public $sale_price = null;
    public $length_cm = null;
    public $width_cm = null;
    public ?int $unit_id = null;
    public ?int $category_id = null;
    public ?string $product_description = null;
    public array $materials = [];

    protected ProductService $service;

    public function boot(ProductService $service): void
    {
        $this->service = $service;
    }

    protected array $messages = [
        'sku.required' => 'SKU wajib diisi.',
        'sku.unique' => 'SKU sudah digunakan. Gunakan SKU lain.',
        'name.required' => 'Nama produk wajib diisi.',
        'base_price.required' => 'Harga pokok wajib diisi.',
        'base_price.integer' => 'Harga pokok harus berupa angka bulat.',
        'base_price.min' => 'Harga pokok tidak boleh kurang dari 0.',
        'sale_price.required' => 'Harga jual wajib diisi.',
        'sale_price.integer' => 'Harga jual harus berupa angka bulat.',
        'sale_price.min' => 'Harga jual tidak boleh kurang dari 0.',
        'unit_id.required' => 'Satuan wajib dipilih.',
        'unit_id.exists' => 'Satuan yang dipilih tidak valid.',
        'category_id.required' => 'Kategori produk wajib dipilih.',
        'category_id.exists' => 'Kategori yang dipilih tidak valid.',
        'length_cm.numeric' => 'Panjang harus berupa angka.',
        'length_cm.min' => 'Panjang tidak boleh kurang dari 0.',
        'width_cm.numeric' => 'Lebar harus berupa angka.',
        'width_cm.min' => 'Lebar tidak boleh kurang dari 0.',
        'materials.required' => 'Pilih minimal satu material untuk produk ini.',
        'materials.min' => 'Pilih minimal satu material untuk produk ini.',
        'materials.*.exists' => 'Material yang dipilih tidak valid.',
    ];

    protected array $validationAttributes = [
        'sku' => 'SKU',
        'name' => 'Nama Produk',
        'base_price' => 'Harga Pokok',
        'sale_price' => 'Harga Jual',
        'length_cm' => 'Panjang (cm)',
        'width_cm' => 'Lebar (cm)',
        'unit_id' => 'Satuan',
        'category_id' => 'Kategori Produk',
        'product_description' => 'Deskripsi',
        'materials' => 'Material Produk',
        'materials.*' => 'Material Produk',
    ];

    public function mount(): void
    {
        $this->authorize('product.create');

        $this->setPageMeta(
            'Tambah Produk',
            'Masukkan data produk, harga, dan material penyusunnya.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Produk', 'url' => route('products.index')],
                ['label' => 'Tambah', 'current' => true],
            ]
        );

        $this->loadReferenceData();
        $this->refreshMaterialsForCategory();
        $this->syncDimensionFields();
    }

    public function updatedCategoryId($value): void
    {
        $this->materials = [];
        $this->refreshMaterialsForCategory($value ? (int) $value : null);
    }

    public function updatedUnitId($value): void
    {
        $this->syncDimensionFields(true);
    }

    protected function rules(): array
    {
        return $this->rulesFor(null);
    }

    protected function rulesFor(?int $productId): array
    {
        $categoryId = $this->category_id;

        return [
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('mst_products', 'sku')->ignore($productId),
            ],
            'name' => ['required', 'string', 'max:128'],
            'base_price' => ['required', 'integer', 'min:0'],
            'sale_price' => ['required', 'integer', 'min:0'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'unit_id' => ['required', 'exists:mst_units,id'],
            'category_id' => ['required', 'exists:mst_categories,id'],
            'product_description' => ['nullable', 'string'],
            'materials' => ['required', 'array', 'min:1'],
            'materials.*' => [
                'integer',
                Rule::exists('mst_materials', 'id')->where(function ($query) use ($categoryId) {
                    if ($categoryId) {
                        $query->where('category_id', $categoryId);
                    }
                }),
            ],
        ];
    }

    public function save(): void
    {
        try {
            $this->normalizePriceInputs();
            $data = $this->validate();
            $data['description'] = $data['product_description'] ?? null;
            unset($data['product_description']);

            $product = $this->service->store($data);

            session()->flash('toast', [
                'message' => "Produk {$product->name} berhasil ditambahkan.",
                'type' => 'success',
            ]);
            $this->redirectRoute('products.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat menambahkan produk.');
        }
    }

    protected function toastValidation(ValidationException $e, ?string $fallback = null): void
    {
        $errors = $e->validator->errors()->all();
        if (!empty($errors)) {
            $message = "Periksa input:\n• " . implode("\n• ", $errors);
        } else {
            $message = $fallback ?: 'Periksa kembali input. Ada data yang belum sesuai.';
        }

        $this->dispatch('toast', message: $message, type: 'warning');
    }

    public function render()
    {
        return view('livewire.admin.product.create');
    }
}
