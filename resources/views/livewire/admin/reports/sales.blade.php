<div class="space-y-6">
    <x-breadcrumbs :items="$breadcrumbs" />

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950/60">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-forms.input label="Dari" name="start_date" type="date" wire:model.live="start_date" />
            <x-forms.input label="Sampai" name="end_date" type="date" wire:model.live="end_date" />
        </div>
    </div>

    @php $summary = $this->summary; @endphp
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Total Order</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $summary['orders'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Total Penjualan</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Laba Kotor</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">HPP</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($summary['hpp'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Sudah Dibayar</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($summary['paid'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Piutang</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($summary['outstanding'], 0, ',', '.') }}</div>
        </div>
    </div>

    <x-card title="Top Produk" description="10 produk dengan nilai penjualan tertinggi pada periode ini.">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr>
                        <th class="py-2">Produk</th>
                        <th class="py-2">Qty</th>
                        <th class="py-2">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($this->topProducts as $row)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-gray-100">{{ $row->product?->name ?? 'Produk' }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">{{ number_format((float) $row->total_qty, 0, ',', '.') }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Rp {{ number_format((float) $row->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
