<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Akun Saya" backUrl="/" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pb-20 md:pb-0">
            <div class="max-w-[1440px] mx-auto px-6 md:px-[64px] py-12 md:py-24">
                
                <!-- Desktop Title -->
                <div class="mb-12 hidden md:flex items-center gap-6">
                    <div class="w-20 h-20 rounded-full overflow-hidden border border-[#e5e2de] bg-[#f0ede9] shrink-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Customer') }}&background=064e3b&color=fff&size=128" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight uppercase leading-none mb-2">Halo, {{ explode(' ', auth()->user()->name ?? 'Customer')[0] }}!</h1>
                        <p class="font-mono text-[10px] font-medium tracking-[0.2em] text-[#615e57] uppercase">
                            Status: {{ auth()->user()->hasRole('reseller') ? 'Mitra Reseller' : (auth()->user()->hasRole('super_admin') ? 'Super Admin' : 'Pelanggan Reguler') }} | Member sejak {{ auth()->user()->created_at ? auth()->user()->created_at->format('Y') : date('Y') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-[240px_1fr] lg:grid-cols-[280px_1fr] gap-10 lg:gap-16 items-start">
                    
                    <!-- Left Sidebar Menu -->
                    <aside class="flex flex-col gap-1 border-b border-[#e5e2de] pb-6 md:border-none md:pb-0">
                        <button wire:click="setTab('dasbor')" class="font-mono text-[11px] uppercase tracking-[0.15em] px-4 py-3 text-left transition-colors {{ $activeTab === 'dasbor' ? 'font-bold text-[#1c1c1a] bg-[#e5e2de]' : 'font-semibold text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a]' }}">Dasbor</button>
                        <button wire:click="setTab('pesanan')" class="font-mono text-[11px] uppercase tracking-[0.15em] px-4 py-3 text-left transition-colors {{ $activeTab === 'pesanan' ? 'font-bold text-[#1c1c1a] bg-[#e5e2de]' : 'font-semibold text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a]' }}">Pesanan Saya</button>
                        <button wire:click="setTab('alamat')" class="font-mono text-[11px] uppercase tracking-[0.15em] px-4 py-3 text-left transition-colors {{ $activeTab === 'alamat' ? 'font-bold text-[#1c1c1a] bg-[#e5e2de]' : 'font-semibold text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a]' }}">Alamat Tersimpan</button>
                        <button wire:click="setTab('voucher')" class="font-mono text-[11px] uppercase tracking-[0.15em] px-4 py-3 text-left transition-colors {{ $activeTab === 'voucher' ? 'font-bold text-[#1c1c1a] bg-[#e5e2de]' : 'font-semibold text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a]' }}">Voucher Saya</button>
                        <button wire:click="setTab('reseller')" class="font-mono text-[11px] uppercase tracking-[0.15em] px-4 py-3 text-left transition-colors flex justify-between items-center {{ $activeTab === 'reseller' ? 'font-bold text-[#1c1c1a] bg-[#e5e2de]' : 'font-semibold text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a]' }}">
                            Portal Reseller
                            @if(auth()->user()->reseller_status === 'active')
                                <span class="bg-[#064e3b] text-white text-[8px] px-1.5 py-0.5 rounded-sm ml-2">AKTIF</span>
                            @elseif(auth()->user()->reseller_status === 'pending')
                                <span class="bg-[#ca8a04] text-white text-[8px] px-1.5 py-0.5 rounded-sm ml-2">PENDING</span>
                            @endif
                        </button>
                        <button wire:click="setTab('akun')" class="font-mono text-[11px] uppercase tracking-[0.15em] px-4 py-3 text-left transition-colors {{ $activeTab === 'akun' ? 'font-bold text-[#1c1c1a] bg-[#e5e2de]' : 'font-semibold text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a]' }}">Pengaturan Akun</button>
                        <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                            @csrf
                            <button type="submit" class="w-full font-mono text-[11px] font-semibold tracking-[0.15em] uppercase text-[#ba1a1a] hover:bg-[#ba1a1a]/10 mt-4 px-4 py-3 transition-colors text-left">Keluar</button>
                        </form>
                    </aside>

                    <!-- Right Content Area -->
                    <div class="flex flex-col gap-8">
                        @if($activeTab === 'dasbor')
                            <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block">Dasbor</h2>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Total Orders -->
                                <div class="bg-[#fcf9f5] border border-[#e5e2de] p-6 md:p-8 flex flex-col items-start gap-4">
                                    <div class="p-3 bg-[#e5e2de] rounded-sm text-[#1c1c1a]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-1">Total Pesanan</p>
                                        <p class="font-serif text-[32px] font-bold text-[#1c1c1a] leading-none">{{ $this->dashboard_stats['total_orders'] }}</p>
                                    </div>
                                    <div class="font-sans text-[12px] text-[#615e57] flex gap-4 mt-2">
                                        <span><strong class="text-[#1c1c1a]">{{ $this->dashboard_stats['pending_orders'] }}</strong> Pending</span>
                                        <span><strong class="text-[#064e3b]">{{ $this->dashboard_stats['completed_orders'] }}</strong> Selesai</span>
                                    </div>
                                </div>
                                
                                <!-- Total Spent -->
                                <div class="bg-[#064e3b] text-white p-6 md:p-8 flex flex-col items-start gap-4">
                                    <div class="p-3 bg-[#043326] rounded-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#c4c7c7] mb-1">Total Pembelian</p>
                                        <p class="font-serif text-[32px] font-bold text-white leading-none">Rp{{ number_format($this->dashboard_stats['total_spent'], 0, ',', '.') }}</p>
                                    </div>
                                    <p class="font-sans text-[12px] text-[#c4c7c7] mt-2">Total belanja untuk pesanan sukses.</p>
                                </div>
                            </div>
                            
                            <!-- Simple Spending Chart -->
                            <div class="bg-white border border-[#e5e2de] p-6 md:p-8 mt-6">
                                <h3 class="font-serif text-[18px] font-semibold text-[#1c1c1a] mb-6">Aktivitas Belanja (6 Bulan Terakhir)</h3>
                                <div class="flex items-end justify-between gap-2 h-40 pt-4 border-b border-[#e5e2de]">
                                    @php
                                        // Mock data for chart, could be dynamic in the future
                                        $months = [
                                            ['label' => now()->subMonths(5)->format('M'), 'height' => '20%'],
                                            ['label' => now()->subMonths(4)->format('M'), 'height' => '45%'],
                                            ['label' => now()->subMonths(3)->format('M'), 'height' => '30%'],
                                            ['label' => now()->subMonths(2)->format('M'), 'height' => '60%'],
                                            ['label' => now()->subMonths(1)->format('M'), 'height' => '15%'],
                                            ['label' => now()->format('M'), 'height' => '85%'],
                                        ];
                                    @endphp
                                    @foreach($months as $month)
                                        <div class="w-full flex flex-col items-center gap-2 relative group">
                                            <div class="w-full max-w-[40px] bg-[#e5e2de] hover:bg-[#064e3b] transition-colors relative" style="height: {{ $month['height'] }};">
                                                <!-- Tooltip on hover -->
                                                <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-[#1c1c1a] text-white font-mono text-[9px] py-1 px-2 rounded-sm whitespace-nowrap pointer-events-none transition-opacity">
                                                    Info
                                                </div>
                                            </div>
                                            <span class="font-mono text-[10px] text-[#615e57] uppercase">{{ $month['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Banner -->
                            <div class="mt-4 bg-[#f0ede9] p-6 border-l-4 border-[#1c1c1a] flex flex-col md:flex-row gap-6 justify-between items-center">
                                <div>
                                    <h3 class="font-serif text-[18px] font-semibold text-[#1c1c1a] mb-2">Ada pertanyaan tentang pesanan Anda?</h3>
                                    <p class="font-sans text-[13px] text-[#615e57]">Tim layanan pelanggan kami siap membantu Anda 24/7.</p>
                                </div>
                                <a href="https://wa.me/6281234567890" target="_blank" class="shrink-0 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-3 hover:bg-[#1c1c1a] hover:text-white transition-colors text-center w-full md:w-auto">Hubungi CS</a>
                            </div>

                        @elseif($activeTab === 'pesanan')
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                                <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block">Pesanan Saya</h2>
                                <div class="w-full sm:w-64 relative">
                                    <input type="text" wire:model.live.debounce.300ms="searchPesanan" placeholder="Cari ID Pesanan / Produk..." class="w-full bg-[#fcf9f5] border border-[#e5e2de] pl-10 pr-4 py-2.5 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors placeholder-[#a3a3a3]">
                                    <svg class="w-4 h-4 text-[#a3a3a3] absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                            </div>

                            @if($this->orders->isEmpty())
                                <!-- Empty State -->
                                <div class="flex flex-col items-center justify-center py-16 text-center border border-[#e5e2de] bg-white">
                                    <svg class="w-12 h-12 text-[#c4c7c7] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <p class="font-sans text-[14px] text-[#615e57] mb-4">
                                        @if(!empty($searchPesanan))
                                            Tidak ditemukan pesanan dengan kata kunci "{{ $searchPesanan }}".
                                        @else
                                            Anda belum memiliki riwayat pesanan.
                                        @endif
                                    </p>
                                    @if(empty($searchPesanan))
                                        <a href="/shop" wire:navigate class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-3 hover:bg-[#1c1c1a] hover:text-white transition-colors">Mulai Belanja</a>
                                    @else
                                        <button wire:click="$set('searchPesanan', '')" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-3 hover:bg-[#1c1c1a] hover:text-white transition-colors">Reset Pencarian</button>
                                    @endif
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
                                                @if($order->payment_url)
                                                    <a href="{{ $order->payment_url }}" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-6 py-2.5 hover:bg-[#043326] transition-colors w-full sm:w-auto text-center inline-block">Bayar Sekarang</a>
                                                @else
                                                    <a href="{{ url('/order-detail?id=' . $order->id) }}" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#1c1c1a] px-6 py-2.5 hover:bg-black transition-colors w-full sm:w-auto text-center inline-block">Cara Pembayaran</a>
                                                @endif
                                            @else
                                                <a href="{{ url('/order-detail?id=' . $order->id) }}" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#615e57] border border-[#c4c7c7] px-6 py-2.5 hover:border-[#1c1c1a] hover:text-[#1c1c1a] transition-colors w-full sm:w-auto text-center inline-block">Lihat Invoice</a>
                                                <a href="{{ url('/product/' . $order->items->first()->product->slug) }}" wire:navigate class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-2.5 hover:bg-[#1c1c1a] hover:text-white transition-colors w-full sm:w-auto text-center inline-block">Beli Lagi</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                        @elseif($activeTab === 'alamat')
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                                <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block">Alamat Tersimpan</h2>
                                @if(!$showAddressForm)
                                    <button wire:click="toggleAddressForm" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-6 py-3 hover:bg-[#043326] transition-colors w-full sm:w-auto text-center">Tambah Alamat Baru</button>
                                @endif
                            </div>

                            @if (session()->has('address_success'))
                                <div class="bg-[#064e3b]/10 border border-[#064e3b]/20 text-[#064e3b] px-4 py-3 mb-6 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="text-[13px]">{{ session('address_success') }}</span>
                                </div>
                            @endif

                            @if($showAddressForm)
                                <!-- Address Form -->
                                <div class="bg-white border border-[#e5e2de] p-6 md:p-8 mb-8">
                                    <h3 class="font-mono text-[12px] font-bold tracking-widest uppercase text-[#1c1c1a] mb-6">{{ $editingAddressId ? 'Ubah Alamat' : 'Tambah Alamat Baru' }}</h3>
                                    <form wire:submit="saveAddress" class="flex flex-col gap-5">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Label Alamat</label>
                                                <input type="text" wire:model="addressForm.title" placeholder="Contoh: Rumah, Kantor" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                            </div>
                                            <div>
                                                <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Nama Penerima *</label>
                                                <input type="text" wire:model="addressForm.recipient_name" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                                @error('addressForm.recipient_name') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Nomor Telepon *</label>
                                                <input type="tel" wire:model="addressForm.phone" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                                @error('addressForm.phone') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Provinsi *</label>
                                                <input type="text" wire:model="addressForm.province" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                                @error('addressForm.province') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                            <div>
                                                <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Kota/Kabupaten *</label>
                                                <input type="text" wire:model="addressForm.city" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                                @error('addressForm.city') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Kecamatan</label>
                                                <input type="text" wire:model="addressForm.district" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                            </div>
                                            <div>
                                                <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Kode Pos</label>
                                                <input type="text" wire:model="addressForm.postal_code" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Alamat Lengkap *</label>
                                            <textarea wire:model="addressForm.full_address" rows="3" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors placeholder-[#a3a3a3]" placeholder="Nama jalan, gedung, no. rumah, dll."></textarea>
                                            @error('addressForm.full_address') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" wire:model="addressForm.is_primary" id="is_primary" class="w-4 h-4 text-[#064e3b] border-[#e5e2de] rounded focus:ring-[#064e3b]">
                                            <label for="is_primary" class="font-sans text-[13px] text-[#1c1c1a] cursor-pointer">Jadikan sebagai alamat utama</label>
                                        </div>
                                        
                                        <div class="flex gap-4 mt-4">
                                            <button type="button" wire:click="toggleAddressForm" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-8 py-3 hover:bg-[#f0ede9] transition-colors">Batal</button>
                                            <button type="submit" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-8 py-3 hover:bg-[#043326] transition-colors">
                                                <span wire:loading.remove wire:target="saveAddress">Simpan</span>
                                                <span wire:loading wire:target="saveAddress">Menyimpan...</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            @if($this->addresses->isEmpty() && !$showAddressForm)
                                <!-- Empty Address State -->
                                <div class="flex flex-col items-center justify-center py-16 text-center border border-[#e5e2de] bg-white">
                                    <svg class="w-12 h-12 text-[#c4c7c7] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <p class="font-sans text-[14px] text-[#615e57] mb-0">Anda belum menyimpan alamat pengiriman.</p>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @foreach($this->addresses as $address)
                                        <div class="border {{ $address->is_primary ? 'border-[#064e3b] bg-[#fcf9f5]' : 'border-[#e5e2de] bg-white' }} p-6 relative flex flex-col h-full">
                                            @if($address->is_primary)
                                                <div class="absolute top-0 right-0 bg-[#064e3b] text-white font-mono text-[8px] font-bold tracking-widest uppercase px-3 py-1">Utama</div>
                                            @endif
                                            
                                            <div class="mb-4">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h3 class="font-serif text-[18px] font-semibold text-[#1c1c1a]">{{ $address->recipient_name }}</h3>
                                                    @if($address->title)
                                                        <span class="bg-[#e5e2de] text-[#615e57] text-[10px] font-mono uppercase px-2 py-0.5 rounded-sm">{{ $address->title }}</span>
                                                    @endif
                                                </div>
                                                <p class="font-sans text-[13px] text-[#615e57]">{{ $address->phone }}</p>
                                            </div>
                                            
                                            <p class="font-sans text-[13px] text-[#1c1c1a] leading-relaxed mb-6 flex-1">
                                                {{ $address->full_address }}<br>
                                                {{ $address->district ? $address->district . ', ' : '' }}{{ $address->city }}<br>
                                                {{ $address->province }} {{ $address->postal_code }}
                                            </p>
                                            
                                            <div class="flex items-center gap-4 pt-4 border-t border-[#e5e2de] mt-auto">
                                                <button wire:click="editAddress({{ $address->id }})" class="font-mono text-[10px] font-bold tracking-[0.1em] uppercase text-[#064e3b] hover:text-[#043326] transition-colors">Ubah</button>
                                                <button wire:click="deleteAddress({{ $address->id }})" wire:confirm="Apakah Anda yakin ingin menghapus alamat ini?" class="font-mono text-[10px] font-bold tracking-[0.1em] uppercase text-[#ba1a1a] hover:text-red-900 transition-colors">Hapus</button>
                                                
                                                @if(!$address->is_primary)
                                                    <div class="ml-auto">
                                                        <button wire:click="setPrimaryAddress({{ $address->id }})" class="font-mono text-[10px] font-bold tracking-[0.1em] uppercase text-[#615e57] border border-[#e5e2de] px-3 py-1.5 hover:border-[#1c1c1a] hover:text-[#1c1c1a] transition-colors">Jadikan Utama</button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        @elseif($activeTab === 'voucher')
                            <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block mb-4">Voucher Saya</h2>

                            @if($this->vouchers->isEmpty())
                                <!-- Empty State -->
                                <div class="flex flex-col items-center justify-center py-16 text-center border border-[#e5e2de] bg-white">
                                    <svg class="w-12 h-12 text-[#c4c7c7] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    <p class="font-sans text-[14px] text-[#615e57]">Belum ada voucher yang tersedia untuk Anda saat ini.</p>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @foreach($this->vouchers as $voucher)
                                        <div class="border border-[#064e3b] bg-white flex flex-row overflow-hidden relative">
                                            <!-- Voucher Graphic Left -->
                                            <div class="bg-[#064e3b] w-24 flex flex-col justify-center items-center text-white shrink-0 px-2 relative border-r-2 border-dashed border-white">
                                                <span class="font-serif text-2xl font-bold uppercase rotate-180" style="writing-mode: vertical-rl;">PROMO</span>
                                                <!-- Cutouts for ticket effect -->
                                                <div class="absolute -top-3 -right-3 w-6 h-6 rounded-full bg-white"></div>
                                                <div class="absolute -bottom-3 -right-3 w-6 h-6 rounded-full bg-white"></div>
                                            </div>
                                            
                                            <!-- Voucher Content -->
                                            <div class="p-6 flex-1 bg-[#fcf9f5]">
                                                <div class="font-mono text-[12px] font-bold tracking-widest uppercase text-[#064e3b] mb-1">{{ $voucher->code }}</div>
                                                <div class="font-sans text-[16px] font-bold text-[#1c1c1a] mb-2">
                                                    Diskon {{ $voucher->discount_type === 'percentage' ? rtrim(rtrim(number_format($voucher->discount_value, 2, ',', '.'), '0'), ',') . '%' : 'Rp' . number_format($voucher->discount_value, 0, ',', '.') }}
                                                </div>
                                                
                                                <div class="font-sans text-[12px] text-[#615e57] mb-4 space-y-1">
                                                    @if($voucher->min_spend > 0)
                                                        <p>Min. belanja Rp{{ number_format($voucher->min_spend, 0, ',', '.') }}</p>
                                                    @endif
                                                    @if($voucher->max_discount > 0)
                                                        <p>Maks. diskon Rp{{ number_format($voucher->max_discount, 0, ',', '.') }}</p>
                                                    @endif
                                                    @if($voucher->valid_until)
                                                        <p class="text-[#ba1a1a]">Berlaku hingga {{ $voucher->valid_until->format('d M Y') }}</p>
                                                    @else
                                                        <p>Tanpa batas waktu</p>
                                                    @endif
                                                </div>
                                                
                                                <button onclick="navigator.clipboard.writeText('{{ $voucher->code }}'); alert('Kode voucher disalin!');" class="font-mono text-[10px] font-bold tracking-[0.1em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-4 py-2 hover:bg-[#1c1c1a] hover:text-white transition-colors">Salin Kode</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        @elseif($activeTab === 'akun')
                            <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block">Pengaturan Akun</h2>
                            
                            <!-- Profile Information Form -->
                            <div class="bg-white border border-[#e5e2de] p-6 md:p-8">
                                <h3 class="font-mono text-[12px] font-bold tracking-widest uppercase text-[#1c1c1a] mb-6">Informasi Pribadi</h3>
                                
                                @if (session()->has('profile_success'))
                                    <div class="bg-[#064e3b]/10 border border-[#064e3b]/20 text-[#064e3b] px-4 py-3 mb-6 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="text-[13px]">{{ session('profile_success') }}</span>
                                    </div>
                                @endif

                                <form wire:submit="updateProfile" class="flex flex-col gap-5">
                                    <div>
                                        <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Nama Lengkap</label>
                                        <input type="text" wire:model="name" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors placeholder-[#a3a3a3]">
                                        @error('name') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Email</label>
                                        <input type="email" wire:model="email" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors placeholder-[#a3a3a3]">
                                        @error('email') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="submit" class="self-start mt-2 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-8 py-3 hover:bg-[#043326] transition-colors">
                                        <span wire:loading.remove wire:target="updateProfile">Simpan Perubahan</span>
                                        <span wire:loading wire:target="updateProfile">Menyimpan...</span>
                                    </button>
                                </form>
                            </div>

                            <!-- Update Password Form -->
                            <div class="bg-white border border-[#e5e2de] p-6 md:p-8">
                                <h3 class="font-mono text-[12px] font-bold tracking-widest uppercase text-[#1c1c1a] mb-6">Ubah Kata Sandi</h3>
                                
                                @if (session()->has('password_success'))
                                    <div class="bg-[#064e3b]/10 border border-[#064e3b]/20 text-[#064e3b] px-4 py-3 mb-6 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="text-[13px]">{{ session('password_success') }}</span>
                                    </div>
                                @endif

                                <form wire:submit="updatePassword" class="flex flex-col gap-5">
                                    <div>
                                        <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Kata Sandi Saat Ini</label>
                                        <input type="password" wire:model="current_password" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                        @error('current_password') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Kata Sandi Baru</label>
                                        <input type="password" wire:model="new_password" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                        @error('new_password') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57] mb-2">Konfirmasi Kata Sandi Baru</label>
                                        <input type="password" wire:model="new_password_confirmation" class="w-full bg-[#fcf9f5] border border-[#e5e2de] px-4 py-3 text-[13px] font-sans text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] focus:ring-0 transition-colors">
                                    </div>
                                    <button type="submit" class="self-start mt-2 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-8 py-3 hover:bg-[#043326] transition-colors">
                                        <span wire:loading.remove wire:target="updatePassword">Perbarui Sandi</span>
                                        <span wire:loading wire:target="updatePassword">Menyimpan...</span>
                                    </button>
                                </form>
                            </div>
                        @elseif($activeTab === 'reseller')
                            <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block">Laporan Pembelian Reseller</h2>
                            
                            @if(auth()->user()->reseller_status === 'active')
                                <div class="bg-[#064e3b] text-white p-6 md:p-8 flex flex-col items-start gap-4 mb-6">
                                    <div class="p-3 bg-white/20 rounded-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#a3b8b0] mb-1">Diskon Aktif Anda</p>
                                        <p class="font-serif text-[32px] font-bold text-white leading-none">{{ $this->reseller_stats['discount_percent'] ?? 0 }}%</p>
                                    </div>
                                    <div class="font-sans text-[12px] text-[#a3b8b0] mt-2">
                                        Berlaku untuk semua pembelian produk tanpa batas.
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="bg-[#fcf9f5] border border-[#e5e2de] p-6 flex flex-col items-start gap-3">
                                        <p class="font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57]">Total Pembelian Reseller</p>
                                        <p class="font-sans text-[20px] font-bold text-[#1c1c1a]">Rp{{ number_format($this->reseller_stats['total_spent'] ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="bg-[#fcf9f5] border border-[#e5e2de] p-6 flex flex-col items-start gap-3">
                                        <p class="font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-[#615e57]">Total Hemat / Keuntungan</p>
                                        <p class="font-sans text-[20px] font-bold text-[#064e3b]">Rp{{ number_format($this->reseller_stats['total_savings'] ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @elseif(auth()->user()->reseller_status === 'pending')
                                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-6 flex items-start gap-4">
                                    <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div>
                                        <h3 class="font-sans font-bold text-[16px] mb-1">Status Pengajuan Pending</h3>
                                        <p class="font-sans text-[13px]">Pengajuan akun reseller Anda sedang kami tinjau. Kami akan menghubungi Anda segera melalui WhatsApp atau Email untuk proses verifikasi. Harap tunggu!</p>
                                    </div>
                                </div>
                                @php
                                    $settings = \App\Models\SiteSetting::whereIn('key', ['reseller_min_deposit', 'reseller_banks', 'reseller_whatsapp_payment'])->pluck('value', 'key');
                                    $depositFee = $settings['reseller_min_deposit'] ?? 0;
                                    $banksData = $settings['reseller_banks'] ?? '[]';
                                    $banks = json_decode($banksData, true) ?? [];
                                    $whatsapp = $settings['reseller_whatsapp_payment'] ?? '';
                                @endphp
                                @if($depositFee > 0)
                                <div class="mt-4 bg-[#fcf9f5] border border-[#e5e2de] p-6 flex flex-col items-start gap-4">
                                    <p class="font-mono text-[10px] uppercase tracking-widest text-[#615e57]">Tindakan Diperlukan</p>
                                    <div class="w-full">
                                        <p class="font-sans text-[16px] font-bold text-[#1c1c1a]">Pembayaran Deposit Awal: Rp{{ number_format($depositFee, 0, ',', '.') }}</p>
                                        <p class="font-sans text-[13px] text-[#615e57] mt-1 mb-4">Sistem mendeteksi bahwa Anda belum melakukan pembayaran. Silakan transfer ke rekening admin dan konfirmasi via WhatsApp untuk mengaktifkan diskon Anda.</p>
                                        
                                        @if(count($banks) > 0)
                                        <div class="bg-white border border-[#e5e2de] p-4 mb-4">
                                            <p class="font-mono text-[9px] uppercase tracking-widest text-[#1c1c1a] font-bold mb-3 border-b border-[#e5e2de] pb-2">Tujuan Transfer</p>
                                            <div class="flex flex-col gap-3">
                                                @foreach($banks as $bank)
                                                <div class="flex flex-col gap-1">
                                                    <span class="font-sans text-[13px] font-bold text-[#1c1c1a] uppercase">{{ $bank['bank_name'] ?? '' }}</span>
                                                    <span class="font-mono text-[14px] text-[#064e3b] font-bold tracking-wider">{{ $bank['account_number'] ?? '' }}</span>
                                                    <span class="font-sans text-[11px] text-[#615e57] uppercase">A.N {{ $bank['account_name'] ?? '' }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif

                                        @if($whatsapp)
                                        @php
                                            $waMsg = "Halo Admin Raabiha, saya ingin mengkonfirmasi pembayaran pendaftaran akun Reseller dengan rincian sebagai berikut:%0A%0ANama: " . auth()->user()->name . "%0AEmail: " . auth()->user()->email . "%0ATanggal: " . now()->format('d M Y');
                                        @endphp
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}?text={{ $waMsg }}" target="_blank" class="w-full bg-[#25D366] text-white px-6 py-3 font-mono text-[10px] font-bold tracking-[0.2em] uppercase hover:bg-[#128C7E] transition-colors inline-flex justify-center items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                                            Kirim Bukti Pembayaran ke WhatsApp
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @elseif(auth()->user()->reseller_status === 'rejected')
                                <div class="bg-red-50 border border-red-200 text-red-800 p-6 flex items-start gap-4">
                                    <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div>
                                        <h3 class="font-sans font-bold text-[16px] mb-1">Status Pengajuan Ditolak</h3>
                                        <p class="font-sans text-[13px]">Mohon maaf, pengajuan akun reseller Anda tidak dapat kami proses saat ini. Silakan hubungi admin kami untuk informasi lebih lanjut.</p>
                                    </div>
                                </div>
                            @else
                                <div class="bg-[#f0ede9] p-6 flex flex-col items-center text-center gap-4 py-12">
                                    <svg class="w-12 h-12 text-[#064e3b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <div>
                                        <h3 class="font-serif font-bold text-[20px] text-[#1c1c1a] mb-2">Tingkatkan Keuntungan Anda!</h3>
                                        <p class="font-sans text-[13px] text-[#615e57] max-w-md mx-auto">Gabung menjadi mitra reseller Raabiha dan dapatkan potongan harga eksklusif untuk setiap pembelian tanpa minimal order. Daftarkan diri Anda sekarang!</p>
                                    </div>
                                    <a href="/reseller-register" wire:navigate class="mt-2 inline-block bg-[#064e3b] text-white px-6 py-3 font-mono text-[10px] font-bold tracking-[0.2em] uppercase hover:bg-black transition-colors">
                                        Daftar Menjadi Reseller
                                    </a>
                                </div>
                            @endif
                        @endif
                </div>
            </div>
        </main>
    </div>
</div>
