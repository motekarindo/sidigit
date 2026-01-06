<div class="flex items-center gap-1">
    <button 
        wire:click="moveUp({{ $row->id }})"
        class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-md border border-gray-200 dark:border-gray-700 transition-colors"
        title="Naik">
        <x-lucide-chevron-up class="w-4 h-4" />
    </button>
    <button 
        wire:click="moveDown({{ $row->id }})"
        class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md border border-gray-200 dark:border-gray-700 transition-colors"
        title="Turun">
        <x-lucide-chevron-down class="w-4 h-4" />
    </button>
</div>