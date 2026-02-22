@php
    $remaining = max(0, $grandTotal - $paidAmount);
    $paymentMethods = collect([
        ['value' => 'cash', 'label' => 'Cash'],
        ['value' => 'transfer', 'label' => 'Transfer'],
        ['value' => 'qris', 'label' => 'QRIS'],
    ]);
@endphp

<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card class="rounded-3xl p-4 shadow-theme-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.edit', ['order' => $orderId]) }}" class="btn btn-secondary">Kembali ke Edit</a>
                <a href="{{ route('orders.index') }}" class="btn btn-secondary">Daftar Order</a>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500">Order No</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $orderNo }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500">Status</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $status }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500">Grand Total</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <p class="text-sm text-gray-500">Sisa Tagihan</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">Rp {{ number_format($remaining, 0, ',', '.') }}</p>
            </div>
        </div>

        <form wire:submit.prevent="save" class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" min="1" step="1" wire:model="amount"
                        class="form-input mt-2" required />
                    @error('amount')
                        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-forms.searchable-select
                        label="Metode"
                        :options="$paymentMethods"
                        optionValue="value"
                        optionLabel="label"
                        placeholder="Pilih metode"
                        wire:model="method"
                    />
                    @error('method')
                        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Bayar <span class="text-red-500">*</span></label>
                    <input type="datetime-local" wire:model="paid_at"
                        class="form-input mt-2" required />
                    @error('paid_at')
                        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                    <input type="text" wire:model="notes" class="form-input mt-2" />
                    @error('notes')
                        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Simpan Pembayaran</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>

        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-950/60">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Pembayaran</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2">Tanggal</th>
                            <th class="py-2">Metode</th>
                            <th class="py-2">Jumlah</th>
                            <th class="py-2">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-gray-100">{{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-200">{{ strtoupper($payment->method ?? '-') }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-200">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-200">{{ $payment->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">Belum ada pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-card>
</div>
