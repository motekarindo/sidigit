<div class="space-y-3">
    <x-forms.input label="Nama Satuan" name="form.name" placeholder="Nama Satuan" wire:model.blur="form.name" required />
    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" wire:model="form.is_dimension"
            class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900">
        Gunakan satuan ini untuk ukuran (Panjang & Lebar)
    </label>
</div>
