<?php

namespace App\Livewire\Admin\Orders;

use App\Livewire\Admin\Orders\Concerns\HandlesOrderForm;
use App\Services\OrderService;
use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Order')]
class Create extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;
    use HandlesOrderForm;

    public ?int $customer_id = null;
    public string $status = 'draft';
    public string $order_date;
    public ?string $deadline = null;
    public ?string $notes = null;

    public array $items = [];
    public array $payments = [];

    protected array $messages = [
        'customer_id.exists' => 'Customer yang dipilih tidak valid.',
        'status.required' => 'Status wajib diisi.',
        'status.string' => 'Status harus berupa teks.',
        'order_date.required' => 'Tanggal order wajib diisi.',
        'order_date.date' => 'Tanggal order tidak valid.',
        'deadline.date' => 'Deadline tidak valid.',
        'notes.string' => 'Catatan harus berupa teks.',
        'items.required' => 'Item order wajib diisi.',
        'items.array' => 'Item order tidak valid.',
        'items.min' => 'Minimal 1 item order.',
        'items.*.product_id.required' => 'Produk wajib dipilih.',
        'items.*.product_id.exists' => 'Produk yang dipilih tidak valid.',
        'items.*.material_id.exists' => 'Bahan yang dipilih tidak valid.',
        'items.*.unit_id.required' => 'Satuan item wajib diisi.',
        'items.*.unit_id.exists' => 'Satuan item tidak valid.',
        'items.*.qty.required' => 'Qty wajib diisi.',
        'items.*.qty.integer' => 'Qty harus berupa angka bulat.',
        'items.*.qty.min' => 'Qty minimal 1.',
        'items.*.length_cm.numeric' => 'Panjang harus berupa angka.',
        'items.*.length_cm.min' => 'Panjang tidak boleh kurang dari 0.',
        'items.*.width_cm.numeric' => 'Lebar harus berupa angka.',
        'items.*.width_cm.min' => 'Lebar tidak boleh kurang dari 0.',
        'items.*.price.integer' => 'Harga jual harus berupa angka bulat.',
        'items.*.price.min' => 'Harga jual tidak boleh kurang dari 0.',
        'items.*.discount.integer' => 'Diskon harus berupa angka bulat.',
        'items.*.discount.min' => 'Diskon tidak boleh kurang dari 0.',
        'items.*.finish_ids.array' => 'Finishing harus berupa daftar.',
        'items.*.finish_ids.*.exists' => 'Finishing yang dipilih tidak valid.',
        'payments.array' => 'Pembayaran harus berupa daftar.',
        'payments.*.amount.integer' => 'Jumlah pembayaran harus berupa angka bulat.',
        'payments.*.amount.min' => 'Jumlah pembayaran tidak boleh kurang dari 0.',
        'payments.*.method.string' => 'Metode pembayaran tidak valid.',
        'payments.*.paid_at.date' => 'Tanggal pembayaran tidak valid.',
        'payments.*.notes.string' => 'Catatan pembayaran harus berupa teks.',
    ];

    protected array $validationAttributes = [
        'customer_id' => 'Customer',
        'status' => 'Status',
        'order_date' => 'Tanggal order',
        'deadline' => 'Deadline',
        'notes' => 'Catatan',
        'items' => 'Item order',
        'items.*.product_id' => 'Produk',
        'items.*.material_id' => 'Bahan',
        'items.*.unit_id' => 'Satuan',
        'items.*.qty' => 'Qty',
        'items.*.length_cm' => 'Panjang',
        'items.*.width_cm' => 'Lebar',
        'items.*.price' => 'Harga jual',
        'items.*.discount' => 'Diskon',
        'items.*.finish_ids' => 'Finishing',
        'items.*.finish_ids.*' => 'Finishing',
        'payments' => 'Pembayaran',
        'payments.*.amount' => 'Jumlah pembayaran',
        'payments.*.method' => 'Metode pembayaran',
        'payments.*.paid_at' => 'Tanggal pembayaran',
        'payments.*.notes' => 'Catatan pembayaran',
    ];

    protected OrderService $service;

    public function mount(OrderService $service): void
    {
        $this->authorize('order.create');
        $this->service = $service;
        $this->order_date = now()->format('Y-m-d');
        $this->loadReferenceData();
        $this->items = [$this->newItem()];
        $this->payments = [];

        $this->setPageMeta(
            'Tambah Order',
            'Buat order baru dan detail item.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Order', 'url' => route('orders.index')],
                ['label' => 'Tambah', 'current' => true],
            ]
        );
    }

    protected function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:mst_customers,id'],
            'status' => ['required', 'string'],
            'order_date' => ['required', 'date'],
            'deadline' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:mst_products,id'],
            'items.*.material_id' => ['nullable', 'exists:mst_materials,id'],
            'items.*.unit_id' => ['required', 'exists:mst_units,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.length_cm' => ['nullable', 'numeric', 'min:0'],
            'items.*.width_cm' => ['nullable', 'numeric', 'min:0'],
            'items.*.price' => ['nullable', 'integer', 'min:0'],
            'items.*.discount' => ['nullable', 'integer', 'min:0'],
            'items.*.finish_ids' => ['nullable', 'array'],
            'items.*.finish_ids.*' => ['integer', 'exists:finishes,id'],
            'payments' => ['nullable', 'array'],
            'payments.*.amount' => ['nullable', 'integer', 'min:0'],
            'payments.*.method' => ['nullable', 'string'],
            'payments.*.paid_at' => ['nullable', 'date'],
            'payments.*.notes' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->validate();

            $order = $this->service->store([
                'customer_id' => $data['customer_id'] ?? null,
                'status' => $data['status'],
                'order_date' => $data['order_date'],
                'deadline' => $data['deadline'] ?? null,
                'notes' => $data['notes'] ?? null,
                'items' => $data['items'],
                'payments' => $data['payments'] ?? [],
            ]);

            session()->flash('toast', [
                'message' => "Order {$order->order_no} berhasil dibuat.",
                'type' => 'success',
            ]);
            $this->redirectRoute('orders.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat menambahkan order.');
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
        return view('livewire.admin.orders.create');
    }
}
