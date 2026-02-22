<div class="space-y-6">
    <x-breadcrumbs :items="$breadcrumbs" />

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950/60">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
            <x-forms.input label="Dari" name="start_date" type="date" wire:model.live="start_date" />
            <x-forms.input label="Sampai" name="end_date" type="date" wire:model.live="end_date" />
            <div class="lg:col-span-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Cabang</label>
                <select wire:model.live="branch_id" class="form-input mt-2">
                    @if ($isSuperAdmin)
                        <option value="">Semua Cabang</option>
                    @endif
                    @foreach ($this->branchOptions as $branch)
                        <option value="{{ $branch['id'] }}">{{ $branch['name'] }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Scope aktif: {{ $this->selectedBranchLabel }}
                </p>
            </div>
        </div>
    </div>

    @php $summary = $this->summary; @endphp
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Total Order</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $summary['orders'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Omzet</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">HPP</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                {{ number_format($summary['hpp'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Laba Kotor</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                {{ number_format($summary['gross_profit'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Pembayaran Masuk</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                {{ number_format($summary['paid'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Piutang</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                {{ number_format($summary['outstanding'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Pengeluaran</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                {{ number_format($summary['expenses'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/60">
            <div class="text-sm text-gray-500">Laba Bersih</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                {{ number_format($summary['net_profit'], 0, ',', '.') }}</div>
        </div>
    </div>

    <x-card title="Breakdown Per Cabang"
        description="Rekap performa tiap cabang berdasarkan periode dan filter cabang yang dipilih.">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr>
                        <th class="py-2">Cabang</th>
                        <th class="py-2">Order</th>
                        <th class="py-2">Omzet</th>
                        <th class="py-2">Laba Kotor</th>
                        <th class="py-2">Pembayaran</th>
                        <th class="py-2">Piutang</th>
                        <th class="py-2">Pengeluaran</th>
                        <th class="py-2">Laba Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($this->branchBreakdown as $row)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-gray-100">
                                {{ $row['branch_name'] }} <span class="text-xs text-gray-400">#{{ $row['branch_id'] }}</span>
                            </td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">{{ number_format($row['orders'], 0, ',', '.') }}
                            </td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Rp
                                {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Rp
                                {{ number_format($row['gross_profit'], 0, ',', '.') }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Rp
                                {{ number_format($row['paid'], 0, ',', '.') }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Rp
                                {{ number_format($row['outstanding'], 0, ',', '.') }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Rp
                                {{ number_format($row['expenses'], 0, ',', '.') }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">Rp
                                {{ number_format($row['net_profit'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
