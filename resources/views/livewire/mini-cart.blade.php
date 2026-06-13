<div class="hidden md:block">
    <!-- Backdrop -->
    @if($isOpen)
    <div wire:click="close" class="fixed inset-0 bg-black/50 z-[150] transition-opacity backdrop-blur-sm"></div>
    @endif

    <!-- Sidebar -->
    <div class="fixed top-0 right-0 h-full w-[400px] bg-[#fcf9f5] shadow-2xl z-[160] transform transition-transform duration-300 ease-in-out {{ $isOpen ? 'translate-x-0' : 'translate-x-full' }} flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-[#e5e2de]">
            <h2 class="font-serif text-xl font-bold text-[#1c1c1a] tracking-tight uppercase">Keranjang ({{ $count }})</h2>
            <button wire:click="close" class="text-[#615e57] hover:text-[#1c1c1a] focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body / Items -->
        <div class="flex-1 overflow-y-auto px-6 py-6 scrollbar-none">
            @if(!$cart || $cart->items->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-12 h-12 mx-auto text-[#e5e2de] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="font-sans text-sm text-[#615e57] mb-6">Keranjang belanja Anda kosong.</p>
                    <button wire:click="close" class="bg-[#064e3b] text-white px-6 py-3 font-mono text-[10px] font-bold tracking-[0.2em] uppercase hover:bg-[#043326] transition-colors">MULAI BELANJA</button>
                </div>
            @else
                <div class="flex flex-col gap-6">
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
                        <div class="flex gap-4 relative">
                            <div class="w-20 h-24 bg-[#f0ede9] shrink-0">
                                <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex flex-col flex-1 pr-6">
                                <div class="flex justify-between items-start gap-2">
                                    <h3 class="font-serif text-sm font-semibold text-[#1c1c1a] leading-tight line-clamp-2">
                                        <a href="/product/{{ $item->product->slug }}" class="hover:text-[#615e57] transition-colors">{{ $item->product->name }}</a>
                                    </h3>
                                </div>
                                <div class="font-sans text-xs text-[#1c1c1a] mt-1">Rp{{ number_format($price, 0, ',', '.') }}</div>
                                
                                @if($item->variant)
                                <div class="font-mono text-[9px] uppercase tracking-[0.1em] text-[#615e57] mt-1 truncate">
                                    @foreach($item->variant->attributeOptions as $option)
                                        {{ $option->attribute->name }}: {{ $option->value }} @if(!$loop->last) <span class="mx-1">/</span> @endif
                                    @endforeach
                                </div>
                                @endif
                                <div class="mt-auto pt-2 flex items-center">
                                    <div class="border border-[#e5e2de] flex items-center h-[28px]">
                                        <button type="button" wire:click="decrementQuantity({{ $item->id }})" class="w-8 h-full flex items-center justify-center text-[#615e57] hover:text-[#1c1c1a] hover:bg-black/5 transition-colors focus:outline-none">
                                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                        </button>
                                        <div class="w-8 h-full flex items-center justify-center font-mono text-[10px] text-[#1c1c1a]">{{ $item->quantity }}</div>
                                        <button type="button" wire:click="incrementQuantity({{ $item->id }})" class="w-8 h-full flex items-center justify-center text-[#615e57] hover:text-[#1c1c1a] hover:bg-black/5 transition-colors focus:outline-none">
                                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button wire:click="removeItem({{ $item->id }})" class="absolute top-0 right-0 text-[#ba1a1a] hover:text-[#1c1c1a] focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Footer -->
        @if($cart && !$cart->items->isEmpty())
        <div class="border-t border-[#e5e2de] bg-[#f6f3ef] p-6">
            <div class="flex justify-between items-center mb-6">
                <span class="font-mono text-[12px] font-bold tracking-[0.1em] text-[#1c1c1a] uppercase">SUBTOTAL</span>
                <span class="font-serif text-[20px] font-semibold text-[#1c1c1a]">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <a href="/cart" wire:navigate.hover wire:click="close" class="flex items-center justify-center border border-[#1c1c1a] text-[#1c1c1a] py-3.5 px-2 font-mono text-[9px] font-bold tracking-[0.1em] uppercase text-center hover:bg-[#f2efe8] transition-colors leading-tight">LIHAT KERANJANG</a>
                <a href="/checkout" wire:navigate.hover wire:click="close" class="flex items-center justify-center bg-[#064e3b] text-white py-3.5 px-2 font-mono text-[9px] font-bold tracking-[0.1em] uppercase text-center hover:bg-[#043326] transition-colors leading-tight">LANJUT PEMBAYARAN</a>
            </div>
        </div>
        @endif
    </div>
</div>
