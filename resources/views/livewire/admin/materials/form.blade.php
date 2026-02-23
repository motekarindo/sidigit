<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <x-forms.input label="Nama Material" name="form.name" placeholder="Nama material"
            wire:model.blur="form.name" required />

        <div>
            <x-forms.searchable-select
                label="Kategori"
                :options="$this->categoryOptions"
                placeholder="Pilih kategori"
                wire:model="form.category_id"
                required
            />
            @error('form.category_id')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <x-forms.input label="Batas Minimum (Reorder Level)" name="form.reorder_level" placeholder="0"
            wire:model.blur="form.reorder_level" type="number" step="0.01" min="0" />

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Harga Pokok per Unit (Satuan Dasar)</label>
            <div class="mt-2" x-data="rupiahField(@entangle('form.cost_price').live)">
                <input type="text" inputmode="numeric" x-model="display" @input="onInput" class="form-input" />
            </div>
            @error('form.cost_price')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <x-forms.searchable-select
                label="Satuan Dasar (Stok)"
                :options="$this->unitOptions"
                placeholder="Pilih satuan"
                wire:model="form.unit_id"
                required
            />
            @error('form.unit_id')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-forms.searchable-select
                label="Satuan Pembelian (Opsional)"
                :options="$this->unitOptions"
                placeholder="Pilih satuan pembelian"
                wire:model="form.purchase_unit_id"
            />
            @error('form.purchase_unit_id')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Contoh: 1 RIM = 500 PCS.</p>
        </div>

        <x-forms.input label="Konversi ke Satuan Dasar" name="form.conversion_qty" placeholder="1"
            wire:model.blur="form.conversion_qty" type="number" step="0.01" min="0.01" />

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-forms.input label="Lebar Roll (cm) - Opsional" name="form.roll_width_cm" placeholder="Contoh: 320"
                wire:model.blur="form.roll_width_cm" type="number" step="0.01" min="1" />
            <x-forms.input label="Waste Roll (%) - Opsional" name="form.roll_waste_percent" placeholder="Contoh: 3"
                wire:model.blur="form.roll_waste_percent" type="number" step="0.01" min="0" max="100" />
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 -mt-2">
            Jika diisi, pemakaian bahan order akan dihitung berbasis lebar roll + waste. Jika kosong, sistem pakai hitung area standar.
        </p>

        <div>
            <livewire:components.text-editor
                label="Deskripsi"
                placeholder="Tulis deskripsi material"
                wire:model="form.description"
            />
            @error('form.description')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
