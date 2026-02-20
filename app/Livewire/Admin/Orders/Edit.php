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
#[Title('Edit Order')]
class Edit extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;
    use HandlesOrderForm;

    public int $orderId;
    public ?int $customer_id = null;
    public string $status = 'draft';
    public string $order_date;
    public ?string $deadline = null;
    public ?string $notes = null;

    public array $items = [];
    public array $payments = [];

    protected OrderService $service;

    public function mount(int $order, OrderService $service): void
    {
        $this->authorize('order.edit');
        $this->service = $service;
        $this->loadReferenceData();

        $orderModel = $this->service->find($order);

        $this->orderId = $orderModel->id;
        $this->customer_id = $orderModel->customer_id;
        $this->status = $orderModel->status;
        $this->order_date = $orderModel->order_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->deadline = $orderModel->deadline?->format('Y-m-d');
        $this->notes = $orderModel->notes;

        $this->items = $orderModel->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'category_id' => $item->product?->category_id,
                'material_id' => $item->material_id,
                'unit_id' => $item->unit_id,
                'qty' => (float) $item->qty,
                'length_cm' => $item->length_cm,
                'width_cm' => $item->width_cm,
                'price' => (float) $item->price,
                'discount' => (float) $item->discount,
                'finish_ids' => $item->finishes?->pluck('finish_id')->toArray() ?? [],
                'allow_all_materials' => false,
            ];
        })->toArray();

        $this->payments = $orderModel->payments->map(function ($payment) {
            return [
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'paid_at' => $payment->paid_at?->format('Y-m-d H:i'),
                'notes' => $payment->notes,
            ];
        })->toArray();

        if (empty($this->items)) {
            $this->items = [$this->newItem()];
        }

        $this->setPageMeta(
            'Edit Order',
            'Perbarui data order dan item.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Order', 'url' => route('orders.index')],
                ['label' => 'Edit', 'current' => true],
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
        $data = $this->validate();

        try {
            $order = $this->service->update($this->orderId, [
                'customer_id' => $data['customer_id'] ?? null,
                'status' => $data['status'],
                'order_date' => $data['order_date'],
                'deadline' => $data['deadline'] ?? null,
                'notes' => $data['notes'] ?? null,
                'items' => $data['items'],
                'payments' => $data['payments'] ?? [],
            ]);

            session()->flash('toast', [
                'message' => "Order {$order->order_no} berhasil diperbarui.",
                'type' => 'success',
            ]);
            $this->redirectRoute('orders.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat memperbarui order.');
        }
    }

    public function render()
    {
        return view('livewire.admin.orders.edit');
    }
}
