<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Portal Reseller" />
    </x-slot:header>

    <div class="page-slide-in">
        <style wire:ignore>
        .reseller-dashboard-layout {
            display: flex !important;
            flex-direction: column !important;
            gap: 24px !important;
            align-items: stretch !important;
            width: 100% !important;
        }
        .reseller-dashboard-sidebar {
            display: flex !important;
            flex-direction: column !important;
            gap: 4px !important;
            border-bottom: 1px solid #e5e2de !important;
            padding-bottom: 24px !important;
            width: 100% !important;
            flex-shrink: 0 !important;
        }
        .reseller-dashboard-content {
            flex: 1 !important;
            min-width: 0 !important;
            width: 100% !important;
        }
        @media (min-width: 768px) {
            .reseller-dashboard-layout {
                flex-direction: row !important;
                gap: 40px !important;
                align-items: start !important;
            }
            .reseller-dashboard-sidebar {
                width: 240px !important;
                border-bottom: none !important;
                padding-bottom: 0 !important;
            }
        }
        @media (min-width: 1024px) {
            .reseller-dashboard-layout {
                gap: 64px !important;
            }
            .reseller-dashboard-sidebar {
                width: 280px !important;
            }
        }
        </style>

        <main class="site-main bg-[#fcf9f5] min-h-screen pb-20 md:pb-0">
            <div class="max-w-[1440px] mx-auto px-6 md:px-[64px] py-12 md:py-24">
                
                <!-- Desktop Title -->
                <div class="mb-12 hidden md:block">
                    <h1 class="font-serif text-[32px] md:text-[48px] font-bold text-[#1c1c1a] tracking-tight uppercase">Portal Reseller</h1>
                    <p class="font-mono text-[10px] font-medium tracking-[0.2em] text-[#615e57] uppercase mt-2">Selamat Datang, {{ auth()->user()->name }} | Status: {{ ucfirst(auth()->user()->reseller_status) }}</p>
                </div>

                <div class="reseller-dashboard-layout">
                    
                    <!-- Left Sidebar Menu -->
                    <aside class="reseller-dashboard-sidebar">
                        <button wire:click="$set('activeTab', 'overview')" class="font-mono text-[11px] font-bold tracking-[0.15em] uppercase px-4 py-3 transition-colors text-left {{ $activeTab == 'overview' ? 'text-[#1c1c1a] bg-[#e5e2de]' : 'text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a]' }}">Overview</button>
                        <button wire:click="$set('activeTab', 'pesanan')" class="font-mono text-[11px] font-bold tracking-[0.15em] uppercase px-4 py-3 transition-colors text-left {{ $activeTab == 'pesanan' ? 'text-[#1c1c1a] bg-[#e5e2de]' : 'text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a]' }}">Histori Pesanan</button>
                        <button wire:click="logout" class="font-mono text-[11px] font-semibold tracking-[0.15em] uppercase text-[#ba1a1a] hover:bg-[#ba1a1a]/10 mt-4 px-4 py-3 transition-colors text-left">Keluar</button>
                    </aside>
 
                    <!-- Right Content Area -->
                    <div class="reseller-dashboard-content">
                        <!-- Skeleton Loading -->
                        <div wire:loading wire:target="activeTab" class="w-full">
                            <div class="animate-pulse flex flex-col gap-8">
                                <div class="h-8 bg-[#e5e2de] w-1/3 hidden md:block"></div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="h-32 bg-[#e5e2de] w-full"></div>
                                    <div class="h-32 bg-[#e5e2de] w-full"></div>
                                </div>
                                <div class="h-24 bg-[#e5e2de] w-full mt-4"></div>
                            </div>
                        </div>

                        <!-- Actual Content -->
                        <div wire:loading.remove wire:target="activeTab" class="flex flex-col gap-8 w-full">
                            @if (auth()->user()->reseller_status === 'pending')
                                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-6 flex items-start gap-4">
                                    <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div>
                                        <h3 class="font-serif text-[18px] font-semibold mb-1">Status Reseller Menunggu Persetujuan</h3>
                                        <p class="font-sans text-[14px]">Akun Anda saat ini sedang ditinjau oleh tim kami. Anda akan mendapatkan diskon khusus reseller setelah status akun Anda disetujui.</p>
                                    </div>
                                </div>
                            @endif

                        @if($activeTab == 'overview')
                            <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block">Overview Kinerja</h2>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="border border-[#e5e2de] bg-white p-6">
                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-2">Total Pembelian (Reseller)</p>
                                    <p class="font-serif text-[28px] font-semibold text-[#1c1c1a]">Rp{{ number_format($this->orders->sum('grand_total'), 0, ',', '.') }}</p>
                                </div>
                                <div class="border border-[#e5e2de] bg-white p-6">
                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-2">Total Pesanan Selesai</p>
                                    <p class="font-serif text-[28px] font-semibold text-[#1c1c1a]">{{ $this->orders->where('status', 'completed')->count() }}</p>
                                </div>
                            </div>
                            
                            <div class="bg-[#f0ede9] p-6 border border-[#e5e2de] mt-4">
                                <h3 class="font-serif text-[16px] font-semibold text-[#1c1c1a] mb-2">Informasi Diskon</h3>
                                <p class="font-sans text-[14px] text-[#615e57]">Sebagai reseller yang berstatus <strong>{{ auth()->user()->reseller_status }}</strong>, Anda mendapatkan potongan harga otomatis untuk setiap pembelian produk yang mendukung diskon reseller.</p>
                            </div>
                        @endif

                        @if($activeTab == 'pesanan')
                            <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block">Histori Pesanan Reseller</h2>

                            @if($this->orders->isEmpty())
                                <!-- Empty State -->
                                <div class="flex flex-col items-center justify-center py-16 text-center border border-[#e5e2de]">
                                    <svg class="w-12 h-12 text-[#c4c7c7] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <p class="font-sans text-[14px] text-[#615e57] mb-4">Anda belum memiliki riwayat pesanan.</p>
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
                                    @endphp

                                    <div class="border border-[#e5e2de] bg-transparent flex flex-col mb-4">
                                        <div class="border-b border-[#e5e2de] bg-[#f0ede9] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-6">
                                                <div>
                                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Tanggal</p>
                                                    <p class="font-sans text-[13px] font-medium text-[#1c1c1a]">{{ $order->created_at->format('d M Y') }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Total Belanja</p>
                                                    <p class="font-sans text-[13px] font-medium text-[#1c1c1a]">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">No Pesanan</p>
                                                    <p class="font-mono text-[11px] font-bold text-[#1c1c1a]">#{{ $order->order_number }}</p>
                                                </div>
                                            </div>
                                            <div class="self-start sm:self-center px-3 py-1 font-mono text-[9px] font-bold tracking-widest uppercase bg-[#fcf9f5] border border-[#1c1c1a] text-[#1c1c1a]">
                                                {{ $statusLabel }}
                                            </div>
                                        </div>
                                        <div class="p-4 bg-white flex justify-between items-center">
                                            <span class="font-sans text-sm text-[#615e57]">{{ $order->items->count() }} macam produk dibeli</span>
                                            <a href="{{ url('/order-detail?id=' . $order->id) }}" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#064e3b] hover:underline">Detail</a>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endif

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.app>
