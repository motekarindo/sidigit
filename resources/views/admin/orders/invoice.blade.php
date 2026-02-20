@extends('layouts.invoice')

@section('title', 'Invoice ' . ($order->order_no ?? ''))

@php
    $customer = $order->customer;
    $issuedAt = $order->order_date?->format('d M Y') ?? now()->format('d M Y');
    $dueAt = $order->deadline?->format('d M Y') ?? '-';
    $status = $order->status ?? 'draft';
    $payments = $order->payments?->sortBy('paid_at') ?? collect();
    $paidAmount = (float) ($order->paid_amount ?? 0);
    $balance = max(0, (float) ($order->grand_total ?? 0) - $paidAmount);
    $statusLabel = [
        'draft' => 'Draft',
        'desain' => 'Desain',
        'approve' => 'Approve',
        'produksi' => 'Produksi',
        'diambil' => 'Diambil',
        'selesai' => 'Selesai',
    ][$status] ?? ucfirst($status);
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 no-print">
        <a href="{{ route('orders.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:border-brand-200 hover:text-brand-600">
            Kembali ke Order
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('orders.invoice', ['order' => $order->id, 'print' => 1]) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:border-brand-200 hover:text-brand-600">
                Print Invoice
            </a>
            <a href="{{ route('orders.invoice.pdf', $order->id) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600">
                Download PDF
            </a>
        </div>
    </div>

    <div class="mt-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-lg font-semibold text-gray-900">{{ config('app.name', 'Sidigit') }}</p>
                <p class="text-[10px] text-gray-500">Invoice untuk order percetakan digital</p>
                <p class="text-[10px] text-gray-500">Email: {{ config('mail.from.address', '-') }}</p>
            </div>
            <div class="text-left lg:text-right">
                <p class="text-base font-semibold text-gray-900">INVOICE</p>
                <p class="text-[10px] text-gray-500">Invoice No</p>
                <p class="text-sm font-semibold text-gray-900">{{ $order->order_no }}</p>
                <p class="mt-1 text-[10px] text-gray-500">Status: <span class="font-semibold text-gray-900">{{ $statusLabel }}</span></p>
                <p class="text-[10px] text-gray-500">Issued: <span class="font-semibold text-gray-900">{{ $issuedAt }}</span></p>
                <p class="text-[10px] text-gray-500">Due: <span class="font-semibold text-gray-900">{{ $dueAt }}</span></p>
            </div>
        </div>

        <div class="mt-3 grid grid-cols-1 gap-3 lg:grid-cols-3 text-[10px]">
            <div>
                <p class="font-semibold uppercase text-gray-500">From</p>
                <p class="mt-1 font-semibold text-gray-900">{{ config('app.name', 'Sidigit') }}</p>
                <p class="text-gray-500">Alamat belum diatur.</p>
                <p class="text-gray-500">{{ config('mail.from.address', '-') }}</p>
            </div>
            <div>
                <p class="font-semibold uppercase text-gray-500">To</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $customer?->name ?? 'Umum' }}</p>
                <p class="text-gray-500">{{ $customer?->address ?? '-' }}</p>
                <p class="text-gray-500">{{ $customer?->phone_number ?? '-' }}</p>
                <p class="text-gray-500">{{ $customer?->email ?? '-' }}</p>
            </div>
            <div>
                <p class="font-semibold uppercase text-gray-500">Ringkasan</p>
                <div class="mt-1 space-y-0.5 text-[10px] text-gray-600">
                    <div class="flex items-center justify-between">
                        <span>Sub Total</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Diskon</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format((float) $order->total_discount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Total</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Terbayar</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Sisa</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 overflow-x-auto">
            <table class="w-full min-w-[640px] text-[10px]">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500">
                        <th class="pb-2">S.No</th>
                        <th class="pb-2">Produk</th>
                        <th class="pb-2">Bahan</th>
                        <th class="pb-2">Ukuran</th>
                        <th class="pb-2 text-right">Qty</th>
                        <th class="pb-2 text-right">Unit Cost</th>
                        <th class="pb-2 text-right">Diskon</th>
                        <th class="pb-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        @php
                            $size = $item->length_cm && $item->width_cm
                                ? number_format((float) $item->length_cm, 2, ',', '.') . ' x ' . number_format((float) $item->width_cm, 2, ',', '.') . ' cm'
                                : '-';
                            $finishNames = $item->finishes
                                ->map(fn ($finish) => $finish->finish?->name)
                                ->filter()
                                ->implode(', ');
                        @endphp
                        <tr>
                            <td class="py-1 text-gray-600">{{ $loop->iteration }}</td>
                            <td class="py-1">
                                <p class="font-semibold text-gray-900">{{ $item->product?->name ?? '-' }}</p>
                                @if (!empty($finishNames))
                                    <p class="text-[9px] text-gray-500">Finishing: {{ $finishNames }}</p>
                                @endif
                            </td>
                            <td class="py-1 text-gray-600">{{ $item->material?->name ?? '-' }}</td>
                            <td class="py-1 text-gray-600">{{ $size }}</td>
                            <td class="py-1 text-right text-gray-600">{{ number_format((float) $item->qty, 0, ',', '.') }}</td>
                            <td class="py-1 text-right text-gray-600">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                            <td class="py-1 text-right text-gray-600">Rp {{ number_format((float) $item->discount, 0, ',', '.') }}</td>
                            <td class="py-1 text-right font-semibold text-gray-900">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-2 text-[10px] text-gray-600">
            <span class="font-semibold text-gray-700">Catatan:</span>
            {{ $order->notes ?: 'Tidak ada catatan.' }}
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 no-print">
            <a href="{{ route('orders.edit', $order->id) }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:border-brand-200 hover:text-brand-600">
                Lanjutkan Pembayaran
            </a>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600">
                Print
            </button>
        </div>

        <div class="mt-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Riwayat Pembayaran</h3>
                <span class="text-[9px] font-semibold text-gray-500">Total: Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
            </div>
            <div class="mt-1 overflow-x-auto rounded-2xl border border-gray-200">
                <table class="w-full min-w-[520px] text-[10px]">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-2 py-1.5">Tanggal</th>
                            <th class="px-2 py-1.5">Metode</th>
                            <th class="px-2 py-1.5">Catatan</th>
                            <th class="px-2 py-1.5 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="px-2 py-1.5 text-gray-600">{{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="px-2 py-1.5 text-gray-600">{{ ucfirst($payment->method ?? '-') }}</td>
                                <td class="px-2 py-1.5 text-gray-600">{{ $payment->notes ?? '-' }}</td>
                                <td class="px-2 py-1.5 text-right font-semibold text-gray-900">
                                    Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-2 py-2 text-center text-gray-500">
                                    Belum ada pembayaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (!empty($print))
        @push('scripts')
            <script>
                window.addEventListener('DOMContentLoaded', () => {
                    setTimeout(() => window.print(), 300);
                });
            </script>
        @endpush
    @endif
@endsection
