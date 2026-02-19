@php
    $totals = $this->totalsPreview();
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer (Opsional)</label>
                <div class="mt-2">
                    <select wire:model="customer_id"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Pilih customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @error('customer_id')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Order</label>
                    <input type="date" wire:model="order_date"
                        class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    @error('order_date')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deadline</label>
                    <input type="date" wire:model="deadline"
                        class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    @error('deadline')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select wire:model="status"
                    class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="draft">Draft</option>
                    <option value="menunggu-dp">Menunggu DP</option>
                    <option value="desain">Desain</option>
                    <option value="approval">Approval Customer</option>
                    <option value="produksi">Produksi</option>
                    <option value="finishing">Finishing</option>
                    <option value="qc">QC</option>
                    <option value="siap">Siap Diambil/Dikirim</option>
                    <option value="selesai">Selesai</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                <textarea wire:model="notes" rows="4"
                    class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Item Order</h2>
            <button type="button" wire:click="addItem"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                + Tambah Item
            </button>
        </div>

        <div class="space-y-4">
            @foreach ($items as $index => $item)
                @php
                    $calc = $this->calculateItemPreview($item);
                    $categoryId = $item['category_id'] ?? null;
                    $materialOptions = $item['allow_all_materials'] ?? false
                        ? $materialsAll
                        : ($categoryId !== null && isset($materialsByCategory[$categoryId])
                            ? $materialsByCategory[$categoryId]
                            : []);
                    $showDimension = !empty($item['unit_id']) && in_array((int) $item['unit_id'], $dimensionUnitIds, true);
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950/60">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-3">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Produk</label>
                            <select wire:model.live="items.{{ $index }}.product_id"
                                wire:change="handleProductChange({{ $index }}, $event.target.value)"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Pilih produk</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-3">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Bahan</label>
                                <button type="button" wire:click="toggleMaterialScope({{ $index }})"
                                    class="text-xs font-semibold text-brand-500">
                                    {{ !empty($item['allow_all_materials']) ? 'Filter kategori' : 'Semua bahan' }}
                                </button>
                            </div>
                            <select wire:model.live="items.{{ $index }}.material_id"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Pilih bahan</option>
                                @foreach ($materialOptions as $material)
                                    <option value="{{ $material['id'] }}">{{ $material['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Qty</label>
                            <input type="number" step="0.01" min="1" wire:model.live="items.{{ $index }}.qty"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                        </div>

                        <div class="lg:col-span-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Harga Jual</label>
                            <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.price"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                        </div>

                        <div class="lg:col-span-2 flex items-start justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})"
                                class="mt-6 rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/40 dark:text-red-300 dark:hover:bg-red-500/10">
                                Hapus
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-4">
                        @if ($showDimension)
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Panjang (cm)</label>
                                <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.length_cm"
                                    class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Lebar (cm)</label>
                                <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.width_cm"
                                    class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                            </div>
                        @else
                            <div class="hidden lg:block"></div>
                            <div class="hidden lg:block"></div>
                        @endif
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Diskon</label>
                            <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.discount"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Finishing</label>
                            <select multiple wire:model="items.{{ $index }}.finish_ids"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                @foreach ($finishes as $finish)
                                    <option value="{{ $finish['id'] }}">{{ $finish['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-300">
                        <span>HPP: <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($calc['hpp'], 0, ',', '.') }}</span></span>
                        <span>Total: <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($calc['total'], 0, ',', '.') }}</span></span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pembayaran</h2>
            <button type="button" wire:click="addPayment"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-900">
                + Tambah Pembayaran
            </button>
        </div>

        <div class="space-y-3">
            @foreach ($payments as $index => $payment)
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah</label>
                        <input type="number" step="0.01" min="0" wire:model="payments.{{ $index }}.amount"
                            class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    </div>
                    <div class="lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Metode</label>
                        <select wire:model="payments.{{ $index }}.method"
                            class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                        <input type="datetime-local" wire:model="payments.{{ $index }}.paid_at"
                            class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                        <input type="text" wire:model="payments.{{ $index }}.notes"
                            class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    </div>
                    <div class="lg:col-span-1 flex items-end justify-end">
                        <button type="button" wire:click="removePayment({{ $index }})"
                            class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/40 dark:text-red-300 dark:hover:bg-red-500/10">
                            Hapus
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950/60">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ringkasan</h2>
        <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-gray-600 dark:text-gray-300 lg:grid-cols-4">
            <div>HPP: <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totals['total_hpp'], 0, ',', '.') }}</span></div>
            <div>Total Harga: <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totals['total_price'], 0, ',', '.') }}</span></div>
            <div>Diskon: <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totals['total_discount'], 0, ',', '.') }}</span></div>
            <div>Grand Total: <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totals['grand_total'], 0, ',', '.') }}</span></div>
        </div>
    </div>
</div>
