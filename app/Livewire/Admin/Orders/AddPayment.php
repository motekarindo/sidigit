<?php

namespace App\Livewire\Admin\Orders;

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
#[Title('Input Pembayaran Order')]
class AddPayment extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;

    protected OrderService $service;

    public int $orderId;
    public string $orderNo = '';
    public string $status = 'draft';
    public float $grandTotal = 0;
    public float $paidAmount = 0;

    public ?int $amount = null;
    public string $method = 'cash';
    public string $paid_at;
    public ?string $notes = null;

    public function boot(OrderService $service): void
    {
        $this->service = $service;
    }

    public function mount(int $order): void
    {
        $this->authorize('order.edit');

        $this->orderId = $order;
        $this->paid_at = now()->format('Y-m-d\TH:i');
        $this->refreshOrderState();

        $this->setPageMeta(
            'Input Pembayaran Order',
            'Tambahkan pembayaran tanpa mengubah item order.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Order', 'url' => route('orders.index')],
                ['label' => 'Input Pembayaran', 'current' => true],
            ]
        );
    }

    protected function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'string', 'in:cash,transfer,qris'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        try {
            if ($this->remainingAmount() <= 0) {
                $this->amount = null;
                $this->dispatch('toast', message: 'Tagihan sudah lunas. Jika ada selisih bayar, nilainya dihitung sebagai kembalian.', type: 'warning');
                return;
            }

            $data = $this->validate();

            $this->service->addPayment($this->orderId, [
                'amount' => $data['amount'],
                'method' => $data['method'],
                'paid_at' => $data['paid_at'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->amount = null;
            $this->method = 'cash';
            $this->paid_at = now()->format('Y-m-d\TH:i');
            $this->notes = null;
            $this->refreshOrderState();

            $this->dispatch('toast', message: 'Pembayaran berhasil ditambahkan.', type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Gagal menambahkan pembayaran.');
        }
    }

    public function fillRemainingAmount(): void
    {
        $remaining = $this->remainingAmount();

        if ($remaining <= 0) {
            $this->amount = null;
            $this->dispatch('toast', message: 'Order ini sudah lunas. Selisih minus diperlakukan sebagai kembalian.', type: 'warning');
            return;
        }

        $this->amount = $remaining;
    }

    protected function refreshOrderState(): void
    {
        $order = $this->service->find($this->orderId);

        $this->orderNo = $order->order_no;
        $this->status = $order->status;
        $this->grandTotal = (float) $order->grand_total;
        $this->paidAmount = (float) $order->paid_amount;

        if ($this->remainingAmount() <= 0) {
            $this->amount = null;
        }
    }

    protected function remainingAmount(): int
    {
        return max(0, (int) ceil($this->grandTotal - $this->paidAmount));
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
        $order = $this->service->find($this->orderId);

        return view('livewire.admin.orders.add-payment', [
            'payments' => $order->payments->sortByDesc('paid_at')->values(),
        ]);
    }
}
