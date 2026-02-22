<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card class="rounded-3xl p-4 shadow-theme-sm">
        <form wire:submit.prevent="save" class="space-y-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if (($status ?? 'draft') === 'draft')
                        <button type="button" wire:click="makeQuotation" class="btn btn-secondary">
                            Buat Quotation
                        </button>
                    @endif
                    @if (($status ?? 'draft') === 'quotation')
                        <button type="button" wire:click="approveQuotation" class="btn btn-secondary">
                            Approve Quotation
                        </button>
                    @endif
                    @if (in_array(($status ?? 'draft'), ['draft', 'approval'], true))
                        <a href="{{ route('orders.invoice', ['order' => $orderId]) }}" class="btn btn-secondary">
                            Buat Invoice
                        </a>
                    @endif
                    @if (($status ?? 'draft') !== 'draft')
                        <a href="{{ route('orders.quotation', ['order' => $orderId, 'print' => 1]) }}" target="_blank"
                            class="btn btn-secondary">
                            Print Quotation
                        </a>
                    @endif
                    @if (in_array(($status ?? 'draft'), ['draft', 'approval'], true))
                        <a href="{{ route('orders.invoice', ['order' => $orderId, 'print' => 1]) }}" target="_blank"
                            class="btn btn-secondary">
                            Print Invoice
                        </a>
                    @endif
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary">
                        <span wire:loading.remove wire:target="save">Simpan</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </div>

            @include('admin.orders._form')
        </form>
    </x-card>
</div>
