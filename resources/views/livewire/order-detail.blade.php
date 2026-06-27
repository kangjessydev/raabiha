<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Detail Pesanan" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pb-20 md:pb-0">
            <div class="max-w-[800px] mx-auto px-4 sm:px-6 py-8 md:py-16">
                
                <!-- Back Button & Header -->
                <div class="hidden md:flex items-center gap-4 mb-8">
                    <a href="/account" class="w-10 h-10 border border-[#e5e2de] bg-white flex items-center justify-center hover:bg-[#f0ede9] transition-colors shrink-0">
                        <svg class="w-5 h-5 text-[#1c1c1a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div>
                        <h1 class="font-serif text-[28px] font-bold text-[#1c1c1a] leading-tight">Detail Pesanan</h1>
                        <p class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#615e57]">Invoice #{{ $order->order_number }}</p>
                    </div>
                </div>

                @php
                    $statusLabels = [
                        'pending' => 'Menunggu Pembayaran',
                        'paid' => 'Sudah Dibayar',
                        'packed' => 'Dikemas',
                        'sent' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ];
                    $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
                    
                    if ($order->status == 'completed') {
                        $badgeStyle = 'bg-[#064e3b]/10 text-[#064e3b] border border-[#064e3b]/20';
                    } elseif ($order->status == 'cancelled') {
                        $badgeStyle = 'bg-red-50 text-red-700 border border-red-200';
                    } elseif ($order->status == 'paid' || $order->status == 'sent') {
                        $badgeStyle = 'bg-blue-50 text-blue-700 border border-blue-200';
                    } else {
                        $badgeStyle = 'bg-[#fcf9f5] border border-[#1c1c1a] text-[#1c1c1a]';
                    }
                @endphp

                <!-- Invoice Card -->
                <div class="bg-white border border-[#e5e2de] shadow-sm">
                    <!-- Invoice Header -->
                    <div class="p-6 md:p-8 border-b border-[#e5e2de] flex flex-col md:flex-row md:items-start justify-between gap-6">
                        <div>
                            <div class="font-serif text-2xl font-bold tracking-widest text-[#064e3b] uppercase mb-4">Raabiha</div>
                            <div class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#615e57] mb-1">Status Pesanan</div>
                            <div class="inline-block px-3 py-1 font-mono text-[9px] font-bold tracking-widest uppercase {{ $badgeStyle }} mb-4">
                                {{ $statusLabel }}
                            </div>
                            <div class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#615e57] mb-1">Tanggal Pembelian</div>
                            <div class="font-sans text-[13px] text-[#1c1c1a] font-medium">{{ $order->created_at->format('d F Y, H:i') }} WIB</div>
                        </div>
                        <div class="md:text-right">
                            <div class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#615e57] mb-1">Alamat Pengiriman</div>
                            @php
                                $addr = is_array($order->shipping_address) ? $order->shipping_address : [];
                                $isPickup = $addr['is_pickup'] ?? false;
                                $name = trim(($addr['first_name'] ?? '') . ' ' . ($addr['last_name'] ?? ''));
                                $phone = $addr['phone'] ?? '';
                                $addressLine = $addr['address'] ?? '';
                                
                                $districtStr = '';
                                if (isset($addr['district']) && str_contains($addr['district'], '::')) {
                                    $parts = explode('::', $addr['district']);
                                    $districtStr = $parts[1] ?? '';
                                } else {
                                    $districtStr = trim(($addr['city'] ?? '') . ' ' . ($addr['province'] ?? ''));
                                }
                            @endphp
                            
                            @if($isPickup)
                                <div class="font-sans text-[13px] text-[#1c1c1a] font-medium mb-1">Ambil di Toko (Pickup)</div>
                                <div class="font-sans text-[13px] text-[#615e57] mb-4">
                                    {{ $name }} - {{ $phone }}
                                </div>
                            @else
                                <div class="font-sans text-[13px] text-[#1c1c1a] font-medium mb-1">{{ $name ?: 'Tidak ada data' }}</div>
                                <div class="font-sans text-[13px] text-[#615e57] mb-4">
                                    @if($phone) {{ $phone }}<br> @endif
                                    @if($addressLine) {{ $addressLine }}<br> @endif
                                    @if($districtStr) {{ $districtStr }} @endif
                                </div>
                            @endif

                            @if($order->courier)
                                <div class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#615e57] mb-1">Kurir Pengiriman</div>
                                <div class="font-sans text-[13px] text-[#1c1c1a] font-medium uppercase mb-4">{{ $order->courier }}</div>
                            @endif

                            @if($order->awb_number)
                                <div class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#615e57] mb-1">Nomor Resi</div>
                                <div class="font-sans text-[13px] text-[#064e3b] font-medium font-mono bg-[#064e3b]/10 px-2 py-1 inline-block border border-[#064e3b]/20">
                                    {{ $order->awb_number }}
                                </div>
                                <div class="mt-3">
                                    <a href="{{ url('/order/' . $order->id . '/track') }}" class="font-mono text-[9px] font-bold tracking-widest uppercase text-white bg-[#064e3b] hover:bg-[#043326] px-3 py-1.5 transition-colors focus:outline-none flex items-center justify-center gap-1.5 inline-flex">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                        <span>Lacak Paket</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>




                    <!-- Items List -->
                    <div class="p-6 md:p-8 border-b border-[#e5e2de]">
                        <h3 class="font-mono text-[11px] font-bold tracking-[0.15em] uppercase text-[#1c1c1a] mb-6">Daftar Produk</h3>
                        <div class="flex flex-col gap-6">
                            @foreach($order->items as $item)
                                @php
                                    $imageId = is_array($item->product->images) ? ($item->product->images[0] ?? null) : null;
                                    $image = asset('assets/images/placeholder.webp');
                                    if ($imageId) {
                                        if (is_numeric($imageId)) {
                                            $media = \Awcodes\Curator\Models\Media::find($imageId);
                                            if ($media) $image = $media->url;
                                        } else {
                                            $image = Storage::url($imageId);
                                        }
                                    }
                                    $variantText = '';
                                @endphp
                                <div class="flex flex-row gap-4 items-center">
                                    <div class="w-16 h-16 md:w-20 md:h-20 bg-[#e5e2de] shrink-0 border border-[#e5e2de]">
                                        <img src="{{ $image }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <a href="/product/{{ $item->product->slug }}" class="font-serif text-[15px] md:text-[16px] font-semibold text-[#1c1c1a] leading-tight mb-1 hover:text-[#064e3b] transition-colors line-clamp-1">{{ $item->product->name }}</a>
                                        <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-1">
                                            {{ $variantText }}
                                        </p>
                                        <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-3">Qty: {{ $item->quantity }} x Rp{{ number_format($item->price, 0, ',', '.') }}</p>
                                        
                                        @if($order->status == 'completed')
                                            @php
                                                $hasReviewed = \App\Models\ProductReview::where('product_id', $item->product_id)->where('user_id', auth()->id())->exists();
                                            @endphp
                                            @if(!$hasReviewed)
                                                <a href="/product/{{ $item->product->slug }}" class="inline-block border border-[#1c1c1a] px-3 py-1.5 text-[8px] font-mono font-bold uppercase tracking-[0.1em] hover:bg-[#1c1c1a] hover:text-white text-[#1c1c1a] transition-colors focus:outline-none">
                                                    Beri Ulasan
                                                </a>
                                            @else
                                                <span class="inline-block bg-[#064e3b]/10 text-[#064e3b] px-3 py-1.5 text-[8px] font-mono font-bold uppercase tracking-[0.1em] border border-[#064e3b]/20">
                                                    Sudah Diulas
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="font-sans text-[14px] md:text-[15px] font-semibold text-[#1c1c1a] text-right shrink-0">
                                        Rp{{ number_format($item->total, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="p-6 md:p-8 bg-[#fcf9f5]">
                        <h3 class="font-mono text-[11px] font-bold tracking-[0.15em] uppercase text-[#1c1c1a] mb-6">Rincian Pembayaran</h3>
                        
                        <div class="flex flex-col gap-3 font-sans text-[13px] text-[#615e57] mb-6">
                            <div class="flex justify-between items-center">
                                <span>Subtotal Produk</span>
                                <span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Ongkos Kirim</span>
                                <span>Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                            </div>
                            @if($order->discount > 0)
                            <div class="flex justify-between items-center text-[#ba1a1a]">
                                <span>Diskon ({{ $order->coupon_code }})</span>
                                <span>- Rp{{ number_format($order->discount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t border-[#e5e2de]">
                            <span class="font-mono text-[12px] font-bold tracking-[0.15em] uppercase text-[#1c1c1a]">Total Belanja</span>
                            <span class="font-serif text-[20px] font-bold text-[#064e3b]">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-8 flex flex-col sm:flex-row justify-between gap-4">
                    <button onclick="window.print()" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] bg-white px-8 py-3 hover:bg-[#f0ede9] transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak Invoice
                    </button>

                    @if($order->status == 'pending' && $order->payment_url)
                        <a href="{{ $order->payment_url }}" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-8 py-3 hover:bg-[#043326] transition-colors flex items-center justify-center">
                            Bayar Sekarang
                        </a>
                    @elseif(in_array($order->status, ['completed', 'cancelled']))
                        <a href="/shop" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#1c1c1a] px-8 py-3 hover:bg-black transition-colors flex items-center justify-center">
                            Belanja Lagi
                        </a>
                    @endif
                </div>

            </div>
        </main>
    </div>
</div>
