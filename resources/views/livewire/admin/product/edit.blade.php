<div class="space-y-6">
    <div class="flex items-baseline justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Produk</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Perbarui informasi produk, harga, dan material yang digunakan.
            </p>
        </div>
        <a href="{{ route('products.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
            Kembali
        </a>
    </div>

    <div
        class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/70">
        @if (session('error'))
            <div
                class="rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm font-medium text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="update" class="space-y-8">
            @include('admin.product._form')

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-70 dark:ring-offset-gray-950">
                    <span wire:loading.remove>Simpan Perubahan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
