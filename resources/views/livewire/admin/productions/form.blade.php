<div class="space-y-4">
    @if ($modalMode === 'assign')
        <div>
            <x-forms.searchable-select
                label="Role Penanggung Jawab"
                :options="$this->roleOptions"
                optionValue="id"
                optionLabel="label"
                placeholder="Pilih role"
                wire:model="assign_role_id"
            />
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Assignment menggunakan role agar distribusi pekerjaan tetap fleksibel per tim.
            </p>
        </div>
    @elseif ($modalMode === 'qc_fail')
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan QC Gagal</label>
            <textarea wire:model="qc_fail_note" rows="4" class="form-input mt-2" placeholder="Contoh: warna tidak presisi, perlu cetak ulang."></textarea>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Job akan kembali ke status <strong>In Progress</strong> dan status order otomatis kembali ke <strong>Produksi</strong>.
            </p>
        </div>
    @else
        <div class="space-y-3">
            @forelse ($historyLogs as $log)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm dark:border-gray-800 dark:bg-gray-950/40">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ strtoupper(str_replace('_', ' ', $log['event'])) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $log['created_at'] ?: '-' }}
                        </div>
                    </div>
                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                        {{ $log['from_status'] ?: '-' }} -> {{ $log['to_status'] ?: '-' }}
                    </div>
                    @if (!empty($log['note']))
                        <div class="mt-2 text-gray-700 dark:text-gray-200">{{ $log['note'] }}</div>
                    @endif
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Oleh: {{ $log['changed_by'] ?: 'Sistem' }}
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Belum ada riwayat untuk job ini.
                </div>
            @endforelse
        </div>
    @endif
</div>
