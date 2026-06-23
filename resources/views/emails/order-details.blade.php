@extends('emails.layout')

@section('content_details')
    <h3 style="font-family: 'Playfair Display', Georgia, serif; font-size: 16px; border-bottom: 1px solid #e5e1d8; padding-bottom: 8px; margin-top: 30px;">Rincian Pesanan #{{ $order->order_number }}</h3>
    <table class="details-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-right" style="width: 60px;">Qty</th>
                <th class="text-right" style="width: 100px;">Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <span class="bold">{{ $item->product->name }}</span>
                        @if($item->selected_attributes)
                            <br><span style="font-size: 11px; color: #615e57;">Varian: {{ is_array($item->selected_attributes) ? implode(', ', $item->selected_attributes) : (is_string($item->selected_attributes) ? $item->selected_attributes : '') }}</span>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right price">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="text-right bold">Subtotal</td>
                <td class="text-right price bold">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($order->discount_amount > 0)
                <tr>
                    <td colspan="2" class="text-right">Potongan Diskon</td>
                    <td class="text-right price" style="color: #ba1a1a;">-Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($order->shipping_cost > 0)
                <tr>
                    <td colspan="2" class="text-right">Ongkos Kirim ({{ strtoupper($order->shipping_courier ?? '') }})</td>
                    <td class="text-right price">Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($order->unique_code > 0)
                <tr>
                    <td colspan="2" class="text-right">Kode Unik</td>
                    <td class="text-right price">Rp{{ number_format($order->unique_code, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr style="background-color: #f2efe8; font-weight: bold;">
                <td colspan="2" class="text-right" style="padding: 12px;">Total Pembayaran</td>
                <td class="text-right price" style="padding: 12px; color: #0b4e26;">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 25px; font-size: 13px; color: #615e57; line-height: 1.5;">
        <span class="bold" style="color: #222523;">Alamat Pengiriman:</span><br>
        @if(!empty($order->shipping_address['name'])) {{ $order->shipping_address['name'] }}<br> @endif
        @if(!empty($order->shipping_address['phone'])) {{ $order->shipping_address['phone'] }}<br> @endif
        @php
            $addressParts = array_filter([
                $order->shipping_address['address'] ?? null,
                $order->shipping_address['city'] ?? null,
                $order->shipping_address['province'] ?? null
            ]);
            $addressLine = implode(', ', $addressParts);
            if (!empty($order->shipping_address['postal_code'])) {
                $addressLine .= ' ' . $order->shipping_address['postal_code'];
            }
        @endphp
        {{ $addressLine }}
    </div>
@endsection
