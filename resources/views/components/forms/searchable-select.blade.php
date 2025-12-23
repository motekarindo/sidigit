@props([
    'label' => null,
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'placeholder' => 'Pilih...',
])

<div x-data="{
    open: false,
    search: '',
    value: @entangle($attributes->wire('model')),
    get filtered() {
        const term = this.search.toLowerCase();
        return this.options.filter(o => String(o.label).toLowerCase().includes(term));
    },
    options: @js(
    collect($options)
        ->map(
            fn($opt) => [
                'value' => data_get($opt, $optionValue),
                'label' => data_get($opt, $optionLabel),
            ],
        )
        ->values(),
),
    labelFor(value) {
        const found = this.options.find(o => String(o.value) === String(value));
        return found ? found.label : '';
    },
}" class="relative">
    @if ($label)
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</label>
    @endif

    <button type="button" @click="open = !open" class="form-input mt-2 text-left">
        <span x-text="value ? labelFor(value) : '{{ $placeholder }}'"
            :class="value ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500'"></span>
    </button>

    <div x-show="open" @click.away="open = false" x-cloak
        class="absolute z-50 mt-2 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
        <div class="p-2">
            <input type="text" x-model="search" placeholder="Cari..." class="form-input" />
        </div>
        <ul class="max-h-56 overflow-auto py-1">
            <template x-for="opt in filtered" :key="opt.value">
                <li>
                    <button type="button" @click="value = opt.value; open = false; search = ''"
                        :class="String(value) === String(opt.value) ?
                            'bg-brand-50 text-brand-700 dark:bg-gray-800 dark:text-brand-300' :
                            'text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800'"
                        class="w-full px-3 py-2 text-left text-sm">
                        <span x-text="opt.label"></span>
                    </button>
                </li>
            </template>
            <template x-if="filtered.length === 0">
                <li class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</li>
            </template>
        </ul>
    </div>
</div>
