<?php
/**
 * Cart Page Override
 *
 * This template overrides the default WooCommerce cart to perfectly match
 * the Raabiha Editorial Brutalism & Architectural Modesty design system.
 * 
 * Note: Currently static UI as requested by CTO.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="max-w-[1440px] mx-auto px-6 md:px-[64px] py-12 md:py-24">
    <!-- Header -->
    <div class="mb-16">
        <h1 class="font-serif text-[32px] md:text-[48px] font-bold text-[#1c1c1a] tracking-tight uppercase">Your Bag</h1>
        <div class="font-mono text-[9px] font-medium tracking-[0.2em] text-[#615e57] uppercase mt-2">2 Items Selected</div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-12 lg:gap-16 items-start">
        
        <!-- Left Column: Product List -->
        <div class="flex flex-col">
            
            <!-- Item 1 -->
            <div class="flex flex-col sm:flex-row gap-8 pb-12 mb-12 border-b border-[rgba(23,24,24,0.1)]">
                <!-- Image -->
                <div class="w-full sm:w-[280px] h-[320px] sm:h-[280px] bg-[#f0ede9] shrink-0">
                    <img src="https://images.unsplash.com/photo-1584273143981-41c073dfe8f8?auto=format&fit=crop&q=80&w=800" alt="The Artisanal Emerald Abaya" class="w-full h-full object-cover grayscale-[20%]">
                </div>
                <!-- Info -->
                <div class="flex flex-col flex-1 py-2">
                    <div class="flex justify-between items-start gap-4">
                        <h3 class="font-serif text-[24px] font-semibold text-[#1c1c1a] leading-tight">The Artisanal Emerald Abaya</h3>
                        <div class="font-sans text-[18px] text-[#1c1c1a] whitespace-nowrap">$1,250.00</div>
                    </div>
                    <div class="font-mono text-[9px] uppercase tracking-[0.1em] text-[#615e57] mt-2 mb-8">SKU: RB-2024-040</div>
                    
                    <div class="grid grid-cols-[80px_1fr] gap-y-4 mb-auto">
                        <div class="font-mono text-[10px] font-semibold tracking-[0.1em] text-[#1c1c1a] uppercase">Color</div>
                        <div class="font-sans text-[14px] text-[#1c1c1a]">EMERALD SILK</div>
                        <div class="font-mono text-[10px] font-semibold tracking-[0.1em] text-[#1c1c1a] uppercase">Size</div>
                        <div class="font-sans text-[14px] text-[#1c1c1a]">MEDIUM</div>
                    </div>
                    
                    <div class="flex justify-between items-end mt-8">
                        <div class="border border-[#e5e2de] flex items-center h-[48px]">
                            <button type="button" class="px-4 text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                            </button>
                            <span class="font-mono text-[14px] px-2 min-w-[40px] text-center text-[#1c1c1a]">01</span>
                            <button type="button" class="px-4 text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <button type="button" class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] hover:text-[#1c1c1a] transition-colors group">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Remove</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="flex flex-col sm:flex-row gap-8 pb-12 mb-12 border-b border-[rgba(23,24,24,0.1)]">
                <!-- Image -->
                <div class="w-full sm:w-[280px] h-[320px] sm:h-[280px] bg-[#f0ede9] shrink-0">
                    <img src="https://images.unsplash.com/photo-1600093463592-8e36ae95ef56?auto=format&fit=crop&q=80&w=800" alt="Bespoke Silk Veil" class="w-full h-full object-cover grayscale-[20%]">
                </div>
                <!-- Info -->
                <div class="flex flex-col flex-1 py-2">
                    <div class="flex justify-between items-start gap-4">
                        <h3 class="font-serif text-[24px] font-semibold text-[#1c1c1a] leading-tight">Bespoke Silk Veil</h3>
                        <div class="font-sans text-[18px] text-[#1c1c1a] whitespace-nowrap">$450.00</div>
                    </div>
                    <div class="font-mono text-[9px] uppercase tracking-[0.1em] text-[#615e57] mt-2 mb-8">SKU: RB-2024-033</div>
                    
                    <div class="grid grid-cols-[80px_1fr] gap-y-4 mb-auto">
                        <div class="font-mono text-[10px] font-semibold tracking-[0.1em] text-[#1c1c1a] uppercase">Color</div>
                        <div class="font-sans text-[14px] text-[#1c1c1a]">DEEP EMERALD</div>
                        <div class="font-mono text-[10px] font-semibold tracking-[0.1em] text-[#1c1c1a] uppercase">Size</div>
                        <div class="font-sans text-[14px] text-[#1c1c1a]">ONE SIZE</div>
                    </div>
                    
                    <div class="flex justify-between items-end mt-8">
                        <div class="border border-[#e5e2de] flex items-center h-[48px]">
                            <button type="button" class="px-4 text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                            </button>
                            <span class="font-mono text-[14px] px-2 min-w-[40px] text-center text-[#1c1c1a]">01</span>
                            <button type="button" class="px-4 text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <button type="button" class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] hover:text-[#1c1c1a] transition-colors group">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Remove</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Trust Banner -->
            <div class="bg-[#f6f3ef] p-6 mt-4 flex flex-col sm:flex-row gap-6 items-start sm:items-center">
                <div class="text-[#064e3b] shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <div>
                    <h4 class="font-sans text-[14px] font-semibold text-[#1c1c1a] mb-1">Sustainable Architectural Craftsmanship</h4>
                    <p class="font-sans text-[14px] text-[#615e57]">Your order supports artisanal communities and uses only 100% biodegradable silk packaging.</p>
                </div>
            </div>

        </div>

        <!-- Right Column: Order Summary -->
        <div class="bg-[#f0ede9] p-8 lg:p-10 sticky top-[120px]">
            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-8">Order Summary</h2>
            
            <div class="flex flex-col gap-6 font-sans text-[14px] text-[#1c1c1a]">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>$1,700.00</span>
                </div>
                <div class="flex justify-between">
                    <span>Estimated Shipping</span>
                    <span>$35.00</span>
                </div>
                <div class="flex justify-between">
                    <span>Tax</span>
                    <span>$144.50</span>
                </div>
            </div>
            
            <div class="h-px bg-[rgba(23,24,24,0.1)] my-8"></div>
            
            <!-- Promo Code -->
            <div class="mb-10">
                <label class="font-mono text-[9px] font-semibold tracking-[0.2em] text-[#615e57] uppercase mb-4 block">Promo Code</label>
                <div class="flex justify-between border-b border-[#1c1c1a] pb-3">
                    <input type="text" placeholder="ENTER CODE" class="font-mono text-[10px] uppercase tracking-[0.1em] bg-transparent outline-none w-full text-[#1c1c1a] placeholder-[#a3a3a3]">
                    <button type="button" class="font-mono text-[10px] font-bold uppercase tracking-[0.1em] text-[#1c1c1a] hover:text-[#064e3b] transition-colors ml-4 shrink-0">Apply</button>
                </div>
            </div>
            
            <!-- Total -->
            <div class="flex justify-between items-end mb-8">
                <span class="font-serif text-[24px] font-semibold text-[#1c1c1a]">TOTAL</span>
                <span class="font-serif text-[32px] font-semibold text-[#1c1c1a] leading-none">$1,879.50</span>
            </div>
            
            <!-- Button -->
            <button type="button" class="w-full bg-[#064e3b] hover:bg-[#043326] text-white py-5 px-6 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors">
                Proceed To Checkout
            </button>
            
            <!-- Trust Badges -->
            <div class="mt-8 flex flex-col items-center gap-4">
                <div class="flex items-center gap-2 text-[#615e57]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span class="font-mono text-[9px] uppercase tracking-[0.1em]">Secure Checkout Guaranteed</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="border border-[#c4c7c7] px-2 py-0.5 font-mono text-[8px] uppercase tracking-wider text-[#615e57]">Visa</div>
                    <div class="border border-[#c4c7c7] px-2 py-0.5 font-mono text-[8px] uppercase tracking-wider text-[#615e57]">Amex</div>
                    <div class="border border-[#c4c7c7] px-2 py-0.5 font-mono text-[8px] uppercase tracking-wider text-[#615e57]">Apple Pay</div>
                </div>
            </div>
        </div>
        
    </div>
</div>

