@php
    $hasMaterials = count($materialsAll ?? []) > 0;
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="space-y-5">
            <div>
                <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    SKU <span class="text-red-500">*</span>
                </label>
                <input type="text" id="sku" wire:model.defer="sku" required placeholder="SKU produk" @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                        'sku'),
                ])>
                @error('sku')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Produk <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" wire:model.defer="name" required placeholder="Nama produk" @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                        'name'),
                ])>
                @error('name')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div>
                    <label for="base_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Pokok (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2" x-data="rupiahField(@entangle('base_price').live)">
                        <input type="text" id="base_price" placeholder="0" inputmode="numeric" autocomplete="off"
                            x-model="display" @input="onInput"
                            required @class([
                                'block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                                'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                                    'base_price'),
                            ])>
                    </div>
                    @error('base_price')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Jual (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2" x-data="rupiahField(@entangle('sale_price').live)">
                        <input type="text" id="sale_price" placeholder="0" inputmode="numeric" autocomplete="off"
                            x-model="display" @input="onInput"
                            required @class([
                                'block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                                'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                                    'sale_price'),
                            ])>
                    </div>
                    @error('sale_price')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <x-forms.searchable-select
                    label="Satuan"
                    :options="$units"
                    placeholder="Pilih satuan"
                    wire:model.live="unit_id"
                    required
                />
                @error('unit_id')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div id="dimension-fields" @class([
                'grid grid-cols-1 gap-5 lg:grid-cols-2',
                'hidden' => !$showDimensionFields,
            ])>
                <div>
                    <label for="length_cm" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Panjang (cm)
                    </label>
                    <input type="number" step="0.01" min="0" id="length_cm" wire:model.defer="length_cm" placeholder="0"
                        @disabled(!$showDimensionFields) @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                                'length_cm'),
                        ])>
                    @error('length_cm')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="width_cm" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Lebar (cm)
                    </label>
                    <input type="number" step="0.01" min="0" id="width_cm" wire:model.defer="width_cm" placeholder="0"
                        @disabled(!$showDimensionFields) @class([
                            'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has(
                                'width_cm'),
                        ])>
                    @error('width_cm')
                        <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div>
                <x-forms.searchable-select
                    label="Kategori Produk"
                    :options="$categories"
                    placeholder="Pilih kategori"
                    wire:model.live="category_id"
                    required
                />
                @error('category_id')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Bahan <span class="text-red-500">*</span>
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Pilih bahan yang digunakan untuk produk ini. Gunakan <kbd
                        class="rounded border px-1 py-0.5 text-[11px]">Ctrl</kbd>/<kbd
                        class="rounded border px-1 py-0.5 text-[11px]">Cmd</kbd> untuk memilih lebih dari satu.
                </p>

                <div class="mt-3 space-y-3">
                    <p id="materials-hint-empty" @class([
                        'rounded-xl border border-dashed border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-500/40 dark:bg-warning-500/10 dark:text-warning-200',
                        'hidden' => $hasMaterials,
                    ])>
                        Belum ada bahan yang tersedia. Tambahkan bahan terlebih dahulu di
                        <a href="{{ route('materials.index') }}" target="_blank" rel="noopener noreferrer"
                            class="font-semibold underline underline-offset-2">
                            halaman bahan
                        </a>.
                        <button type="button" wire:click="reloadMaterials"
                            class="ml-2 inline-flex items-center rounded-full border border-warning-300 px-2.5 py-1 text-xs font-semibold text-warning-700 transition hover:bg-warning-100 dark:border-warning-400/40 dark:text-warning-100 dark:hover:bg-warning-500/10">
                            Muat ulang bahan
                        </button>
                    </p>

                    <div id="product-material-select" @class(['hidden' => !$hasMaterials])>
                        @php
                            $materialOptions = collect($materialsAll ?? [])->map(function ($material) {
                                $categoryLabel = $material['category'] ?? null;
                                $unitLabel = $material['unit'] ?? null;

                                $categoryText = $categoryLabel ?: 'Tanpa kategori';
                                $unitText = $unitLabel ?: 'Tanpa satuan';

                                return [
                                    'id' => $material['id'],
                                    'label' => $material['name'] . ' - ' . $categoryText . ' - ' . $unitText,
                                ];
                            })->values()->all();
                        @endphp
                        <livewire:components.searchable-multi-select
                            :options="$materialOptions"
                            optionLabelKey="label"
                            optionValueKey="id"
                            placeholder="Pilih material"
                            wire:model="materials"
                            :required="true"
                            :key="'materials-all'"
                        />
                    </div>
                </div>

                @error('materials')
                    <p class="mt-2 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
                @error('materials.*')
                    <p class="mt-2 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <livewire:components.text-editor
                    label="Deskripsi Produk (Opsional)"
                    placeholder="Tulis deskripsi produk"
                    wire:model="product_description"
                />
                @error('product_description')
                    <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
