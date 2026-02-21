@php
    $orderId = $row->ref_id ?? null;
    $orderNo = $row->order_no ?? ($orderId ? 'Order #' . $orderId : '-');
@endphp

@if ($orderId)
    <a href="{{ route('orders.edit', ['order' => $orderId]) }}" class="text-brand-600 hover:underline" target="_blank" rel="noopener">
        {{ $orderNo }}
    </a>
@else
    <span class="text-gray-500">-</span>
@endif
