@php
    $material = $material ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nama Material
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', optional($material)->name) }}" required
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('name'),
                ])>
            @error('name')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-forms.searchable-select
                label="Kategori"
                name="category_id"
                :options="$categories"
                placeholder="Pilih kategori"
                :selected="old('category_id', optional($material)->category_id)"
                required
            />
            @error('category_id')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="reorder_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Batas Minimum (Reorder Level)
            </label>
            <input type="number" step="0.01" min="0" id="reorder_level" name="reorder_level"
                value="{{ old('reorder_level', optional($material)->reorder_level) }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark;border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('reorder_level'),
                ])>
            @error('reorder_level')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="cost_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Harga Pokok per Unit
            </label>
            <div class="mt-2" x-data="rupiahField(@js(old('cost_price', optional($material)->cost_price)))">
                <input type="text" id="cost_price" inputmode="numeric" autocomplete="off" x-model="display"
                    @input="onInput"
                    @class([
                        'block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                        'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('cost_price'),
                    ])>
                <input type="hidden" name="cost_price" x-model="value">
            </div>
            @error('cost_price')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-5">
        <div>
            <x-forms.searchable-select
                label="Satuan Dasar (Stok)"
                name="unit_id"
                :options="$units"
                placeholder="Pilih satuan"
                :selected="old('unit_id', optional($material)->unit_id)"
                required
            />
            @error('unit_id')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-forms.searchable-select
                label="Satuan Pembelian (Opsional)"
                name="purchase_unit_id"
                :options="$units"
                placeholder="Pilih satuan pembelian"
                :selected="old('purchase_unit_id', optional($material)->purchase_unit_id)"
            />
            @error('purchase_unit_id')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Contoh: 1 RIM = 500 PCS.
            </p>
        </div>

        <div>
            <label for="conversion_qty" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Konversi ke Satuan Dasar
            </label>
            <input type="number" step="0.01" min="0.01" id="conversion_qty" name="conversion_qty"
                value="{{ old('conversion_qty', optional($material)->conversion_qty ?? 1) }}"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark;border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-400' => $errors->has('conversion_qty'),
                ])>
            @error('conversion_qty')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Deskripsi (Opsional)
            </label>
            <textarea id="description" name="description" rows="6"
                @class([
                    'mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark;border-gray-700 dark:bg-gray-900 dark:text-gray-100',
                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark;border-error-400' => $errors->has('description'),
                ])>{{ old('description', optional($material)->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
