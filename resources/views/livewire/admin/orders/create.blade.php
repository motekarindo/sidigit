<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card class="rounded-3xl p-4 shadow-theme-sm">
        <form wire:submit.prevent="save" class="space-y-8" novalidate>
            <div class="sticky top-20 z-30 -mx-2 rounded-2xl border border-gray-200/80 bg-white/95 p-2 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="saveWithStatus('draft')" class="btn btn-secondary">
                            Simpan Draft
                        </button>
                        <button type="button" wire:click="saveWithStatus('quotation')" class="btn btn-secondary">
                            Simpan & Buat Quotation
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary">
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </div>
            </div>

            @include('admin.orders._form')
        </form>
    </x-card>
</div>
