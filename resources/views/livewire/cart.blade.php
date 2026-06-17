<div x-data="{ bsVoucherOpen: false }" 
     x-effect="document.body.style.overflow = bsVoucherOpen ? 'hidden' : ''"
     @close-voucher-sheet.window="bsVoucherOpen = false">
    @slot('header')
        <x-global.mobile-subnav title="Keranjang" backUrl="/shop" />
    @endslot

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pb-40 lg:pb-0">
            <div class="max-w-[1440px] mx-auto px-6 md:px-[64px] py-12 md:py-24">
                <!-- Header -->
                <div class="mb-10 md:mb-16 hidden md:block">
                    <h1 class="font-serif text-[32px] md:text-[48px] font-bold text-[#1c1c1a] tracking-tight uppercase">Keranjang</h1>
                    <div class="font-mono text-[9px] font-medium tracking-[0.2em] text-[#615e57] uppercase mt-2">
                        {{ $cart ? $cart->items->count() : 0 }} Produk Terpilih
                    </div>
                </div>

                @if(!$cart || $cart->items->isEmpty())
                    <div class="text-center py-24">
                        <svg class="w-16 h-16 mx-auto text-[#e5e2de] mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <h2 class="font-serif text-2xl text-[#1c1c1a] mb-4">Keranjang Anda Kosong</h2>
                        <p class="text-[#615e57] font-sans mb-8">Anda belum menambahkan produk apa pun ke keranjang belanja.</p>
                        <a href="/shop" wire:navigate.hover class="inline-block bg-[#064e3b] text-white px-8 py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase hover:bg-[#043326] transition-colors">MULAI BELANJA</a>
                    </div>
                @else
                    <!-- Main Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-12 lg:gap-16 items-start">
                        
                        <!-- Left Column: Product List -->
                        <div class="flex flex-col">
                            <!-- Select All -->
                            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-[#e5e2de] sm:border-[rgba(23,24,24,0.1)]">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 sm:w-5 sm:h-5 text-[#064e3b] bg-transparent border border-[#e5e2de] rounded-sm focus:ring-[#064e3b] focus:ring-offset-0 cursor-pointer accent-[#064e3b]">
                                    <span class="font-mono text-[11px] uppercase tracking-widest font-bold text-[#1c1c1a]">Pilih Semua ({{ $cart->items->count() }})</span>
                                </label>
                            </div>

                            @foreach($cart->items as $item)
                                @php
                                    $imageUrl = asset('assets/images/placeholder.png');
                                    if (!empty($item->product->images)) {
                                        if (is_numeric($item->product->images[0])) {
                                            $media = \Awcodes\Curator\Models\Media::find($item->product->images[0]);
                                            if ($media) {
                                                $imageUrl = $media->url;
                                            }
                                        } else {
                                            $imageUrl = asset('storage/' . $item->product->images[0]);
                                        }
                                    }
                                    
                                    $price = $item->variant && $item->variant->price ? $item->variant->price : $item->product->price;
                                @endphp

                                <div class="cart-item-row flex flex-row sm:flex-row gap-4 sm:gap-6 pb-6 sm:pb-12 mb-6 sm:mb-12 border-b border-[#e5e2de] sm:border-[rgba(23,24,24,0.1)] items-center sm:items-start">
                                    <!-- Checkbox -->
                                    <div class="shrink-0 flex items-center justify-center pt-0 sm:pt-4">
                                        <input type="checkbox" wire:model.live="selectedItems" value="{{ $item->id }}" class="w-4 h-4 sm:w-5 sm:h-5 text-[#064e3b] bg-transparent border border-[#e5e2de] rounded-sm focus:ring-[#064e3b] focus:ring-offset-0 cursor-pointer accent-[#064e3b]">
                                    </div>
                                    <!-- Image -->
                                    <div class="w-[80px] h-[100px] sm:w-[240px] sm:h-[280px] bg-[#f0ede9] shrink-0">
                                        <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <!-- Info -->
                                    <div class="flex flex-col flex-1 py-0 sm:py-2 min-w-0">
                                        <div class="flex flex-col sm:flex-row justify-between items-start gap-1 sm:gap-4 w-full">
                                            <h3 class="font-serif text-base sm:text-[24px] font-semibold text-[#1c1c1a] leading-tight sm:normal-case line-clamp-2 sm:line-clamp-none pr-1">
                                                <a href="/product/{{ $item->product->slug }}" wire:navigate.hover class="hover:text-[#615e57] transition-colors">{{ $item->product->name }}</a>
                                            </h3>
                                            <div class="item-subtotal-val font-sans text-sm sm:text-[18px] font-medium sm:font-normal text-[#1c1c1a] whitespace-nowrap shrink-0">
                                                Rp{{ number_format($price * $item->quantity, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        
                                        @if($item->variant)
                                            <!-- Mobile Variations -->
                                            <div class="flex sm:hidden font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] mt-1 mb-auto flex-wrap gap-1 truncate w-full">
                                                @foreach($item->variant->attributeOptions as $option)
                                                    {{ $option->attribute->name }}: {{ $option->value }} @if(!$loop->last) <span class="mx-1">/</span> @endif
                                                @endforeach
                                            </div>
                                            
                                            <!-- Desktop Variations -->
                                            <div class="hidden sm:block font-mono text-[9px] uppercase tracking-[0.1em] text-[#615e57] mt-2 mb-8">SKU: {{ $item->variant->sku ?? $item->product->sku ?? '-' }}</div>
                                            <div class="hidden sm:grid grid-cols-[80px_1fr] gap-y-4 mb-auto">
                                                @foreach($item->variant->attributeOptions as $option)
                                                    <div class="font-mono text-[10px] font-semibold tracking-[0.1em] text-[#1c1c1a] uppercase">{{ $option->attribute->name }}</div>
                                                    <div class="font-sans text-[14px] text-[#1c1c1a] uppercase">{{ $option->value }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="hidden sm:block font-mono text-[9px] uppercase tracking-[0.1em] text-[#615e57] mt-2 mb-8">SKU: {{ $item->product->sku ?? '-' }}</div>
                                            <div class="mb-auto"></div>
                                        @endif
                                        
                                        <div class="flex justify-between items-center mt-4 sm:mt-8 w-full">
                                            <div class="border border-[#e5e2de] flex items-stretch h-[36px] sm:h-[48px] qty-container">
                                                <button type="button" wire:click="decrementQuantity({{ $item->id }})" class="w-10 sm:w-12 h-full flex items-center justify-center text-[#615e57] hover:text-[#1c1c1a] hover:bg-black/5 transition-colors focus:outline-none">
                                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                                </button>
                                                <input type="number" readonly value="{{ $item->quantity }}" min="1" class="h-full w-8 sm:w-12 bg-transparent text-center font-mono text-[12px] sm:text-[14px] text-[#1c1c1a] focus:outline-none appearance-none p-0 m-0 border-0" style="-moz-appearance: textfield;">
                                                <button type="button" wire:click="incrementQuantity({{ $item->id }})" class="w-10 sm:w-12 h-full flex items-center justify-center text-[#615e57] hover:text-[#1c1c1a] hover:bg-black/5 transition-colors focus:outline-none">
                                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                </button>
                                            </div>
                                            <!-- Mobile Remove Link -->
                                            <button type="button" wire:click="removeItem({{ $item->id }})" class="block sm:hidden font-mono text-[10px] font-bold tracking-[0.1em] text-[#ba1a1a] uppercase hover:text-[#1c1c1a] transition-colors focus:outline-none">REMOVE</button>
                                            <!-- Desktop Remove Link -->
                                            <button type="button" wire:click="removeItem({{ $item->id }})" class="hidden sm:flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] hover:text-[#1c1c1a] transition-colors group focus:outline-none"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg><span>Hapus</span></button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Bottom Trust Banner (Mobile) -->
                            <div class="flex sm:hidden bg-[#fcf9f5] border-l-2 border-[#064e3b] p-4 mt-0 mb-12 items-start gap-4">
                                <div class="text-[#064e3b] shrink-0 mt-0.5">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 12c0 4.418 3.582 8 8 8s8-3.582 8-8c0-4.418-3.582-8-8-8-4.418 0-8 3.582-8 8zm0 0l8-8" /></svg>
                                </div>
                                <div>
                                    <p class="font-sans text-[12px] text-[#615e57] leading-relaxed">Pesanan Anda mendukung <span class="font-bold text-[#1c1c1a]">Keahlian Arsitektural Berkelanjutan</span> dan menggunakan kemasan sutra biodegradable.</p>
                                </div>
                            </div>
                            
                            <!-- Bottom Trust Banner (Desktop) -->
                            <div class="hidden sm:flex bg-[#f6f3ef] p-6 mt-4 flex-col sm:flex-row gap-6 items-start sm:items-center">
                                <div class="text-[#064e3b] shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-sans text-[14px] font-semibold text-[#1c1c1a] mb-1">Keahlian Arsitektural Berkelanjutan</h4>
                                    <p class="font-sans text-[14px] text-[#615e57]">Pesanan Anda mendukung komunitas pengrajin dan murni menggunakan kemasan sutra 100% biodegradable.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Order Summary (Desktop Only) -->
                        <div class="hidden lg:block bg-[#f0ede9] p-10 sticky top-[120px]">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-8">Ringkasan Pesanan</h2>
                            
                            @if (session()->has('error'))
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-xs font-mono" role="alert">
                                    <span class="block sm:inline">{{ session('error') }}</span>
                                </div>
                            @endif

                            <div class="flex flex-col gap-6 font-sans text-[14px] text-[#1c1c1a]">
                                <!-- Subtotal Row -->
                                <div class="flex justify-between py-0">
                                    <span class="text-[#1c1c1a] font-sans text-[14px]">Subtotal ({{ count($selectedItems) }} Produk)</span>
                                    <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                @if(isset($resellerDiscount) && $resellerDiscount > 0)
                                <div class="flex justify-between py-0 mt-3 text-[#064e3b]">
                                    <span class="font-sans text-[14px]">Diskon Reseller</span>
                                    <span>-Rp{{ number_format($resellerDiscount, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                @if($discountAmount > 0)
                                <div class="flex justify-between py-0 mt-3 text-[#064e3b]">
                                    <span class="font-sans text-[14px]">Diskon ({{ $appliedVoucher['code'] ?? '' }})</span>
                                    <span>-Rp{{ number_format($discountAmount, 0, ',', '.') }}</span>
                                </div>
                                @endif
                            </div>
                            
                            <div class="h-px bg-[rgba(23,24,24,0.1)] my-8"></div>
                            
                            <!-- Promo Code -->
                            <div class="mb-10 py-0">
                                <label class="font-mono text-[9px] font-semibold tracking-[0.2em] text-[#615e57] uppercase mb-4 block">Kode Promo / Voucher</label>
                                @if($appliedVoucher)
                                <div class="w-full border py-3 px-4 flex justify-between items-center text-left bg-[#f0ede9] border-[#064e3b]">
                                    <div class="flex flex-col">
                                        <span class="font-mono text-[10px] uppercase tracking-[0.1em] font-bold text-[#064e3b]">{{ $appliedVoucher['code'] }}</span>
                                        <span class="font-sans text-[11px] text-[#064e3b] mt-0.5">Voucher diterapkan</span>
                                    </div>
                                    <button type="button" wire:click="removeVoucher" class="text-[#ba1a1a] hover:text-black focus:outline-none p-1">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                @else
                                <button type="button" @click="bsVoucherOpen = true" class="w-full border py-3 px-4 flex justify-between items-center text-left transition-colors bg-transparent border-[#1c1c1a] hover:bg-[#f6f3ef] text-[#1c1c1a]">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.1em] font-bold flex items-center gap-2">PILIH VOUCHER</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                @endif
                            </div>
                            
                            <!-- Total -->
                            <div class="flex justify-between items-end mb-8 py-0">
                                <span class="font-serif text-[24px] font-semibold text-[#1c1c1a] uppercase">TOTAL</span>
                                <span class="font-serif text-[32px] font-semibold text-[#1c1c1a] leading-none">Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
                            </div>
                            
                            <!-- Button -->
                            <button type="button" wire:click="proceedToCheckout" class="flex justify-center items-center gap-2 w-full bg-[#064e3b] hover:bg-[#043326] text-white py-5 px-6 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors">
                                <span class="block">LANJUT KE PEMBAYARAN</span>
                            </button>
                            
                            <!-- Trust Badges -->
                            <div class="mt-8 flex flex-col items-center gap-4">
                                <div class="flex items-center gap-2 text-[#615e57]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="font-mono text-[9px] uppercase tracking-[0.1em]">Pembayaran Aman Terjamin</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="border border-[#c4c7c7] px-2 py-0.5 font-mono text-[8px] uppercase tracking-wider text-[#615e57]">BCA</div>
                                    <div class="border border-[#c4c7c7] px-2 py-0.5 font-mono text-[8px] uppercase tracking-wider text-[#615e57]">Mandiri</div>
                                    <div class="border border-[#c4c7c7] px-2 py-0.5 font-mono text-[8px] uppercase tracking-wider text-[#615e57]">QRIS</div>
                                </div>
                            </div>
                        </div>
                    </div>


                @endif
            </div>
        </main>
    </div>

    @if($cart && $cart->items->isNotEmpty())
    <!-- Mobile Sticky Bottom Bar -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-[#e5e2de] z-[90] pb-safe shadow-[0_-5px_15px_rgba(0,0,0,0.05)]">
        @if (session()->has('error'))
            <div class="bg-red-50 text-red-600 px-5 py-2 text-[10px] font-mono tracking-widest uppercase border-b border-red-100 text-center">
                {{ session('error') }}
            </div>
        @endif
        <!-- Voucher -->
        @if($appliedVoucher)
        <div class="w-full px-5 py-3.5 border-b border-[#e5e2de] flex justify-between items-center bg-[#f0ede9]">
            <div class="flex flex-col">
                <span class="font-mono text-[10px] uppercase tracking-[0.1em] font-bold text-[#064e3b]">{{ $appliedVoucher['code'] }}</span>
                <span class="font-sans text-[10px] text-[#064e3b] mt-0.5">Berhasil diterapkan</span>
            </div>
            <button type="button" wire:click="removeVoucher" class="text-[#ba1a1a] p-2 hover:bg-red-50 rounded-full focus:outline-none">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @else
        <button type="button" @click="bsVoucherOpen = true" class="w-full px-5 py-3.5 border-b border-[#e5e2de] flex justify-between items-center bg-[#fcf9f5] transition-colors hover:bg-[#f6f3ef]">
            <span class="font-mono text-[10px] uppercase tracking-[0.1em] font-bold text-[#1c1c1a]">PILIH VOUCHER</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        @endif
        <!-- Total & Button -->
        <div class="px-5 py-4 bg-white flex justify-between items-center gap-4">
            <div class="flex flex-col min-w-0">
                <span class="font-mono text-[9px] uppercase tracking-[0.1em] text-[#615e57] mb-1">TOTAL BELANJA</span>
                <span class="font-serif text-[18px] font-semibold text-[#1c1c1a] leading-none truncate">Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
            <button type="button" wire:click="proceedToCheckout" class="bg-[#064e3b] text-white px-5 py-3.5 font-mono text-[10px] font-bold tracking-[0.1em] uppercase transition-colors hover:bg-[#043326] flex-shrink-0 min-w-[120px] flex items-center justify-center">
                <span wire:loading.remove wire:target="proceedToCheckout">CHECKOUT ({{ count($selectedItems) }})</span>
                <span wire:loading wire:target="proceedToCheckout">TUNGGU...</span>
            </button>
        </div>
    </div>
    @endif

    <!-- Voucher Bottom Sheet / Modal -->
    <div x-show="bsVoucherOpen" style="display: none;" class="fixed inset-0 z-[100] lg:flex lg:items-center lg:justify-center">
        <!-- Backdrop -->
        <div x-show="bsVoucherOpen" 
             x-transition.opacity.duration.300ms
             @click="bsVoucherOpen = false" 
             class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
             
        <!-- Sheet -->
        <div x-show="bsVoucherOpen" 
             x-transition:enter="transition-transform duration-300 lg:transition-opacity lg:duration-200 lg:ease-out"
             x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
             x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
             x-transition:leave="transition-transform duration-300 lg:transition-opacity lg:duration-200 lg:ease-in"
             x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
             x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
             class="absolute bottom-0 left-0 right-0 bg-[#fcf9f5] rounded-t-2xl lg:rounded-2xl shadow-2xl flex flex-col overflow-hidden lg:relative lg:bottom-auto lg:left-auto lg:right-auto lg:w-full lg:max-w-[480px]">
             
            <!-- Handle -->
            <div class="flex lg:hidden justify-center pt-3 pb-2" @click="bsVoucherOpen = false">
                <div class="w-10 h-1 bg-[#e5e2de] rounded-full cursor-pointer"></div>
            </div>
            
            <!-- Header -->
            <div class="px-5 pb-4 lg:pt-5 border-b border-[#e5e2de] flex justify-between items-center">
                <h3 class="font-serif text-lg font-bold text-[#1c1c1a]">Pilih Voucher</h3>
                <button type="button" @click="bsVoucherOpen = false" class="text-[#615e57] hover:text-[#1c1c1a] focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-5 overflow-y-auto overscroll-contain max-h-[60vh] lg:max-h-[500px]" data-lenis-prevent>
                @if (session()->has('voucher_error'))
                    <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-sm mb-4 text-xs font-sans">
                        {{ session('voucher_error') }}
                    </div>
                @endif
                <div class="flex gap-2 mb-6">
                    <input type="text" wire:model="voucherCode" placeholder="Masukkan Kode Voucher" class="flex-1 bg-transparent border border-[#e5e2de] px-4 h-12 font-mono text-[11px] uppercase focus:outline-none focus:border-[#064e3b]">
                    <button type="button" wire:click="applyVoucher" class="bg-[#1c1c1a] text-white px-4 font-mono text-[10px] font-bold tracking-widest uppercase hover:bg-black transition-colors">
                        <span wire:loading.remove wire:target="applyVoucher">TERAPKAN</span>
                        <span wire:loading wire:target="applyVoucher">...</span>
                    </button>
                </div>
                
                @if($availableVouchers && $availableVouchers->count() > 0)
                    <div class="flex flex-col gap-4 pb-12">
                        <div class="font-sans text-xs font-semibold text-[#1c1c1a] mb-2">Voucher Tersedia:</div>
                        @foreach($availableVouchers as $voucher)
                            <div 
                                @if($voucher->is_eligible) wire:click="selectVoucher('{{ $voucher->code }}')" @endif 
                                class="border p-4 flex flex-col gap-2 transition-colors 
                                {{ !$voucher->is_eligible ? 'border-[#e5e2de] bg-[#fcfcfc] opacity-75 cursor-not-allowed' : ($appliedVoucher && $appliedVoucher['code'] === $voucher->code ? 'border-[#064e3b] bg-[#f0ede9] cursor-pointer' : 'border-[#e5e2de] hover:border-[#064e3b] bg-white cursor-pointer') }}">
                                
                                <div class="flex justify-between items-start">
                                    <div class="{{ !$voucher->is_eligible ? 'opacity-80' : '' }}">
                                        <div class="font-mono text-[12px] font-bold tracking-widest uppercase {{ !$voucher->is_eligible ? 'text-[#615e57]' : 'text-[#064e3b]' }} mb-1">{{ $voucher->code }}</div>
                                        <div class="font-sans text-[13px] font-semibold {{ !$voucher->is_eligible ? 'text-[#615e57]' : 'text-[#1c1c1a]' }}">
                                            Diskon {{ $voucher->discount_type === 'percent' ? rtrim(rtrim(number_format($voucher->discount_amount, 2, ',', '.'), '0'), ',') . '%' : 'Rp' . number_format($voucher->discount_amount, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    @if($appliedVoucher && $appliedVoucher['code'] === $voucher->code)
                                        <span class="bg-[#064e3b] text-white text-[9px] font-mono uppercase tracking-widest px-2 py-1">TERPAKAI</span>
                                    @elseif(!$voucher->is_eligible)
                                        <span class="bg-[#e5e2de] text-[#615e57] text-[9px] font-mono uppercase tracking-widest px-2 py-1">TIDAK MEMENUHI SYARAT</span>
                                    @endif
                                </div>
                                <div class="font-sans text-[11px] text-[#615e57] mt-1">
                                    Min. belanja Rp{{ number_format($voucher->min_purchase, 0, ',', '.') }}
                                    @if($voucher->max_discount > 0)
                                        | Maks. diskon Rp{{ number_format($voucher->max_discount, 0, ',', '.') }}
                                    @endif
                                </div>
                                @if(!$voucher->is_eligible)
                                    <div class="font-sans text-[10px] text-[#ba1a1a] mt-1 italic">
                                        * {{ $voucher->ineligibility_reason }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-[#615e57] font-sans text-sm text-center py-12 flex flex-col items-center">
                        <svg class="w-12 h-12 mb-4 text-[#e5e2de]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        Belum ada voucher yang tersedia untuk Anda.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
