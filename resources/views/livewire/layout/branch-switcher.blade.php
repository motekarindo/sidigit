@php
    $hasBranches = !empty($branches);
@endphp

@if ($hasBranches)
    <div class="hidden items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-600 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 sm:flex">
        <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
            Cabang
        </span>

        @if (count($branches) > 1)
            <select wire:model="activeBranchId"
                class="bg-transparent text-sm font-semibold text-gray-800 focus:outline-none dark:text-gray-100">
                @if ($isSuperAdmin)
                    <option value="">Semua Cabang</option>
                @endif
                @foreach ($branches as $branch)
                    <option value="{{ $branch['id'] }}">
                        {{ $branch['name'] }}{{ $branch['is_main'] ? ' (Induk)' : '' }}
                    </option>
                @endforeach
            </select>
        @else
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                {{ $branches[0]['name'] ?? 'Cabang' }}
            </span>
        @endif
    </div>
@endif
