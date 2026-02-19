<div class="space-y-3">
    <x-forms.input label="Nama Finishing" name="form.name" placeholder="Nama finishing" wire:model.blur="form.name" required />
    <x-forms.input label="Harga" name="form.price" placeholder="0" wire:model.blur="form.price" type="number" step="0.01" min="0" />
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
