<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Detail Pesanan" backUrl="/account" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pb-20 md:pb-0">
            <div class="max-w-[800px] mx-auto px-6 py-12 md:py-24">
                
                <!-- Back Link (Desktop) -->
                <a href="{{ url('/account') }}" wire:navigate.hover class="hidden md:inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-[#615e57] hover:text-[#1c1c1a] transition-colors mb-8">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Akun
                </a>

                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10 border-b border-[#e5e2de] pb-6">
                    <div>
                        <h1 class="font-serif text-[28px] md:text-[36px] font-bold text-[#1c1c1a] tracking-tight uppercase">Detail Pesanan</h1>
                        <p class="font-mono text-[10px] font-medium tracking-[0.2em] text-[#615e57] uppercase mt-2">Nomor: #RBH-0988 <span class="mx-2">|</span> 28 Sep 2024</p>
                    </div>
                    <div class="bg-[#064e3b]/10 text-[#064e3b] border border-[#064e3b]/20 px-4 py-2 font-mono text-[10px] font-bold tracking-widest uppercase self-start md:self-end">
                        Selesai
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                    <div class="bg-white border border-[#e5e2de] p-6">
                        <h3 class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#1c1c1a] mb-4">Alamat Pengiriman</h3>
                        <p class="font-sans text-[14px] font-semibold text-[#1c1c1a] mb-1">Jane Doe</p>
                        <p class="font-sans text-[14px] text-[#615e57] leading-relaxed">
                            Jl. Sudirman Kav. 52-53, Gedung Equity Tower Lt. 12<br>
                            Kebayoran Baru, Jakarta Selatan<br>
                            DKI Jakarta, 12190
                        </p>
                        <p class="font-sans text-[14px] text-[#615e57] mt-2">0812-3456-7890</p>
                    </div>
                    
                    <div class="bg-white border border-[#e5e2de] p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#1c1c1a] mb-4">Informasi Pengiriman</h3>
                            <p class="font-sans text-[14px] font-semibold text-[#1c1c1a] mb-1">JNE Reguler</p>
                            <p class="font-mono text-[11px] font-bold text-[#064e3b] mt-2 tracking-wider">RESI: 0110384828192</p>
                        </div>
                        <button class="font-mono text-[9px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-4 py-2 mt-4 hover:bg-[#1c1c1a] hover:text-white transition-colors text-center">Lacak Paket</button>
                    </div>
                </div>

                <!-- Order Items -->
                <h3 class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#1c1c1a] mb-4">Produk yang Dibeli</h3>
                <div class="border border-[#e5e2de] bg-white mb-8">
                    <!-- Item -->
                    <div class="p-4 md:p-6 flex flex-col md:flex-row gap-4 md:gap-6 md:items-center">
                        <div class="w-16 h-20 bg-[#e5e2de] shrink-0">
                            <img src="{{ asset('assets/images/gallery-5.png') }}" alt="Product" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <h4 class="font-serif text-[16px] font-semibold text-[#1c1c1a] leading-tight mb-1">Structured Trousers</h4>
                            <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Size S | Warna: Charcoal</p>
                        </div>
                        <div class="font-sans text-[14px] text-[#1c1c1a] whitespace-nowrap text-right">
                            <span class="text-[#615e57] mr-2">2 x</span> Rp650.000
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="bg-[#f0ede9] p-6 border border-[#e5e2de] flex flex-col gap-3 font-sans text-[14px]">
                    <div class="flex justify-between text-[#615e57]">
                        <span>Subtotal (2 Produk)</span>
                        <span>Rp1.300.000</span>
                    </div>
                    <div class="flex justify-between text-[#615e57]">
                        <span>Ongkos Kirim</span>
                        <span>Rp20.000</span>
                    </div>
                    <div class="flex justify-between text-[#064e3b]">
                        <span>Diskon / Voucher</span>
                        <span>-Rp20.000</span>
                    </div>
                    <div class="h-px bg-[rgba(23,24,24,0.1)] my-2"></div>
                    <div class="flex justify-between items-end">
                        <span class="font-mono text-[10px] uppercase tracking-widest font-bold text-[#1c1c1a]">Total Pembayaran</span>
                        <span class="font-serif text-[24px] font-semibold text-[#1c1c1a]">Rp1.300.000</span>
                    </div>
                </div>

            </div>
        </main>
    </div>
</x-layouts.app>
