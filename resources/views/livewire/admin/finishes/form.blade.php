<div class="space-y-3">
    <x-forms.input label="Nama Finishing" name="form.name" placeholder="Nama finishing" wire:model.blur="form.name" required />
    <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Harga</label>
        <div class="mt-2" x-data="rupiahField(@entangle('form.price').live)">
            <input type="text" inputmode="numeric" x-model="display" @input="onInput" class="form-input" />
        </div>
        @error('form.price')
            <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-forms.searchable-select
            label="Satuan (Opsional)"
            :options="$this->unitOptions"
            placeholder="Pilih satuan"
            wire:model="form.unit_id"
        />
    </div>
    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" wire:model="form.is_active"
            class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
        Aktif
    </label>
</div>
