<div class="space-y-4">
    <x-forms.input label="Nama Permission" name="form.name" placeholder="Nama permission" wire:model.blur="form.name" />

    <x-forms.input label="Slug" name="form.slug" placeholder="Slug permission" wire:model.blur="form.slug" />

    <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Menu</label>
        <select wire:model.blur="form.menu_id" class="form-input mt-2">
            <option value="">Pilih Menu</option>
            @foreach ($this->menuOptions as $menu)
                <option value="{{ $menu->id }}">{{ $menu->name }}</option>
            @endforeach
        </select>
        @error('form.menu_id')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
