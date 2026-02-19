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
            <div class="text-sm text-gray-500">Total Pengeluaran</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($summary['total'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Expense Bahan</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($summary['material'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Expense Umum</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($summary['general'], 0, ',', '.') }}</div>
        </div>
    </div>

    <x-card title="Top Material" description="10 material dengan expense tertinggi pada periode ini.">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr>
                        <th class="py-2">Material</th>
                        <th class="py-2">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($this->topMaterials as $row)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-gray-100">{{ $row->material?->name ?? 'Material' }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Rp {{ number_format((float) $row->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-4 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
