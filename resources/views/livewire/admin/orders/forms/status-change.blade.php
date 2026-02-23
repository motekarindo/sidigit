@php
    $controlClass = 'mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $statusLabel = collect($statusOptions ?? [])->firstWhere('value', $statusCurrent)['label'] ?? ucfirst((string) $statusCurrent);
@endphp

<div class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-200">
        <span class="font-medium">Status saat ini:</span>
        <span>{{ $statusLabel }}</span>
    </div>

    <div>
        <x-forms.searchable-select
            label="Status Baru"
            :options="$statusOptions"
            optionValue="value"
            optionLabel="label"
            placeholder="Pilih status baru"
            wire:model.live="statusNext"
            :button-class="$controlClass"
            required
        />
        @error('statusNext')
            <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
        @enderror
    </div>

    @if (!empty($statusRequiresReason))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">
            Anda menurunkan status dari fase approval/proses. Alasan revisi wajib diisi.
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Alasan Revisi <span class="text-red-500">*</span>
            </label>
            <textarea
                wire:model.live="statusRevisionReason"
                rows="3"
                class="{{ $controlClass }}"
                placeholder="Contoh: customer meminta revisi file desain."
                required
            ></textarea>
            @error('statusRevisionReason')
                <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
            @enderror
        </div>
    @endif
</div>
