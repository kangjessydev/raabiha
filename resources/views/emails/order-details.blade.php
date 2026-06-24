@extends('emails.layout')

@section('content_details')
    <div style="margin-bottom: 20px; font-size: 13px; color: #615e57; border-bottom: 1px solid #e5e1d8; padding-bottom: 15px;">
        <strong>Nomor Pesanan:</strong> <span style="font-family: monospace; font-size: 14px; font-weight: bold; color: #222523;">#{{ $order->order_number }}</span><br>
        <strong>Tanggal Pesanan:</strong> {{ $order->created_at->format('d M Y, H:i') }}
    </div>

    <h3 style="font-family: 'Playfair Display', Georgia, serif; font-size: 16px; border-bottom: 1px solid #e5e1d8; padding-bottom: 8px; margin-top: 30px; margin-bottom: 15px; color: #222523;">Item Pesanan</h3>
    <table class="details-table" style="width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 13px;">
        <thead>
            <tr style="background-color: #f2efe8;">
                <th style="padding: 10px 12px; text-align: left; font-family: 'JetBrains Mono', monospace; font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #615e57; border-bottom: 1px solid #e5e1d8;">Produk</th>
                <th class="text-right" style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #615e57; border-bottom: 1px solid #e5e1d8; width: 60px;">Qty</th>
                <th class="text-right" style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #615e57; border-bottom: 1px solid #e5e1d8; width: 120px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f2efe8; color: #222523;">
                        <span class="bold">{{ $item->name ?? $item->product?->name ?? '-' }}</span>
                        @if($item->selected_attributes)
                            <br><span style="font-size: 11px; color: #615e57;">Varian: {{ is_array($item->selected_attributes) ? implode(', ', $item->selected_attributes) : (is_string($item->selected_attributes) ? $item->selected_attributes : '') }}</span>
                        @endif
                    </td>
                    <td class="text-right" style="padding: 12px; border-bottom: 1px solid #f2efe8; text-align: right; color: #222523;">x{{ $item->quantity }}</td>
                    <td class="text-right price" style="padding: 12px; border-bottom: 1px solid #f2efe8; text-align: right; font-family: 'JetBrains Mono', monospace; color: #222523;">Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            
            <tr>
                <td colspan="2" class="text-right" style="padding: 10px 12px; text-align: right; border-top: 1px solid #e5e1d8; color: #615e57;">Subtotal</td>
                <td class="text-right price" style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; border-top: 1px solid #e5e1d8; color: #222523;">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td>
            </tr>
            
            @if($order->shipping_cost > 0)
                <tr>
                    <td colspan="2" class="text-right" style="padding: 10px 12px; text-align: right; color: #615e57;">Ongkos Kirim ({{ strtoupper(str_replace('_', ' ', $order->courier ?? '')) }})</td>
                    <td class="text-right price" style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: #222523;">Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                </tr>
            @endif
            
            @if($order->discount_total > 0)
                <tr>
                    <td colspan="2" class="text-right" style="padding: 10px 12px; text-align: right; color: #615e57;">Diskon</td>
                    <td class="text-right price" style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: #2e7d32;">-Rp{{ number_format($order->discount_total, 0, ',', '.') }}</td>
                </tr>
            @endif
            
            @php
                $fee = (int)$order->grand_total - (int)$order->subtotal + (int)($order->discount_total ?? 0) - (int)$order->shipping_cost;
            @endphp
            @if($fee > 0)
                <tr>
                    <td colspan="2" class="text-right" style="padding: 10px 12px; text-align: right; color: #615e57;">Biaya Layanan</td>
                    <td class="text-right price" style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: #222523;">Rp{{ number_format($fee, 0, ',', '.') }}</td>
                </tr>
            @endif
            
            <tr style="background-color: #f2efe8; font-weight: bold;">
                <td colspan="2" class="text-right" style="padding: 12px; text-align: right; border-top: 2px solid #e5e1d8; color: #222523;">Total</td>
                <td class="text-right price" style="padding: 12px; text-align: right; font-family: 'JetBrains Mono', monospace; border-top: 2px solid #e5e1d8; color: #0b4e26;">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 25px; font-size: 13px; color: #615e57; line-height: 1.5; border-top: 1px solid #e5e1d8; padding-top: 15px;">
        <span class="bold" style="color: #222523;">Metode Pembayaran:</span><br>
        <span style="font-weight: 600; color: #222523;">{{ $order->payment_method }}</span>
        @if($order->payment_status === 'pending')
            <span style="display: inline-block; background-color: #fff3cd; color: #856404; padding: 2px 8px; font-size: 10px; font-family: monospace; font-weight: bold; text-transform: uppercase; margin-left: 10px; border-radius: 2px;">Menunggu Pembayaran</span>
        @elseif($order->payment_status === 'paid')
            <span style="display: inline-block; background-color: #d4edda; color: #155724; padding: 2px 8px; font-size: 10px; font-family: monospace; font-weight: bold; text-transform: uppercase; margin-left: 10px; border-radius: 2px;">✓ Lunas</span>
        @elseif($order->payment_status === 'refunded')
            <span style="display: inline-block; background-color: #e2e3e5; color: #383d41; padding: 2px 8px; font-size: 10px; font-family: monospace; font-weight: bold; text-transform: uppercase; margin-left: 10px; border-radius: 2px;">Refunded</span>
        @endif
    </div>

    @php $addr = $order->shipping_address; @endphp
    @if($addr && ($addr['name'] ?? null))
        <div style="margin-top: 20px; font-size: 13px; color: #615e57; line-height: 1.5; border-top: 1px solid #e5e1d8; padding-top: 15px;">
            <span class="bold" style="color: #222523;">Alamat Pengiriman:</span><br>
            <strong style="color: #222523;">{{ $addr['name'] }}</strong><br>
            {{ $addr['phone'] }}<br>
            {{ collect([$addr['address'] ?? '', $addr['district'] ?? '', $addr['city'] ?? '', $addr['province'] ?? '', $addr['postal_code'] ?? ''])->filter()->implode(', ') }}
        </div>
    @endif
@endsection
