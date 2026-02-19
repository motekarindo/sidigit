<div class="w-full rounded-xl border border-gray-200 bg-white p-4  dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-col gap-4 md:flex-row md:items-end">
        <div class="w-full md:flex-1">
            <x-forms.searchable-select label="Menu:" :options="$this->menuOptions" placeholder="Semua Menu"
                wire:model.live="filters.menu_id" />
        </div>

        <div class="w-full md:flex-1">
            <x-forms.searchable-select
                label="Urutkan berdasarkan"
                :options="[
                    ['id' => 'desc', 'name' => 'Terbaru'],
                    ['id' => 'asc', 'name' => 'Terlama'],
                ]"
                placeholder="Pilih urutan"
                wire:model.live="filters.created_at"
            />
        </div>

        <div class="flex items-end">
            <button type="button" wire:click="resetFilters" class="btn btn-danger">
                Reset Filter
            </button>
        </div>
    </div>
</div>
