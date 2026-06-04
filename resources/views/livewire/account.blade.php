<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Akun Saya" backUrl="/" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pb-20 md:pb-0">
            <div class="max-w-[1440px] mx-auto px-6 md:px-[64px] py-12 md:py-24">
                
                <!-- Desktop Title -->
                <div class="mb-12 hidden md:block">
                    <h1 class="font-serif text-[32px] md:text-[48px] font-bold text-[#1c1c1a] tracking-tight uppercase">Akun Saya</h1>
                    <p class="font-mono text-[10px] font-medium tracking-[0.2em] text-[#615e57] uppercase mt-2">Selamat Datang, {{ auth()->user()->name ?? 'Customer' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-[240px_1fr] lg:grid-cols-[280px_1fr] gap-10 lg:gap-16 items-start">
                    
                    <!-- Left Sidebar Menu -->
                    <aside class="flex flex-col gap-1 border-b border-[#e5e2de] pb-6 md:border-none md:pb-0">
                        <a href="#" class="font-mono text-[11px] font-bold tracking-[0.15em] uppercase text-[#1c1c1a] bg-[#e5e2de] px-4 py-3 transition-colors">Pesanan Saya</a>
                        <a href="#" class="font-mono text-[11px] font-semibold tracking-[0.15em] uppercase text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a] px-4 py-3 transition-colors">Alamat Tersimpan</a>
                        <a href="#" class="font-mono text-[11px] font-semibold tracking-[0.15em] uppercase text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a] px-4 py-3 transition-colors">Detail Akun</a>
                        <button wire:click="logout" class="font-mono text-[11px] font-semibold tracking-[0.15em] uppercase text-[#ba1a1a] hover:bg-[#ba1a1a]/10 mt-4 px-4 py-3 transition-colors text-left">Keluar</button>
                    </aside>

                    <!-- Right Content Area: Order History -->
                    <div class="flex flex-col gap-8">
                        <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block">Pesanan Saya</h2>

                        @if($this->orders->isEmpty())
                            <!-- Empty State -->
                            <div class="flex flex-col items-center justify-center py-16 text-center border border-[#e5e2de]">
                                <svg class="w-12 h-12 text-[#c4c7c7] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <p class="font-sans text-[14px] text-[#615e57] mb-4">Anda belum memiliki riwayat pesanan.</p>
                                <a href="/shop" wire:navigate class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-3 hover:bg-[#1c1c1a] hover:text-white transition-colors">Mulai Belanja</a>
                            </div>
                        @else
                            @foreach($this->orders as $order)
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
                                    
                                    // Primary styling for active orders, dim for completed/cancelled
                                    $isInactive = in_array($order->status, ['completed', 'cancelled']);
                                    $cardClass = $isInactive ? 'opacity-75 hover:opacity-100 transition-opacity' : '';
                                    $headerBg = $isInactive ? 'bg-white' : 'bg-[#f0ede9]';
                                    $textBase = $isInactive ? 'text-[#615e57]' : 'text-[#1c1c1a]';
                                    
                                    // Status badge styling
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

                                <!-- Order Card -->
                                <div class="border border-[#e5e2de] bg-transparent flex flex-col {{ $cardClass }}">
                                    <!-- Order Header -->
                                    <div class="border-b border-[#e5e2de] {{ $headerBg }} p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-6">
                                            <div>
                                                <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Tanggal Pesanan</p>
                                                <p class="font-sans text-[13px] font-medium {{ $textBase }}">{{ $order->created_at->format('d M Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Total</p>
                                                <p class="font-sans text-[13px] font-medium {{ $textBase }}">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</p>
                                            </div>
                                            <div>
                                                <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Nomor Pesanan</p>
                                                <p class="font-mono text-[11px] font-bold {{ $textBase }}">#{{ $order->order_number }}</p>
                                            </div>
                                        </div>
                                        <!-- Status Badge -->
                                        <div class="self-start sm:self-center px-3 py-1 font-mono text-[9px] font-bold tracking-widest uppercase {{ $badgeStyle }}">
                                            {{ $statusLabel }}
                                        </div>
                                    </div>
                                    
                                    <!-- Order Items -->
                                    @foreach($order->items as $item)
                                        @php
                                            $image = $item->product->images && count($item->product->images) > 0 ? Storage::url($item->product->images[0]) : asset('assets/images/placeholder.png');
                                            $variantText = '';
                                            if ($item->attributes) {
                                                $options = [];
                                                foreach ($item->attributes as $key => $val) {
                                                    $options[] = $val;
                                                }
                                                $variantText = implode(' / ', $options);
                                            }
                                        @endphp
                                        <div class="p-4 md:p-6 flex flex-col md:flex-row gap-4 md:gap-6 md:items-center {{ !$loop->last ? 'border-b border-[#e5e2de]' : '' }}">
                                            <div class="w-20 h-24 bg-[#e5e2de] shrink-0">
                                                <img src="{{ $image }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover {{ $isInactive ? 'grayscale opacity-80' : '' }}">
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="font-serif text-[16px] md:text-[18px] font-semibold text-[#1c1c1a] leading-tight mb-1">{{ $item->product->name }}</h3>
                                                <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">
                                                    {{ $variantText ? $variantText . ' | ' : '' }}Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                                </p>
                                                <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mt-1">Qty: {{ $item->quantity }}</p>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Order Footer Actions -->
                                    <div class="border-t border-[#e5e2de] p-4 flex flex-col sm:flex-row justify-end gap-3 {{ $isInactive ? 'bg-white' : 'bg-[#fcf9f5]' }}">
                                        @if($order->status == 'pending')
                                            <button class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-2.5 hover:bg-[#f0ede9] transition-colors w-full sm:w-auto text-center">Batal Pesanan</button>
                                            <button class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-6 py-2.5 hover:bg-[#043326] transition-colors w-full sm:w-auto text-center">Bayar Sekarang</button>
                                        @else
                                            <a href="{{ url('/order-detail?id=' . $order->id) }}" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#615e57] border border-[#c4c7c7] px-6 py-2.5 hover:border-[#1c1c1a] hover:text-[#1c1c1a] transition-colors w-full sm:w-auto text-center inline-block">Lihat Invoice</a>
                                            <button class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-2.5 hover:bg-[#1c1c1a] hover:text-white transition-colors w-full sm:w-auto text-center">Beli Lagi</button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.app>
