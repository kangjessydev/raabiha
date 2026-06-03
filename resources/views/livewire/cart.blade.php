<div>
    <x-global.mobile-subnav title="Keranjang" backUrl="/shop" />

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pb-0">
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
                        <a href="/shop" wire:navigate class="inline-block bg-[#064e3b] text-white px-8 py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase hover:bg-[#043326] transition-colors">MULAI BELANJA</a>
                    </div>
                @else
                    <!-- Main Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-12 lg:gap-16 items-start">
                        
                        <!-- Left Column: Product List -->
                        <div class="flex flex-col">
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

                                <div class="cart-item-row flex flex-row sm:flex-row gap-4 sm:gap-8 pb-6 sm:pb-12 mb-6 sm:mb-12 border-b border-[#e5e2de] sm:border-[rgba(23,24,24,0.1)]">
                                    <!-- Image -->
                                    <div class="w-[90px] h-[120px] sm:w-[280px] sm:h-[280px] bg-[#f0ede9] shrink-0">
                                        <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <!-- Info -->
                                    <div class="flex flex-col flex-1 py-0 sm:py-2 min-w-0">
                                        <div class="flex flex-col sm:flex-row justify-between items-start gap-1 sm:gap-4 w-full">
                                            <h3 class="font-serif text-base sm:text-[24px] font-semibold text-[#1c1c1a] leading-tight sm:normal-case line-clamp-2 sm:line-clamp-none pr-1">
                                                <a href="/product/{{ $item->product->slug }}" wire:navigate class="hover:text-[#615e57] transition-colors">{{ $item->product->name }}</a>
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

                        <!-- Right Column: Order Summary -->
                        <div class="bg-transparent lg:bg-[#f0ede9] p-0 lg:p-10 sticky top-[120px]">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-8 hidden lg:block">Ringkasan Pesanan</h2>
                            
                            <div class="flex flex-col gap-0 lg:gap-6 font-sans text-[14px] text-[#1c1c1a]">
                                <!-- Subtotal Row -->
                                <div class="flex justify-between py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] lg:text-[#1c1c1a] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal">Subtotal</span>
                                    <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            
                            <div class="h-px bg-[rgba(23,24,24,0.1)] my-8 hidden lg:block"></div>
                            
                            <!-- Promo Code -->
                            <div class="mb-10 py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                                <label class="font-mono text-[9px] font-semibold tracking-[0.2em] text-[#615e57] uppercase mb-4 hidden lg:block">Kode Promo / Voucher</label>
                                <button type="button" class="w-full lg:border lg:py-3 lg:px-4 flex justify-between items-center text-left transition-colors bg-transparent lg:border-[#1c1c1a] lg:hover:bg-[#f6f3ef] text-[#1c1c1a]">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.1em] font-bold flex items-center gap-2">PILIH VOUCHER</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                            
                            <!-- Total -->
                            <div class="flex justify-between items-end mb-8 py-6 lg:py-0">
                                <span class="font-mono text-[12px] lg:font-serif lg:text-[24px] font-bold lg:font-semibold tracking-[0.1em] lg:tracking-normal text-[#1c1c1a] uppercase"><span class="lg:hidden">ESTIMATED </span>TOTAL</span>
                                <span class="font-serif text-[24px] lg:text-[32px] font-semibold text-[#1c1c1a] leading-none">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            
                            <!-- Button -->
                            <a href="/checkout" wire:navigate class="flex justify-center items-center gap-2 w-full bg-[#064e3b] hover:bg-[#043326] text-white py-5 px-6 font-mono text-[10px] md:text-[12px] lg:text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors">
                                <span class="block lg:hidden">LANJUT KE PEMBAYARAN</span>
                                <span class="hidden lg:block">LANJUT KE PEMBAYARAN</span>
                                <svg class="block lg:hidden" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                            
                            <!-- Trust Badges -->
                            <div class="mt-8 hidden lg:flex flex-col items-center gap-4">
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
</div>
