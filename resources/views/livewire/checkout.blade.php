<div x-data="{ bsVoucherOpen: false }" @close-voucher-sheet.window="bsVoucherOpen = false">
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
                        
                        @if($guest_mode === null)
                        {{-- ===== CHOICE SCREEN: Guest or Login ===== --}}
                        <div class="flex flex-col items-center justify-center min-h-[60vh] py-20">
                            <h2 class="font-serif text-2xl md:text-3xl font-bold text-[#1c1c1a] tracking-tight mb-2 text-center">Sebelum Checkout...</h2>
                            <p class="text-sm text-[#615e57] font-sans mb-12 text-center max-w-sm">Pilih salah satu cara untuk melanjutkan pesanan kamu.</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full max-w-lg">
                                {{-- Guest Checkout --}}
                                <button type="button" wire:click="$set('guest_mode', true)"
                                    class="group flex flex-col items-start p-8 border border-[#e5e2de] hover:border-[#1c1c1a] bg-white hover:bg-[#f9f8f6] transition-all duration-200 text-left focus:outline-none">
                                    <div class="w-10 h-10 mb-4 flex items-center justify-center border border-[#e5e2de] group-hover:border-[#1c1c1a] transition-colors">
                                        <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] mb-1">Lanjut Sebagai Tamu</div>
                                    <div class="text-[11px] text-[#a09e99] font-sans leading-relaxed">Tanpa perlu login. Isi data secara manual.</div>
                                </button>
                                
                                {{-- Login / Register --}}
                                <a href="/login?redirect=/checkout" 
                                    class="group flex flex-col items-start p-8 border border-[#1c1c1a] bg-[#1c1c1a] hover:bg-[#333] transition-all duration-200 text-left">
                                    <div class="w-10 h-10 mb-4 flex items-center justify-center border border-white/20">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                    </div>
                                    <div class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white mb-1">Login / Daftar</div>
                                    <div class="text-[11px] text-white/50 font-sans leading-relaxed">Data & riwayat pesanan tersimpan otomatis.</div>
                                </a>
                            </div>
                        </div>
                    @else
                    {{-- ===== CHECKOUT FORM (Guest or Logged In) ===== --}}

                    @if(!auth()->check())
                        {{-- Subtle back link for guest --}}
                        <div class="flex items-center gap-2 text-[10px] font-mono text-[#615e57] uppercase tracking-widest mb-6">
                            <button type="button" wire:click="$set('guest_mode', null)" class="hover:text-[#1c1c1a] transition-colors flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Ganti pilihan
                            </button>
                            <span class="text-[#d1cec9]">|</span>
                            <span class="text-[#a09e99]">Checkout sebagai Tamu</span>
                        </div>
                    @endif



                        <!-- Section 1: Contact -->
                        <section class="border-b border-[#e5e2de] pb-10">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-1">1. Informasi Kontak</h2>
                            <p class="text-[11px] text-[#a09e99] font-sans mb-6">Isi minimal salah satu — Email <span class="text-[#d1cec9]">atau</span> No. WhatsApp.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Email Address</label>
                                    <input type="email" wire:model="email" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="your@email.com">
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">No. WhatsApp</label>
                                    <input type="tel" wire:model="phone" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="0812xxxxxx">
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </section>


                        <!-- Section 2: Shipping -->
                        <section class="border-b border-[#e5e2de] pb-10">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-6">2. Alamat Pengiriman</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Nama Depan *</label>
                                    <input type="text" wire:model="first_name" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors">
                                    @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Nama Belakang *</label>
                                    <input type="text" wire:model="last_name" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors">
                                    @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex flex-col gap-2 md:col-span-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Alamat Lengkap *</label>
                                    <textarea rows="3" wire:model="address" class="w-full bg-transparent border border-[#e5e2de] p-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="Nama Jalan, Gedung, No. Rumah"></textarea>
                                    @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="flex flex-col gap-2 relative z-20 md:col-span-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Kecamatan / Kota Tujuan *</label>
                                    <div x-data="{
                                        open: false,
                                        get options() {
                                            return $wire.destinationOptions;
                                        }
                                    }">
                                        <input type="text" 
                                               wire:model.live.debounce.500ms="searchLocation" 
                                               @focus="open = true"
                                               @click.outside="open = false"
                                               placeholder="Ketik minimal 3 huruf kecamatan/kota..." 
                                               class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" autocomplete="off">
                                               
                                        <div x-show="open && options.length > 0" class="absolute z-50 w-full mt-1 bg-[#fcf9f5] border border-[#e5e2de] shadow-xl max-h-60 flex flex-col">
                                            <ul class="overflow-y-auto flex-1">
                                                <template x-for="option in options" :key="option.id">
                                                    <li @click="$wire.selectDestination(option.id, option.label); open = false" 
                                                        class="px-4 py-3 cursor-pointer hover:bg-[#f0ede9] border-b border-[#e5e2de] last:border-0 font-sans text-[13px] text-[#1c1c1a]"
                                                        x-text="option.label"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                    @if($selectedDestinationLabel)
                                        <div class="mt-2 text-sm text-[#064e3b] font-semibold flex items-center justify-between bg-[#f0ede9] p-3 rounded">
                                            <span class="truncate">{{ $selectedDestinationLabel }}</span>
                                            <button type="button" @click="$wire.set('selectedDestinationId', ''); $wire.set('selectedDestinationLabel', ''); $wire.set('shipping_cost', 0)" class="text-red-500 hover:text-red-700 ml-2 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    @endif
                                    @error('selectedDestinationId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Kode Pos *</label>
                                    <input type="text" wire:model="postal_code" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors">
                                    @error('postal_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex flex-col gap-2 md:col-span-2">
                                    <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Catatan Pesanan (Opsional)</label>
                                    <textarea rows="2" wire:model="notes" class="w-full bg-transparent border border-[#e5e2de] p-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="Catatan untuk penjual atau kurir, misal: Tolong titip di pos satpam."></textarea>
                                </div>
                                
                                @if(auth()->check())
                                <div class="flex items-start gap-3 md:col-span-2 mt-2 p-4 bg-[#fcf9f5] border border-[#e5e2de]">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" id="save_address" wire:model="save_address" class="w-4 h-4 text-[#064e3b] bg-transparent border-gray-300 rounded focus:ring-[#064e3b] cursor-pointer">
                                    </div>
                                    <div class="flex flex-col">
                                        <label for="save_address" class="font-sans text-sm font-semibold text-[#1c1c1a] cursor-pointer">Simpan Alamat Ini</label>
                                        <p class="text-xs text-[#615e57] mt-0.5">Alamat akan otomatis terisi di pesanan Anda berikutnya.</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </section>

                        <!-- Section 3: Shipping Method -->
                        <section class="border-b border-[#e5e2de] pb-10">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-6">3. Metode Pengiriman</h2>
                            <div class="flex flex-col gap-4">
                                @forelse($shippingRates as $rate)
                                <label class="flex justify-between items-center border p-4 cursor-pointer transition-colors {{ $shipping_method === $rate['id'] ? 'border-[#064e3b] bg-[#f0ede9]' : 'border-[#e5e2de] hover:border-[#1c1c1a]' }}">
                                    <div class="flex items-center gap-4">
                                        <input type="radio" wire:model.live="shipping_method" value="{{ $rate['id'] }}" class="w-4 h-4 text-[#064e3b] focus:ring-[#064e3b] border-gray-300">
                                        @if(!empty($rate['logo']))
                                            <img src="{{ Storage::url($rate['logo']) }}" alt="{{ $rate['courier_name'] }}" class="h-6 object-contain hidden md:block">
                                        @endif
                                        <div>
                                            <span class="block font-serif text-base text-[#1c1c1a]">{{ $rate['courier_name'] }} {{ $rate['service_name'] }}</span>
                                            <span class="block font-sans text-xs text-[#615e57] mt-1">Estimasi {{ $rate['duration'] }}</span>
                                        </div>
                                    </div>
                                    <span class="font-sans text-sm font-semibold text-[#1c1c1a]">Rp {{ number_format($rate['price'], 0, ',', '.') }}</span>
                                </label>
                                @empty
                                <div class="text-sm text-[#615e57] p-4 bg-[#f0ede9] rounded text-center">
                                    Silakan lengkapi alamat pengiriman (Provinsi, Kota, Kecamatan) terlebih dahulu untuk melihat tarif ongkos kirim.
                                </div>
                                @endforelse
                            </div>
                        </section>

                        <!-- Section 4: Payment -->
                        <section class="pb-6">
                            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-6">4. Metode Pembayaran</h2>
                            <div class="flex flex-col gap-4">
                                @forelse($paymentMethods as $method)
                                <label class="flex items-center gap-4 border p-4 cursor-pointer transition-colors {{ $payment_method === $method->code ? 'border-[#064e3b] bg-[#f0ede9]' : 'border-[#e5e2de] hover:border-[#1c1c1a]' }}">
                                    <input type="radio" wire:model.live="payment_method" value="{{ $method->code }}" class="w-4 h-4 text-[#064e3b] focus:ring-[#064e3b] border-gray-300">
                                    @if($method->logo)
                                        <img src="{{ Storage::url($method->logo) }}" alt="{{ $method->name }}" class="h-6 object-contain">
                                    @endif
                                    <span class="font-serif text-base text-[#1c1c1a]">{{ $method->name }}</span>
                                </label>
                                @empty
                                <div class="text-sm text-[#615e57]">Belum ada metode pembayaran yang tersedia.</div>
                                @endforelse
                            </div>
                         </section>

                    @endif {{-- end @else (form shown) --}}

                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="bg-transparent lg:bg-[#f0ede9] p-0 lg:p-10 sticky top-[120px]">
                        <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-8 hidden lg:block">Ringkasan Pesanan</h2>
                        
                        @if (session()->has('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        @endif

                        <!-- Skeleton Loading -->
                        <div wire:loading wire:target="shipping_method, payment_method" class="w-full">
                            <div class="animate-pulse flex flex-col gap-6">
                                <div class="flex gap-4 items-center">
                                    <div class="w-16 h-16 bg-[#e5e2de]"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-3 bg-[#e5e2de] w-3/4"></div>
                                        <div class="h-2 bg-[#e5e2de] w-1/2"></div>
                                    </div>
                                    <div class="w-16 h-3 bg-[#e5e2de]"></div>
                                </div>
                                <div class="h-px bg-[#e5e2de] w-full"></div>
                                <div class="flex justify-between">
                                    <div class="w-1/4 h-3 bg-[#e5e2de]"></div>
                                    <div class="w-1/4 h-3 bg-[#e5e2de]"></div>
                                </div>
                                <div class="flex justify-between">
                                    <div class="w-1/4 h-3 bg-[#e5e2de]"></div>
                                    <div class="w-1/4 h-3 bg-[#e5e2de]"></div>
                                </div>
                                <div class="flex justify-between items-end mt-4">
                                    <div class="w-1/4 h-4 bg-[#e5e2de]"></div>
                                    <div class="w-1/3 h-6 bg-[#e5e2de]"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Actual Content -->
                        <div wire:loading.remove wire:target="shipping_method, payment_method" class="w-full">
                            <!-- Order Items Mini -->
                            <div class="flex flex-col gap-4 mb-8">
                            @if($this->checkoutItems && $this->checkoutItems->count() > 0)
                                @foreach($this->checkoutItems as $item)
                                    @php
                                        $price = $item->variant && $item->variant->price !== null ? $item->variant->price : $item->product->price;
                                        $imageId = $item->product->images && count($item->product->images) > 0 ? $item->product->images[0] : null;
                                        $image = asset('assets/images/placeholder.png');
                                        if ($imageId) {
                                            $media = \Awcodes\Curator\Models\Media::find($imageId);
                                            if ($media) $image = $media->url;
                                        }
                                        
                                        $variantText = '';
                                        if ($item->variant) {
                                            $options = [];
                                            foreach ($item->variant->attributeOptions as $opt) {
                                                $options[] = $opt->value;
                                            }
                                            $variantText = implode(' / ', $options);
                                        }
                                    @endphp
                                    <div class="flex gap-4 items-center">
                                        <div class="w-16 h-16 bg-[#e5e2de] shrink-0">
                                            <img src="{{ $image }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-serif text-sm text-[#1c1c1a]">{{ $item->product->name }}</h4>
                                            <p class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">
                                                {{ $variantText ? $variantText . ' | ' : '' }}Qty: {{ $item->quantity }}
                                            </p>
                                        </div>
                                        <div class="font-sans text-sm text-[#1c1c1a]">Rp{{ number_format($price * $item->quantity, 0, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-sm text-[#615e57]">Keranjang Anda kosong.</div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-0 lg:gap-4 font-sans text-[14px] text-[#1c1c1a]">
                            <div class="flex justify-between py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                                <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] lg:text-[#1c1c1a] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal">Subtotal</span>
                                <span>Rp{{ number_format($this->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if($this->reseller_discount > 0)
                            <div class="flex justify-between py-4 border-b border-[#e5e2de] lg:border-none lg:py-0 text-[#064e3b]">
                                <span class="font-mono text-[10px] uppercase tracking-[0.1em] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal">Diskon Reseller</span>
                                <span>-Rp{{ number_format($this->reseller_discount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-start py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] lg:text-[#1c1c1a] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal">Pengiriman</span>
                                    @php
                                        $selectedRate = collect($this->shippingRates)->firstWhere('id', $this->shipping_method);
                                    @endphp
                                    @if($selectedRate)
                                        <span class="font-mono text-[9px] uppercase tracking-[0.08em] text-[#615e57]">{{ $selectedRate['courier_name'] ?? '' }} {{ $selectedRate['service_name'] ?? '' }} &middot; est. {{ $selectedRate['duration'] ?? '' }}</span>
                                    @elseif(!$this->selectedDestinationId)
                                        <span class="font-mono text-[9px] uppercase tracking-[0.08em] text-[#999]">Pilih destinasi dulu</span>
                                    @endif
                                </div>
                                <span>{{ $this->shipping_cost > 0 ? 'Rp'.number_format($this->shipping_cost, 0, ',', '.') : 'Rp0' }}</span>
                            </div>
                            @if($this->discountAmount > 0)
                            <div class="flex justify-between py-4 border-b border-[#e5e2de] lg:border-none lg:py-0 text-[#064e3b]">
                                <span class="font-mono text-[10px] uppercase tracking-[0.1em] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal">Voucher ({{ $appliedVoucher['code'] ?? '' }})</span>
                                <span>-Rp{{ number_format($this->discountAmount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            @if($this->payment_method)
                            @php
                                $selectedPayment = \App\Models\PaymentMethod::where('code', $this->payment_method)->first();
                            @endphp
                            @if($selectedPayment)
                            <div class="flex justify-between items-center py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] lg:text-[#1c1c1a] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal">Metode Bayar</span>
                                    <span class="font-mono text-[9px] uppercase tracking-[0.08em] text-[#615e57] flex items-center gap-1.5">
                                        @if($selectedPayment->logo)
                                            <img src="{{ Storage::url($selectedPayment->logo) }}" alt="{{ $selectedPayment->name }}" class="h-3 object-contain">
                                        @endif
                                        {{ $selectedPayment->name }}
                                    </span>
                                </div>
                                @php $payFee = $this->paymentFee; @endphp
                                <span class="{{ $payFee > 0 ? 'text-[#1c1c1a]' : 'text-[#999]' }}">
                                    {{ $payFee > 0 ? 'Rp'.number_format($payFee, 0, ',', '.') : 'Gratis' }}
                                </span>
                            </div>
                            @endif
                            @endif
                        </div>
                        
                        <div class="h-px bg-[rgba(23,24,24,0.1)] my-6 hidden lg:block"></div>
                        
                        <!-- Promo Code -->
                        <div class="mb-6 lg:mb-10 py-4 lg:py-0">
                            <label class="font-mono text-[9px] font-semibold tracking-[0.2em] text-[#615e57] uppercase mb-4 hidden lg:block">Kode Promo / Voucher</label>
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
                        
                        <div class="h-px bg-[rgba(23,24,24,0.1)] my-6 hidden lg:block"></div>
                        
                        <!-- Total -->
                        <div class="flex justify-between items-end mb-8 py-6 lg:py-0">
                            <span class="font-mono text-[12px] lg:font-serif lg:text-[24px] font-bold lg:font-semibold tracking-[0.1em] lg:tracking-normal text-[#1c1c1a] uppercase">TOTAL</span>
                            <span class="font-serif text-[24px] lg:text-[32px] font-semibold text-[#1c1c1a] leading-none">Rp{{ number_format($this->total, 0, ',', '.') }}</span>
                        </div>
                        
                        <!-- Button -->
                        @if($errors->any())
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-600 text-sm font-sans">
                                Terdapat isian form yang belum lengkap atau salah. Silakan periksa kembali formulir di atas.
                            </div>
                        @endif
                        <button type="button" wire:click="processCheckout" class="flex justify-center items-center gap-2 w-full bg-[#064e3b] hover:bg-[#043326] text-white py-5 px-6 font-mono text-[10px] md:text-[12px] lg:text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors">
                            <span class="block" wire:loading.remove wire:target="processCheckout">BAYAR SEKARANG</span>
                            <span class="block" wire:loading wire:target="processCheckout">MEMPROSES...</span>
                        </button>
                        
                        <!-- Trust Badges -->
                        <div class="mt-8 flex flex-col items-center gap-4">
                            <div class="flex items-center gap-2 text-[#615e57]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span class="font-mono text-[9px] uppercase tracking-[0.1em]">Pembayaran Aman Terjamin</span>
                            </div>
                        </div>

                        </div> <!-- End wire:loading.remove -->
                    </div>
                </div>
            </div>
        </main>
    </div>
    
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
             class="absolute bottom-0 left-0 right-0 bg-[#fcf9f5] rounded-t-2xl lg:rounded-2xl shadow-2xl flex flex-col max-h-[80vh] overflow-hidden lg:relative lg:bottom-auto lg:left-auto lg:right-auto lg:w-full lg:max-w-[480px]">
             
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
            <div class="p-5 overflow-y-auto flex-1 min-h-0">
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
