<div class="space-y-2"
    x-data="{
        draggingJobId: null,
        draggingFromStatus: null,
        overStatus: null,
        startDrag(event, jobId, fromStatus) {
            this.draggingJobId = jobId;
            this.draggingFromStatus = fromStatus;
            event.dataTransfer.effectAllowed = 'move';
        },
        endDrag() {
            this.draggingJobId = null;
            this.draggingFromStatus = null;
            this.overStatus = null;
        },
        dropTo(toStatus) {
            if (!this.draggingJobId) return;
            $wire.moveCard(this.draggingJobId, toStatus);
            this.endDrag();
        },
    }">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Produksi Kanban</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Alur: Antrian -> Desain -> In Progress -> Selesai -> QC -> Siap Diambil. Tahap desain opsional (bisa langsung ke In Progress).
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('productions.history') }}" class="btn btn-secondary inline-flex items-center gap-2">
                    <x-lucide-history class="h-4 w-4" />
                    <span>Riwayat</span>
                </a>
            </div>
        </div>

        <div class="mt-4">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-input"
                placeholder="Cari order no, nama produk, atau nama user claim...">
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Drag card ke kolom berikutnya untuk ubah status. QC gagal tetap melalui tombol agar alasan revisi tercatat.
            </p>
        </div>

        <div class="mt-6 overflow-x-auto pb-2">
            <div class="grid min-w-[1400px] grid-cols-6 gap-4">
                @foreach ($this->columns as $columnKey => $column)
                    <div class="flex min-h-[620px] flex-col rounded-2xl border bg-gray-50/60 {{ $column['accent'] }} transition dark:bg-gray-900/40"
                        x-bind:class="overStatus === '{{ $columnKey }}' ? 'ring-2 ring-brand-400/60' : ''"
                        x-on:dragenter.prevent="overStatus = '{{ $columnKey }}'"
                        x-on:dragover.prevent
                        x-on:dragleave="if (overStatus === '{{ $columnKey }}') overStatus = null"
                        x-on:drop.prevent="dropTo('{{ $columnKey }}')">
                        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $column['label'] }}</h3>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                {{ count($this->groupedJobs[$columnKey] ?? []) }}
                            </span>
                        </div>

                        <div class="flex-1 space-y-3 overflow-y-auto p-3">
                            @forelse (($this->groupedJobs[$columnKey] ?? []) as $job)
                                <article class="cursor-move rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                                    draggable="true"
                                    x-on:dragstart="startDrag($event, {{ $job->id }}, '{{ $job->status }}')"
                                    x-on:dragend="endDrag()">
                                    <div class="mb-3 flex items-start justify-between gap-2">
                                        <div>
                                            <a href="{{ route('orders.edit', ['order' => $job->order_id]) }}"
                                                class="text-sm font-semibold text-brand-600 hover:underline">
                                                {{ $job->order?->order_no ?? '-' }}
                                            </a>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $job->orderItem?->product?->name ?? '-' }}
                                                · Qty {{ number_format((float) ($job->orderItem?->qty ?? 0), 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            {{ \App\Models\ProductionJob::stageOptions()[$job->stage] ?? ucfirst($job->stage) }}
                                        </span>
                                    </div>

                                    <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                        <div>
                                            Role: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $job->assignedRole?->name ?? '-' }}</span>
                                        </div>
                                        <div>
                                            Claim:
                                            <span class="font-medium {{ $this->isMine($job) ? 'text-emerald-600 dark:text-emerald-300' : 'text-gray-700 dark:text-gray-200' }}">
                                                {{ $job->claimedByUser?->name ?? '-' }}
                                            </span>
                                        </div>
                                        <div>Update: {{ $job->updated_at?->format('d M H:i') ?? '-' }}</div>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if ($columnKey === \App\Models\ProductionJob::STATUS_ANTRIAN)
                                            @can('production.edit')
                                                @if ($this->canClaim($job) && empty($job->claimed_by))
                                                    <button type="button" wire:click="claim({{ $job->id }})" class="btn btn-secondary text-xs">
                                                        Ambil Task
                                                    </button>
                                                @endif
                                                @if ($this->canMove($job))
                                                    <button type="button" wire:click="moveCard({{ $job->id }}, 'desain')" class="btn btn-secondary text-xs">
                                                        Ke Desain
                                                    </button>
                                                    <button type="button" wire:click="moveCard({{ $job->id }}, 'in_progress')" class="btn btn-primary text-xs">
                                                        Mulai Produksi
                                                    </button>
                                                @endif
                                            @endcan
                                        @elseif ($columnKey === 'desain')
                                            @can('production.edit')
                                                @if ($this->canClaim($job) && empty($job->claimed_by))
                                                    <button type="button" wire:click="claim({{ $job->id }})" class="btn btn-secondary text-xs">
                                                        Ambil Task
                                                    </button>
                                                @endif
                                                @if ($this->canMove($job))
                                                    <button type="button" wire:click="moveCard({{ $job->id }}, 'in_progress')" class="btn btn-primary text-xs">
                                                        Lanjut Produksi
                                                    </button>
                                                @endif
                                            @endcan
                                        @elseif ($columnKey === \App\Models\ProductionJob::STATUS_IN_PROGRESS)
                                            @can('production.edit')
                                                @if ($this->canClaim($job) && empty($job->claimed_by))
                                                    <button type="button" wire:click="claim({{ $job->id }})" class="btn btn-secondary text-xs">
                                                        Ambil Task
                                                    </button>
                                                @endif
                                                @if ($this->canMove($job))
                                                    <button type="button" wire:click="markSelesai({{ $job->id }})" class="btn btn-primary text-xs">
                                                        Selesai
                                                    </button>
                                                @endif
                                            @endcan
                                        @elseif ($columnKey === \App\Models\ProductionJob::STATUS_SELESAI)
                                            @can('production.qc')
                                                @if ($this->canMove($job))
                                                    <button type="button" wire:click="moveToQc({{ $job->id }})" class="btn btn-primary text-xs">
                                                        Kirim QC
                                                    </button>
                                                @endif
                                            @endcan
                                        @elseif ($columnKey === \App\Models\ProductionJob::STATUS_QC)
                                            @can('production.qc')
                                                @if ($this->canMove($job))
                                                    <button type="button" wire:click="qcPass({{ $job->id }})" class="btn btn-primary text-xs">
                                                        QC Lulus
                                                    </button>
                                                    <button type="button" wire:click="openQcFail({{ $job->id }})" class="btn btn-secondary text-xs">
                                                        QC Gagal
                                                    </button>
                                                @endif
                                            @endcan
                                        @endif

                                        @can('production.edit')
                                            @if ($this->canRelease($job))
                                                <button type="button" wire:click="release({{ $job->id }})" class="btn btn-secondary text-xs">
                                                    Lepas
                                                </button>
                                            @endif
                                        @endcan

                                        <a href="{{ route('orders.edit', ['order' => $job->order_id]) }}" class="btn btn-secondary text-xs">
                                            Lihat Order
                                        </a>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-300 p-4 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    Belum ada task.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>

    <x-modal wire:model="showQcFailModal" maxWidth="md">
        <x-slot name="header">QC Gagal</x-slot>

        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Alasan QC Gagal</label>
            <textarea wire:model="qcFailNote" rows="4" class="form-input" placeholder="Contoh: warna tidak presisi, cetak ulang."></textarea>
            @error('qcFailNote')
                <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <x-slot name="footer">
            <button type="button" wire:click="closeModal" class="btn btn-secondary">Batal</button>
            <button type="button" wire:click="saveQcFail" class="btn btn-primary">Kembalikan ke Produksi</button>
        </x-slot>
    </x-modal>
</div>
