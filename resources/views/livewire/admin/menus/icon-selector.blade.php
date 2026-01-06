<div>
    <label class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2 block">Ikon</label>
    
    {{-- Icon Preview --}}
    <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2">
            @if($form->icon)
                <div x-data="{}">
                    @if($form->icon)
                            @include('svg.lucide', ['icon' => $form->icon, 'class' => 'w-6 h-6 text-gray-700 dark:text-gray-300'])
                        @endif
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $form->icon }}</span>
            @else
                <div class="w-6 h-6 bg-gray-300 dark:bg-gray-600 rounded flex items-center justify-center">
                    <span class="text-xs text-gray-600 dark:text-gray-400">?</span>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Pilih ikon</span>
            @endif
        </div>
    </div>

    {{-- Icon Search --}}
    <div class="mb-3">
        <input 
            type="text" 
            wire:model.debounce.300ms="iconSearch"
            placeholder="Cari ikon..."
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-white"
        >
    </div>

    {{-- Icon Grid --}}
    <div class="h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-2 bg-white dark:bg-gray-800">
        <div class="grid grid-cols-6 sm:grid-cols-8 gap-2">
            @foreach($this->filteredIconOptions as $iconName => $label)
                <button
                    type="button"
                    wire:click="selectIcon('{{ $iconName }}')"
                    @class([
                        'p-2 border rounded-lg flex items-center justify-center transition-colors group relative',
                        $form->icon === $iconName 
                            ? 'bg-brand-100 dark:bg-brand-900/30 border-brand-500' 
                            : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'
                    ])
                    title="{{ $label }} ({{ $iconName }})"
                >
                    <div class="relative">
                        @include('svg.lucide', ['icon' => $iconName, 'class' => 'w-5 h-5 text-gray-700 dark:text-gray-300 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors'])
                        @if($form->icon === $iconName)
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-brand-500 rounded-full flex items-center justify-center">
                                <x-lucide-check class="w-2 h-2 text-white" />
                            </div>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
        
        @if(empty($this->filteredIconOptions))
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                Tidak ada ikon yang ditemukan untuk "{{ request()->get('iconSearch') }}"
            </div>
        @endif
    </div>

    @error('form.icon')
        <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
    @enderror
</div>