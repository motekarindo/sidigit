<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @php
        $sourceOptions = collect([
            ['value' => 'all', 'label' => 'Semua'],
            ['value' => 'manual', 'label' => 'Manual saja'],
            ['value' => 'order', 'label' => 'Order'],
            ['value' => 'expense', 'label' => 'Expense'],
        ]);
    @endphp
    <div>
        <x-forms.searchable-select
            label="Sumber"
            :options="$sourceOptions"
            optionValue="value"
            optionLabel="label"
            placeholder="Pilih sumber"
            wire:model.live="filters.source"
        />
    </div>
</div>
