@props(['order'])

<div class="border border-[#e5e2de] bg-white print:border-none print:shadow-none mb-4" id="invoice-card">
    {{-- Order Number & Date --}}
    <div class="p-5 border-b border-[#e5e2de] flex items-center justify-between">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-[#9e9b96] mb-1">Nomor Pesanan</p>
            <p class="font-mono text-[15px] font-bold text-[#1c1c1a]">{{ $order->order_number }}</p>
        </div>
        <div class="text-right">
            <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-[#9e9b96] mb-1">Tanggal</p>
            <p class="font-sans text-[13px] text-[#615e57]">{{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

    {{-- Items --}}
    <div class="p-5 border-b border-[#e5e2de]">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-[#9e9b96] mb-4">Item Pesanan</p>
        <div class="space-y-3">
            @foreach($order->items as $item)
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-sans text-[13px] font-medium text-[#1c1c1a] truncate">{{ $item->name ?? $item->product?->name ?? '-' }}</p>
                        <p class="font-sans text-[11px] text-[#9e9b96]">x{{ $item->quantity }}</p>
                    </div>
                    <p class="font-sans text-[13px] text-[#1c1c1a] shrink-0">
                        Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Totals --}}
    <div class="p-5 border-b border-[#e5e2de] space-y-2">
        <div class="flex justify-between text-[13px] text-[#615e57]">
            <span class="font-sans">Subtotal</span>
            <span class="font-sans">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($order->shipping_cost > 0)
            <div class="flex justify-between text-[13px] text-[#615e57]">
                <span class="font-sans">Ongkos Kirim <span class="uppercase text-[11px]">({{ str_replace('_', ' ', $order->courier ?? '') }})</span></span>
                <span class="font-sans">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
            </div>
        @endif
        @if($order->discount_total > 0)
            <div class="flex justify-between text-[13px] text-green-600">
                <span class="font-sans">Diskon</span>
                <span class="font-sans">- Rp {{ number_format($order->discount_total, 0, ',', '.') }}</span>
            </div>
        @endif
        @php
            $fee = (int)$order->grand_total - (int)$order->subtotal + (int)($order->discount_total ?? 0) - (int)$order->shipping_cost;
        @endphp
        @if($fee > 0)
            <div class="flex justify-between text-[13px] text-[#615e57]">
                <span class="font-sans">Biaya Layanan</span>
                <span class="font-sans">Rp {{ number_format($fee, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="flex justify-between text-[14px] font-bold text-[#1c1c1a] pt-2 border-t border-[#e5e2de]">
            <span class="font-sans">Total</span>
            <span class="font-sans">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Payment Method --}}
    <div class="p-5 border-b border-[#e5e2de]">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-[#9e9b96] mb-2">Metode Pembayaran</p>
        <p class="font-sans text-[14px] font-semibold text-[#1c1c1a]">{{ $order->payment_method }}</p>

        @if($order->payment_status === 'pending' && $order->payment_url)
            <a href="{{ $order->payment_url }}" target="_blank"
               class="inline-flex items-center gap-2 mt-3 bg-[#064e3b]/10 text-[#064e3b] hover:bg-[#064e3b]/20 transition-colors px-4 py-2 text-[12px] font-mono font-bold uppercase tracking-wider print:hidden">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Selesaikan Pembayaran
            </a>
            <p class="hidden print:block mt-2 text-xs font-medium text-[#b91c1c]">Status: Menunggu Pembayaran</p>
        @elseif($order->payment_status === 'paid')
            <span class="inline-flex items-center gap-1 mt-3 bg-green-50 text-green-700 px-3 py-1 text-[11px] font-mono uppercase tracking-wider rounded">
                ✓ Lunas
            </span>
        @endif
    </div>

    {{-- Shipping Address --}}
    @php $addr = $order->shipping_address; @endphp
    @if($addr && ($addr['name'] ?? null))
        <div class="p-5">
            <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-[#9e9b96] mb-2">Dikirim ke</p>
            <p class="font-sans text-[13px] font-semibold text-[#1c1c1a]">{{ $addr['name'] ?? '' }}</p>
            <p class="font-sans text-[12px] text-[#615e57] mt-0.5">{{ $addr['phone'] ?? '' }}</p>
            <p class="font-sans text-[12px] text-[#615e57] mt-1 leading-relaxed">
                {{ collect([$addr['address'] ?? '', $addr['district'] ?? '', $addr['city'] ?? '', $addr['province'] ?? '', $addr['postal_code'] ?? ''])->filter()->implode(', ') }}
            </p>
        </div>
    @endif
</div>
