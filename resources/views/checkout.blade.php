<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Checkout" backUrl="/cart" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pb-0">
            <div class="max-w-[1440px] mx-auto px-6 md:px-[64px] py-12 md:py-24">
                
                <div class="mb-10 md:mb-16 hidden md:block">
                    <h1 class="font-serif text-[32px] md:text-[48px] font-bold text-[#1c1c1a] tracking-tight uppercase">Checkout</h1>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-12 lg:gap-16 items-start">
                    
                    <!-- Left Column: Checkout Forms -->
                    <div class="flex flex-col gap-10">
                        
                        <!-- Login Banner -->
                        <div class="bg-[#f0ede9] p-4 border border-[#e5e2de] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="font-sans text-sm text-[#1c1c1a]">Sudah punya akun? Log in untuk proses checkout lebih cepat.</div>
                            <button type="button" onclick="window.location.href='/login'" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-2 hover:bg-[#1c1c1a] hover:text-white transition-colors">Log In</button>
                        </div>
                        
                        <!-- Section 1: Contact -->
                        <section class="border-b border-[#e5e2de] pb-10">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-6">1. Informasi Kontak</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Email Address *</label>
                                    <input type="email" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="your@email.com">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Nomor Telepon *</label>
                                    <input type="tel" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="0812xxxxxx">
                                </div>
                            </div>
                        </section>

                        <!-- Section 2: Shipping -->
                        <section class="border-b border-[#e5e2de] pb-10">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-6">2. Alamat Pengiriman</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Nama Depan *</label>
                                    <input type="text" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Nama Belakang *</label>
                                    <input type="text" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors">
                                </div>
                                <div class="flex flex-col gap-2 md:col-span-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Alamat Lengkap *</label>
                                    <textarea rows="3" class="w-full bg-transparent border border-[#e5e2de] p-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="Nama Jalan, Gedung, No. Rumah"></textarea>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Provinsi *</label>
                                    <select class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors">
                                        <option value="">Pilih Provinsi</option>
                                        <option value="dki">DKI Jakarta</option>
                                        <option value="jabar">Jawa Barat</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Kota / Kabupaten *</label>
                                    <select class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors">
                                        <option value="">Pilih Kota</option>
                                        <option value="jaksel">Jakarta Selatan</option>
                                        <option value="bandung">Bandung</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Kecamatan *</label>
                                    <input type="text" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Kode Pos *</label>
                                    <input type="text" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors">
                                </div>
                                <div class="flex flex-col gap-2 md:col-span-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Catatan Pesanan (Opsional)</label>
                                    <textarea rows="2" class="w-full bg-transparent border border-[#e5e2de] p-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="Catatan untuk penjual atau kurir, misal: Tolong titip di pos satpam."></textarea>
                                </div>
                                <div class="md:col-span-2 mt-2">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" class="w-4 h-4 text-[#064e3b] focus:ring-[#064e3b] border-gray-300 rounded-sm">
                                        <span class="font-sans text-sm text-[#615e57] group-hover:text-[#1c1c1a] transition-colors">Simpan informasi ini untuk belanja berikutnya</span>
                                    </label>
                                </div>
                            </div>
                        </section>

                        <!-- Section 3: Shipping Method -->
                        <section class="border-b border-[#e5e2de] pb-10">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-6">3. Metode Pengiriman</h2>
                            <div class="flex flex-col gap-4">
                                <label class="flex justify-between items-center border border-[#064e3b] bg-[#f0ede9] p-4 cursor-pointer transition-colors">
                                    <div class="flex items-center gap-4">
                                        <input type="radio" name="shipping" class="w-4 h-4 text-[#064e3b] focus:ring-[#064e3b] border-gray-300" checked>
                                        <div>
                                            <span class="block font-serif text-base text-[#1c1c1a]">JNE Reguler</span>
                                            <span class="block font-sans text-xs text-[#615e57] mt-1">Estimasi 2-3 hari kerja</span>
                                        </div>
                                    </div>
                                    <span class="font-sans text-sm font-semibold text-[#1c1c1a]">Rp20.000</span>
                                </label>
                                <label class="flex justify-between items-center border border-[#e5e2de] p-4 cursor-pointer hover:border-[#1c1c1a] transition-colors">
                                    <div class="flex items-center gap-4">
                                        <input type="radio" name="shipping" class="w-4 h-4 text-[#064e3b] focus:ring-[#064e3b] border-gray-300">
                                        <div>
                                            <span class="block font-serif text-base text-[#1c1c1a]">JNE YES</span>
                                            <span class="block font-sans text-xs text-[#615e57] mt-1">Estimasi 1 hari kerja</span>
                                        </div>
                                    </div>
                                    <span class="font-sans text-sm font-semibold text-[#1c1c1a]">Rp35.000</span>
                                </label>
                            </div>
                        </section>

                        <!-- Section 4: Payment -->
                        <section class="pb-6">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-6">4. Metode Pembayaran</h2>
                            <div class="flex flex-col gap-4">
                                <label class="flex items-center gap-4 border border-[#064e3b] bg-[#f0ede9] p-4 cursor-pointer transition-colors">
                                    <input type="radio" name="payment" class="w-4 h-4 text-[#064e3b] focus:ring-[#064e3b] border-gray-300" checked>
                                    <span class="font-serif text-base text-[#1c1c1a]">Bank Transfer (Virtual Account)</span>
                                </label>
                                <label class="flex items-center gap-4 border border-[#e5e2de] p-4 cursor-pointer hover:border-[#1c1c1a] transition-colors">
                                    <input type="radio" name="payment" class="w-4 h-4 text-[#064e3b] focus:ring-[#064e3b] border-gray-300">
                                    <span class="font-serif text-base text-[#1c1c1a]">QRIS</span>
                                </label>
                                <label class="flex items-center gap-4 border border-[#e5e2de] p-4 cursor-pointer hover:border-[#1c1c1a] transition-colors">
                                    <input type="radio" name="payment" class="w-4 h-4 text-[#064e3b] focus:ring-[#064e3b] border-gray-300">
                                    <span class="font-serif text-base text-[#1c1c1a]">Credit / Debit Card</span>
                                </label>
                            </div>
                        </section>

                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="bg-transparent lg:bg-[#f0ede9] p-0 lg:p-10 sticky top-[120px]">
                        <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-8 hidden lg:block">Ringkasan Pesanan</h2>
                        
                        <!-- Order Items Mini -->
                        <div class="flex flex-col gap-4 mb-8">
                            <div class="flex gap-4 items-center">
                                <div class="w-16 h-16 bg-[#e5e2de] shrink-0">
                                    <img src="{{ asset('assets/images/gallery-3.png') }}" alt="Product" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-serif text-sm text-[#1c1c1a]">The Asymmetrical Tunic</h4>
                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Size M/L | Qty: 1</p>
                                </div>
                                <div class="font-sans text-sm text-[#1c1c1a]">Rp850.000</div>
                            </div>
                            <div class="flex gap-4 items-center">
                                <div class="w-16 h-16 bg-[#e5e2de] shrink-0">
                                    <img src="{{ asset('assets/images/gallery-5.png') }}" alt="Product" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-serif text-sm text-[#1c1c1a]">Structured Trousers</h4>
                                    <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Size S | Qty: 1</p>
                                </div>
                                <div class="font-sans text-sm text-[#1c1c1a]">Rp650.000</div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-0 lg:gap-4 font-sans text-[14px] text-[#1c1c1a]">
                            <div class="flex justify-between py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                                <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] lg:text-[#1c1c1a] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal">Subtotal</span>
                                <span>Rp1.500.000</span>
                            </div>
                            <div class="flex justify-between py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                                <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] lg:text-[#1c1c1a] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal">Pengiriman</span>
                                <span>Rp20.000</span>
                            </div>
                        </div>
                        
                        <div class="h-px bg-[rgba(23,24,24,0.1)] my-6 hidden lg:block"></div>
                        
                        <!-- Total -->
                        <div class="flex justify-between items-end mb-8 py-6 lg:py-0">
                            <span class="font-mono text-[12px] lg:font-serif lg:text-[24px] font-bold lg:font-semibold tracking-[0.1em] lg:tracking-normal text-[#1c1c1a] uppercase">TOTAL</span>
                            <span class="font-serif text-[24px] lg:text-[32px] font-semibold text-[#1c1c1a] leading-none">Rp1.520.000</span>
                        </div>
                        
                        <!-- Button -->
                        <button type="button" onclick="window.location.href='/order-success'" class="flex justify-center items-center gap-2 w-full bg-[#064e3b] hover:bg-[#043326] text-white py-5 px-6 font-mono text-[10px] md:text-[12px] lg:text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors">
                            <span class="block">BAYAR SEKARANG</span>
                        </button>
                        
                        <!-- Trust Badges -->
                        <div class="mt-8 flex flex-col items-center gap-4">
                            <div class="flex items-center gap-2 text-[#615e57]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span class="font-mono text-[9px] uppercase tracking-[0.1em]">Pembayaran Aman Terjamin</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.app>
