{{-- Hierarchical Menu Table --}}
<div class="space-y-4">
    @include('livewire.components.table.toolbar', ['filtersView' => $filtersView])

    <div class="bg-white rounded-xl shadow dark:bg-gray-900 dark:shadow-none dark:ring-1 dark:ring-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                @php
                    $selectionColumnCheckbox = $this->selectionColumnCheckbox();
                    $columns = $this->columns();
                    $rowActions = $this->rowActions();
                @endphp

                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 text-left py-3">
                            @if ($selectionColumnCheckbox)
                                <input type="checkbox" wire:model.change="selectAll"
                                    class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-brand-500 dark:focus:ring-brand-400/30" />
                            @else
                                <span class="text-gray-500 dark:text-gray-400">#</span>
                            @endif
                        </th>

                        @foreach ($columns as $column)
                            <th class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-900 dark:text-gray-200">{{ $column['label'] }}</span>
                                    @if ($column['sortable'] ?? false)
                                        <button wire:click="sortBy('{{ $column['field'] }}')"
                                            class="text-xs text-gray-500 dark:text-gray-400">
                                            @if ($sortField === $column['field'])
                                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                            @else
                                                ↕
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </th>
                        @endforeach

                        @if ($rowActions)
                            <th class="px-4 py-3 text-right"></th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $parentRow)
                        {{-- Parent Row --}}
                        <tr class="border-t bg-gray-50 dark:bg-gray-800/30 dark:border-gray-800"
                            wire:key="parent-{{ $parentRow->id }}">
                            <td class="px-4 py-3">
                                @if ($selectionColumnCheckbox)
                                    <input type="checkbox" value="{{ $parentRow->id }}" wire:model.change="selected"
                                        class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-brand-500 dark:focus:ring-brand-400/30" />
                                @else
                                    <span class="text-gray-600 dark:text-gray-300">
                                        {{ ($rows->firstItem() ?? 0) + $loop->index }}
                                    </span>
                                @endif
                            </td>

                            @foreach ($columns as $column)
                                <td class="px-4 py-3">
                                    @if ($column['field'] === 'name')
                                        <div class="flex items-center gap-2">
                                            @if($parentRow->icon)
                                                @if ($parentRow->icon)
                                                @include('svg.lucide', ['icon' => $parentRow->icon, 'class' => 'w-4 h-4 text-gray-600 dark:text-gray-400'])
                                            @endif
                                            @endif
                                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $parentRow->name }}</span>
                                            @if ($parentRow->children->count() > 0)
                                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 rounded-full">
                                                    {{ $parentRow->children->count() }} child
                                                </span>
                                            @endif
                                        </div>
                                    @elseif ($column['field'] === 'icon')
                                        @if($parentRow->icon)
                                            @if ($parentRow->icon)
                                                @include('svg.lucide', ['icon' => $parentRow->icon, 'class' => 'w-5 h-5 text-gray-600 dark:text-gray-400'])
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    @elseif (isset($column['view']))
                                        @include($column['view'], ['row' => $parentRow])
                                    @elseif(isset($column['format']) && is_callable($column['format']))
                                        {{ $column['format']($parentRow) ?? '-' }}
                                    @else
                                        <span class="text-gray-900 dark:text-gray-200">
                                            {{ data_get($parentRow, $column['field']) ?? '-' }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach

                            @if ($rowActions)
                                <td class="px-4 py-3 relative text-right" wire:key="action-{{ $parentRow->id }}">
                                    <div x-data="{
                                        open: false,
                                        style: '',
                                        toggle(el) {
                                            const rect = el.getBoundingClientRect();
                                            this.open = !this.open;
                                            if (!this.open) return;
                                            this.$nextTick(() => {
                                                const panel = this.$refs.panel;
                                                const dropdownHeight = panel ? panel.offsetHeight : 160;
                                                const dropdownWidth = panel ? panel.offsetWidth : 192;
                                                const spaceBelow = window.innerHeight - rect.bottom;
                                                const top = spaceBelow < dropdownHeight ?
                                                    rect.top - dropdownHeight - 8 :
                                                    rect.bottom + 8;
                                                const left = Math.min(
                                                    Math.max(8, rect.right - dropdownWidth),
                                                    window.innerWidth - dropdownWidth - 8
                                                );
                                                this.style = `top: ${top}px; left: ${left}px;`;
                                            });
                                        }
                                    }" class="relative">
                                        <button x-ref="trigger" @click="toggle($el)"
                                            class="px-3 py-2 border rounded text-sm inline-flex items-center gap-2 dark:border-gray-700 dark:text-gray-100">
                                            <span>Action</span>
                                            <x-lucide-chevron-up x-show="open"
                                                class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                            <x-lucide-chevron-down x-show="!open"
                                                class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                        </button>

                                        <template x-teleport="body">
                                            <div x-show="open" x-transition @click.outside="open = false"
                                                class="fixed z-50 w-48 bg-white rounded-lg shadow-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800"
                                                x-ref="panel" :style="style">
                                                <ul class="py-1">
                                                    @foreach ($rowActions as $action)
                                                        <li>
                                                            <button
                                                                wire:click="{{ $action['method'] }}({{ $parentRow->id }})"
                                                                @click="open = false"
                                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex items-center gap-2 {{ $action['class'] ?? 'text-gray-700' }}">
                                                                @if (!empty($action['icon']))
                                                                    <x-dynamic-component :component="'lucide-' . $action['icon']"
                                                                        class="w-4 h-4" />
                                                                @endif
                                                                {{ $action['label'] }}
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            @endif
                        </tr>

                        {{-- Child Rows --}}
                        @if ($parentRow->children->count() > 0)
                            @foreach ($parentRow->children->sortBy('order') as $childRow)
                                <tr class="border-t hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/60"
                                    wire:key="child-{{ $childRow->id }}">
                                    <td class="px-4 py-3">
                                        @if ($selectionColumnCheckbox)
                                            <input type="checkbox" value="{{ $childRow->id }}" wire:model.change="selected"
                                                class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-brand-500 dark:focus:ring-brand-400/30" />
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>

                                    @foreach ($columns as $column)
                                        <td class="px-4 py-3">
                                            @if ($column['field'] === 'name')
                                                <div class="flex items-center gap-2">
                                                    <x-lucide-arrow-right class="w-4 h-4 text-gray-400" />
                                                    @if($childRow->icon)
                                                        @if ($childRow->icon)
                                                        @include('svg.lucide', ['icon' => $childRow->icon, 'class' => 'w-4 h-4 text-gray-500 dark:text-gray-400'])
                                                    @endif
                                                    @endif
                                                    <span class="text-gray-700 dark:text-gray-300">{{ $childRow->name }}</span>
                                                </div>
                                            @elseif ($column['field'] === 'icon')
                                                @if($childRow->icon)
                                                    @if ($childRow->icon)
                                                        @include('svg.lucide', ['icon' => $childRow->icon, 'class' => 'w-5 h-5 text-gray-500 dark:text-gray-400'])
                                                    @endif
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            @elseif (isset($column['view']))
                                                @include($column['view'], ['row' => $childRow])
                                            @elseif(isset($column['format']) && is_callable($column['format']))
                                                {{ $column['format']($childRow) ?? '-' }}
                                            @else
                                                <span class="text-gray-700 dark:text-gray-300">
                                                    {{ data_get($childRow, $column['field']) ?? '-' }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach

                                    @if ($rowActions)
                                        <td class="px-4 py-3 relative text-right" wire:key="action-{{ $childRow->id }}">
                                            <div x-data="{
                                                open: false,
                                                style: '',
                                                toggle(el) {
                                                    const rect = el.getBoundingClientRect();
                                                    this.open = !this.open;
                                                    if (!this.open) return;
                                                    this.$nextTick(() => {
                                                        const panel = this.$refs.panel;
                                                        const dropdownHeight = panel ? panel.offsetHeight : 160;
                                                        const dropdownWidth = panel ? panel.offsetWidth : 192;
                                                        const spaceBelow = window.innerHeight - rect.bottom;
                                                        const top = spaceBelow < dropdownHeight ?
                                                            rect.top - dropdownHeight - 8 :
                                                            rect.bottom + 8;
                                                        const left = Math.min(
                                                            Math.max(8, rect.right - dropdownWidth),
                                                            window.innerWidth - dropdownWidth - 8
                                                        );
                                                        this.style = `top: ${top}px; left: ${left}px;`;
                                                    });
                                                }
                                            }" class="relative">
                                                <button x-ref="trigger" @click="toggle($el)"
                                                    class="px-3 py-2 border rounded text-sm inline-flex items-center gap-2 dark:border-gray-700 dark:text-gray-100">
                                                    <span>Action</span>
                                                    <x-lucide-chevron-up x-show="open"
                                                        class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                                    <x-lucide-chevron-down x-show="!open"
                                                        class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                                </button>

                                                <template x-teleport="body">
                                                    <div x-show="open" x-transition @click.outside="open = false"
                                                        class="fixed z-50 w-48 bg-white rounded-lg shadow-lg dark:bg-gray-900 dark:ring-1 dark:ring-gray-800"
                                                        x-ref="panel" :style="style">
                                                        <ul class="py-1">
                                                            @foreach ($rowActions as $action)
                                                                <li>
                                                                    <button
                                                                        wire:click="{{ $action['method'] }}({{ $childRow->id }})"
                                                                        @click="open = false"
                                                                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex items-center gap-2 {{ $action['class'] ?? 'text-gray-700' }}">
                                                                        @if (!empty($action['icon']))
                                                                            <x-dynamic-component :component="'lucide-' . $action['icon']"
                                                                                class="w-4 h-4" />
                                                                        @endif
                                                                        {{ $action['label'] }}
                                                                    </button>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + ($rowActions ? 2 : 1) }}"
                                class="py-6 text-center text-gray-500">
                                No data
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('livewire.components.table.pagination', ['paginator' => $rows])

    @if (!empty($this->formView()))
        @include('livewire.components.modal.form')
    @endif

    @include('livewire.components.modal.delete')
    @include('livewire.components.modal.bulk-delete')
</div>