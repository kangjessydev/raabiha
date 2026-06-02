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
                    <p class="font-mono text-[10px] font-medium tracking-[0.2em] text-[#615e57] uppercase mt-2">Selamat Datang, Customer</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-[240px_1fr] lg:grid-cols-[280px_1fr] gap-10 lg:gap-16 items-start">
                    
                    <!-- Left Sidebar Menu -->
                    <aside class="flex flex-col gap-1 border-b border-[#e5e2de] pb-6 md:border-none md:pb-0">
                        <a href="#" class="font-mono text-[11px] font-bold tracking-[0.15em] uppercase text-[#1c1c1a] bg-[#e5e2de] px-4 py-3 transition-colors">Pesanan Saya</a>
                        <a href="#" class="font-mono text-[11px] font-semibold tracking-[0.15em] uppercase text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a] px-4 py-3 transition-colors">Alamat Tersimpan</a>
                        <a href="#" class="font-mono text-[11px] font-semibold tracking-[0.15em] uppercase text-[#615e57] hover:bg-[#f0ede9] hover:text-[#1c1c1a] px-4 py-3 transition-colors">Detail Akun</a>
                        <a href="{{ url('/login') }}" wire:navigate class="font-mono text-[11px] font-semibold tracking-[0.15em] uppercase text-[#ba1a1a] hover:bg-[#ba1a1a]/10 mt-4 px-4 py-3 transition-colors">Keluar</a>
                    </aside>

                    <!-- Right Content Area: Order History -->
                    <div class="flex flex-col gap-8">
                        <h2 class="font-serif text-[24px] font-semibold text-[#1c1c1a] hidden md:block">Pesanan Saya</h2>

                        <!-- Empty State (Hidden) -->
                        <div class="hidden flex-col items-center justify-center py-16 text-center border border-[#e5e2de]">
                            <svg class="w-12 h-12 text-[#c4c7c7] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <p class="font-sans text-[14px] text-[#615e57] mb-4">Anda belum memiliki riwayat pesanan.</p>
                            <a href="/shop" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-3 hover:bg-[#1c1c1a] hover:text-white transition-colors">Mulai Belanja</a>
                        </div>

                        <!-- Order Card 1 (Menunggu Pembayaran) -->
                        <div class="border border-[#e5e2de] bg-transparent flex flex-col">
                            <!-- Order Header -->
                            <div class="border-b border-[#e5e2de] bg-[#f0ede9] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex flex-col sm:flex-row gap-2 sm:gap-6">
                                    <div>
                                        <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Tanggal Pesanan</p>
                                        <p class="font-sans text-[13px] font-medium text-[#1c1c1a]">12 Okt 2024</p>
                                    </div>
                                    <div>
                                        <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Total</p>
                                        <p class="font-sans text-[13px] font-medium text-[#1c1c1a]">Rp850.000</p>
                                    </div>
                                    <div>
                                        <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Nomor Pesanan</p>
                                        <p class="font-mono text-[11px] font-bold text-[#1c1c1a]">#RBH-1092</p>
                                    </div>
                                </div>
                                <!-- Status Badge -->
                                <div class="self-start sm:self-center bg-[#fcf9f5] border border-[#1c1c1a] text-[#1c1c1a] px-3 py-1 font-mono text-[9px] font-bold tracking-widest uppercase">
                                    Menunggu Pembayaran
                                </div>
                            </div>
                            
                            <!-- Order Item -->
                            <div class="p-4 md:p-6 flex flex-col md:flex-row gap-4 md:gap-6 md:items-center">
                                <div class="w-20 h-24 bg-[#e5e2de] shrink-0">
                                    <img src="{{ asset('assets/images/gallery-3.png') }}" alt="Product" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-serif text-[16px] md:text-[18px] font-semibold text-[#1c1c1a] leading-tight mb-1">The Asymmetrical Tunic</h3>
                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Size M/L | Warna: Slate Sand</p>
                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mt-1">Qty: 1</p>
                                </div>
                            </div>

                            <!-- Order Footer Actions -->
                            <div class="border-t border-[#e5e2de] p-4 flex flex-col sm:flex-row justify-end gap-3 bg-[#fcf9f5]">
                                <button class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-2.5 hover:bg-[#f0ede9] transition-colors w-full sm:w-auto text-center">Batal Pesanan</button>
                                <button class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-6 py-2.5 hover:bg-[#043326] transition-colors w-full sm:w-auto text-center">Bayar Sekarang</button>
                            </div>
                        </div>

                        <!-- Order Card 2 (Selesai) -->
                        <div class="border border-[#e5e2de] bg-transparent flex flex-col opacity-75 hover:opacity-100 transition-opacity">
                            <!-- Order Header -->
                            <div class="border-b border-[#e5e2de] bg-white p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex flex-col sm:flex-row gap-2 sm:gap-6">
                                    <div>
                                        <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Tanggal Pesanan</p>
                                        <p class="font-sans text-[13px] font-medium text-[#615e57]">28 Sep 2024</p>
                                    </div>
                                    <div>
                                        <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Total</p>
                                        <p class="font-sans text-[13px] font-medium text-[#615e57]">Rp1.300.000</p>
                                    </div>
                                    <div>
                                        <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mb-0.5">Nomor Pesanan</p>
                                        <p class="font-mono text-[11px] font-bold text-[#615e57]">#RBH-0988</p>
                                    </div>
                                </div>
                                <!-- Status Badge -->
                                <div class="self-start sm:self-center bg-[#064e3b]/10 text-[#064e3b] border border-[#064e3b]/20 px-3 py-1 font-mono text-[9px] font-bold tracking-widest uppercase">
                                    Selesai
                                </div>
                            </div>
                            
                            <!-- Order Item -->
                            <div class="p-4 md:p-6 flex flex-col md:flex-row gap-4 md:gap-6 md:items-center">
                                <div class="w-20 h-24 bg-[#e5e2de] shrink-0">
                                    <img src="{{ asset('assets/images/gallery-5.png') }}" alt="Product" class="w-full h-full object-cover grayscale opacity-80">
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-serif text-[16px] md:text-[18px] font-semibold text-[#1c1c1a] leading-tight mb-1">Structured Trousers</h3>
                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Size S | Warna: Charcoal</p>
                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] mt-1">Qty: 2</p>
                                </div>
                            </div>

                            <!-- Order Footer Actions -->
                            <div class="border-t border-[#e5e2de] p-4 flex flex-col sm:flex-row justify-end gap-3 bg-white">
                                <a href="{{ url('/order-detail') }}" wire:navigate class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#615e57] border border-[#c4c7c7] px-6 py-2.5 hover:border-[#1c1c1a] hover:text-[#1c1c1a] transition-colors w-full sm:w-auto text-center inline-block">Lihat Invoice</a>
                                <button class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-2.5 hover:bg-[#1c1c1a] hover:text-white transition-colors w-full sm:w-auto text-center">Beli Lagi</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.app>
