<x-layouts.app>
    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pb-24 md:pb-32 pt-32 md:pt-40">
            <!-- Header Section -->
            <section class="max-w-[1440px] mx-auto px-6 lg:px-[64px] mb-16 md:mb-24">
                <p class="text-[#615e57] text-[10px] font-mono tracking-[0.2em] uppercase mb-6">Exclusive Offers</p>
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-12">
                    <div class="max-w-2xl">
                        <h1 class="text-[40px] md:text-[56px] lg:text-[72px] font-serif font-bold text-[#1c1c1a] mb-6 leading-tight tracking-tight">
                            Curated Rewards for the Modest Modernist.
                        </h1>
                        <p class="text-[#615e57] text-sm md:text-base leading-relaxed max-w-xl">
                            Discover our seasonal promotions and bespoke member rewards designed to complement your journey into architectural elegance.
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-4 text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase shrink-0 mb-2">
                        <div class="w-12 h-[1px] bg-[#1c1c1a]"></div>
                        <span>Mei, 2026</span>
                    </div>
                </div>
            </section>

            <!-- Filter Tabs -->
            <section class="max-w-[1440px] mx-auto px-6 lg:px-[64px] mb-12 border-b border-[#e5e2de]">
                <div class="flex gap-8 overflow-x-auto pb-4 scrollbar-hide text-[10px] font-mono tracking-widest uppercase text-[#615e57]">
                    <a href="#" class="text-[#1c1c1a] font-bold border-b-2 border-[#1c1c1a] pb-4 -mb-[17px] whitespace-nowrap transition-colors">All Offers</a>
                    <a href="#" class="hover:text-[#1c1c1a] transition-colors whitespace-nowrap pb-4">Member Exclusive</a>
                    <a href="#" class="hover:text-[#1c1c1a] transition-colors whitespace-nowrap pb-4">Seasonal</a>
                    <a href="#" class="hover:text-[#1c1c1a] transition-colors whitespace-nowrap pb-4">Shipping Promo</a>
                </div>
            </section>

            <!-- Promos Grid -->
            <section class="max-w-[1440px] mx-auto px-6 lg:px-[64px] mb-24 md:mb-32">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Row 1: Main Promo (2 cols) -->
                    <div class="md:col-span-2 bg-[#064e3b] flex flex-col md:flex-row relative overflow-hidden group min-h-[400px]">
                        <!-- Background Texture on Right Half -->
                        <div class="absolute inset-y-0 right-0 w-full md:w-1/2 opacity-80 mix-blend-multiply md:mix-blend-normal z-0">
                            <!-- Placeholder texture -->
                            <div class="w-full h-full bg-[#043326] opacity-50"></div>
                        </div>
                        
                        <!-- Content -->
                        <div class="relative z-10 p-8 md:p-12 flex flex-col h-full w-full">
                            <div>
                                <span class="bg-[#fcf9f5] text-[#064e3b] px-3 py-1 text-[10px] font-mono uppercase tracking-widest inline-block mb-6 md:mb-8 font-bold">Member Exclusive</span>
                                <h3 class="text-5xl md:text-6xl lg:text-7xl font-serif text-white mb-2">25% OFF</h3>
                                <p class="text-white text-lg md:text-xl font-serif tracking-wide opacity-90 uppercase">EID ATELIER COLLECTION</p>
                            </div>
                            
                            <div class="mt-auto pt-16 flex flex-col md:flex-row md:items-end justify-between gap-6">
                                <div>
                                    <p class="text-white/60 text-[9px] font-mono tracking-[0.2em] uppercase mb-1">Valid Until</p>
                                    <p class="text-white text-xs font-mono tracking-widest uppercase font-bold">30 May 2026</p>
                                </div>
                                <button class="bg-white text-[#1c1c1a] px-6 py-4 text-[10px] font-mono font-bold tracking-[0.2em] uppercase hover:bg-[#f0ede9] transition-colors">
                                    SALIN KODE: EIDMUBARAK
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Small Promo (1 col) -->
                    <div class="bg-[#f4f2ee] p-8 md:p-10 flex flex-col min-h-[400px]">
                        <div class="mb-auto">
                            <span class="border border-[#064e3b] text-[#064e3b] px-3 py-1 text-[9px] font-mono uppercase tracking-widest inline-block mb-6 font-bold">Seasonal</span>
                            <h3 class="text-4xl md:text-5xl font-serif text-[#1c1c1a] mb-4">15% OFF</h3>
                            <p class="text-[#615e57] text-sm leading-relaxed">Spring Equinox Curation. Valid for all ready to wear items.</p>
                        </div>
                        
                        <div class="pt-8 mt-8 border-t border-[#d1cec9]">
                            <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mb-4">Code: SPRING15</p>
                            <button class="w-full border border-[#1c1c1a] text-[#1c1c1a] px-6 py-4 text-[10px] font-mono font-bold tracking-[0.2em] uppercase hover:bg-[#1c1c1a] hover:text-white transition-colors">
                                SALIN KODE
                            </button>
                        </div>
                    </div>

                    <!-- Row 2: Small Promo (1 col) -->
                    <div class="bg-[#f4f2ee] p-8 md:p-10 flex flex-col min-h-[400px]">
                        <div class="mb-auto">
                            <span class="border border-[#615e57] text-[#615e57] px-3 py-1 text-[9px] font-mono uppercase tracking-widest inline-block mb-6 font-bold">Shipping Promo</span>
                            <h3 class="text-3xl md:text-4xl font-serif text-[#1c1c1a] mb-4 leading-tight uppercase">Complimentary</h3>
                            <p class="text-[#615e57] text-sm leading-relaxed">Worldwide Express Delivery for orders over Rp1.500.000.</p>
                        </div>
                        
                        <div class="pt-8 mt-8 border-t border-[#d1cec9]">
                            <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mb-4">Code: FREESHIPPING</p>
                            <button class="w-full border border-[#1c1c1a] text-[#1c1c1a] px-6 py-4 text-[10px] font-mono font-bold tracking-[0.2em] uppercase hover:bg-[#1c1c1a] hover:text-white transition-colors">
                                SALIN KODE
                            </button>
                        </div>
                    </div>

                    <!-- Row 2: Medium Promo (2 cols) -->
                    <div class="md:col-span-2 bg-[#fcf9f5] border-2 border-[#064e3b] p-8 md:p-12 flex flex-col lg:flex-row gap-12 lg:gap-8 justify-between items-stretch min-h-[400px]">
                        <div class="flex flex-col h-full flex-1">
                            <span class="bg-[#e6efeb] text-[#064e3b] px-3 py-1 text-[9px] font-mono uppercase tracking-widest inline-block self-start mb-6 font-bold">Member Exclusive</span>
                            <h3 class="text-4xl md:text-5xl lg:text-[56px] font-serif text-[#1c1c1a] mb-4">Rp150.000 VOUCHER</h3>
                            <p class="text-[#615e57] text-sm leading-relaxed max-w-sm mb-auto">Exclusive birthday credit for our inner circle members. Minimum spend of Rp500.000 applies.</p>
                            
                            <div class="mt-12 flex items-center gap-4 text-[#064e3b] text-[9px] font-mono tracking-[0.2em] uppercase">
                                <div class="w-8 h-[1px] bg-[#064e3b]"></div>
                                <span>Valid for 30 days from issue</span>
                            </div>
                        </div>
                        
                        <div class="bg-[#f4f2ee] p-8 md:p-10 flex flex-col justify-center items-center text-center shrink-0 w-full lg:w-auto min-w-[280px]">
                            <p class="text-[#615e57] text-[9px] font-mono tracking-[0.2em] uppercase mb-3">Your Unique Code</p>
                            <p class="text-2xl font-serif font-semibold text-[#1c1c1a] mb-8">B-DAY-RAAB-8821</p>
                            <button class="w-full bg-[#064e3b] text-white px-8 py-4 text-[10px] font-mono font-bold tracking-[0.2em] uppercase hover:bg-[#043326] transition-colors">
                                SALIN KODE
                            </button>
                        </div>
                    </div>

                </div>
            </section>

            <!-- Additional Privileges -->
            <section class="max-w-[1440px] mx-auto px-6 lg:px-[64px]">
                <h2 class="text-3xl font-serif font-bold text-[#1c1c1a] mb-12">Additional Privileges</h2>
                
                <div class="flex flex-col">
                    <!-- Item 1 -->
                    <div class="py-8 border-t border-b border-[#e5e2de] flex flex-col md:flex-row md:items-center gap-4 md:gap-12 group cursor-pointer hover:bg-white transition-colors">
                        <div class="w-32 shrink-0">
                            <p class="text-[#615e57] text-[10px] font-mono tracking-[0.2em] uppercase">First Order</p>
                        </div>
                        <div class="w-full md:w-80 shrink-0">
                            <p class="text-xl md:text-[22px] font-serif font-semibold text-[#1c1c1a] uppercase">10% OFF YOUR DEBUT</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-[#615e57] text-sm">New accounts only. No minimum spend.</p>
                        </div>
                        <div class="shrink-0 pt-4 md:pt-0">
                            <span class="text-[#1c1c1a] font-bold text-[10px] font-mono tracking-[0.2em] uppercase border-b border-[#1c1c1a] pb-1 group-hover:text-[#064e3b] group-hover:border-[#064e3b] transition-colors">COPY: WELCOME10</span>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="py-8 border-b border-[#e5e2de] flex flex-col md:flex-row md:items-center gap-4 md:gap-12 group cursor-pointer hover:bg-white transition-colors">
                        <div class="w-32 shrink-0">
                            <p class="text-[#615e57] text-[10px] font-mono tracking-[0.2em] uppercase">Bespoke</p>
                        </div>
                        <div class="w-full md:w-80 shrink-0">
                            <p class="text-xl md:text-[22px] font-serif font-semibold text-[#1c1c1a] uppercase">FREE CONSULTATION</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-[#615e57] text-sm">Personal atelier session with our designers.</p>
                        </div>
                        <div class="shrink-0 pt-4 md:pt-0">
                            <span class="text-[#1c1c1a] font-bold text-[10px] font-mono tracking-[0.2em] uppercase border-b border-[#1c1c1a] pb-1 group-hover:text-[#064e3b] group-hover:border-[#064e3b] transition-colors">BOOK APPOINTMENT</span>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>
</x-layouts.app>
