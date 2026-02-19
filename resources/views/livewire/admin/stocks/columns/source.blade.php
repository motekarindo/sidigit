@php
    $ref = $row->ref_type ?? 'manual';
    $label = match ($ref) {
        'order' => 'Order',
        'expense' => 'Expense',
        'manual' => 'Manual',
        default => ucfirst((string) $ref),
    };
    $badgeClass = match ($ref) {
        'order' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200',
        'expense' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
        'manual' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
        default => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200',
    };

    $href = null;

    if ($ref === 'order' && $row->ref_id) {
        $href = route('orders.edit', ['order' => $row->ref_id]);
    }

    if ($ref === 'expense' && $row->ref_id) {
        $href = route('expenses.materials.index', ['edit' => $row->ref_id]);
    }
@endphp

@if ($href)
    <a href="{{ $href }}" class="inline-flex" target="_blank" rel="noopener">
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
            {{ $label }} #{{ $row->ref_id }}
        </span>
    </a>
@else
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
        {{ $label }}
    </span>
@endif
