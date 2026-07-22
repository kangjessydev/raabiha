<div x-data="posSystem()" class="h-screen w-full flex flex-col md:flex-row overflow-hidden bg-gray-50/50 relative">

    <!-- Notifications Toast -->
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
        <template x-for="(toast, index) in toasts" :key="toast.id">
            <div x-show="true" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="glass px-6 py-4 rounded-xl flex items-center gap-3 shadow-lg"
                 :class="toast.type === 'error' ? 'border-red-400 bg-red-50/80' : 'border-green-400 bg-green-50/80'">
                <div x-text="toast.message" class="font-medium" :class="toast.type === 'error' ? 'text-red-800' : 'text-green-800'"></div>
            </div>
        </template>
    </div>

    <!-- Cek Session -->
    @if(!$activeSession)
        <!-- Overlay Buka Shift -->
        <div class="absolute inset-0 z-40 flex items-center justify-center bg-gray-900/40 backdrop-blur-sm">
            <div class="glass w-full max-w-md rounded-2xl p-8 relative shadow-2xl">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Buka Shift Kasir</h2>
                    <p class="text-gray-500 text-sm mt-1">Masukkan modal awal (uang kas di laci) untuk memulai transaksi.</p>
                </div>
                
                <form wire:submit.prevent="openSession" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modal Awal (Rp)</label>
                        <input type="number" wire:model="openingCash" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-lg py-3 px-4" placeholder="0">
                        @error('openingCash') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-brand-500/30 transition-all active:scale-95">
                        Mulai Sesi
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- MAIN POS AREA -->
        
        <!-- Left: Products Area -->
        <div class="flex-1 flex flex-col h-full bg-transparent">
            <!-- Header/Search -->
            <div class="glass border-b border-gray-200/50 p-4 sticky top-0 z-10 flex items-center justify-between gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-3 border-none bg-gray-100/50 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all text-sm" placeholder="Cari Produk atau Barcode / SKU...">
                </div>
                
                <div class="flex items-center gap-3">


                    <div class="flex items-center bg-white rounded-xl p-1 shadow-sm border border-gray-200">
                        <select x-model="printerType" class="text-xs border-none bg-transparent focus:ring-0 py-1 pl-2 pr-6">
                            <option value="bluetooth">Bluetooth</option>
                            <option value="serial">USB/Serial</option>
                        </select>
                        <button @click="connectPrinter()" :class="printerConnected ? 'bg-green-100 text-green-700' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span x-text="printerConnected ? 'Tersambung' : 'Hubungkan'"></span>
                        </button>
                    </div>

                    <button @click="showCloseSession = true" class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl font-medium text-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Tutup Shift
                    </button>
                    <a href="{{ url('/admin') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-medium text-sm transition-colors">
                        Kembali ke Admin
                    </a>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @forelse($products as $product)
                        @php
                            // Cek jika produk punya varian
                            $hasVariants = $product->variants->count() > 0;
                            $computedStock = $product->computed_stock ?? ($hasVariants ? $product->variants->sum('stock') : $product->stock);
                            $isOutOfStock = $computedStock <= 0;
                            
                            // Harga display
                            $hasPromo = false;
                            $promoPrice = null;
                            $originalPrice = null;
                            $priceDisplay = '';
                            
                            if ($product->pos_discount_price) {
                                $hasPromo = true;
                                $promoPrice = $product->pos_discount_price;
                                $originalPrice = $product->pos_price ?: $product->price;
                                $priceDisplay = 'Rp ' . number_format($promoPrice, 0, ',', '.');
                            } elseif ($product->pos_price) {
                                $originalPrice = $product->pos_price;
                                $priceDisplay = 'Rp ' . number_format($originalPrice, 0, ',', '.');
                            } elseif ($hasVariants) {
                                $minPrice = $product->variants->min('price');
                                $maxPrice = $product->variants->max('price');
                                if ($minPrice != $maxPrice) {
                                    $priceDisplay = 'Rp ' . number_format($minPrice, 0, ',', '.') . ' - Rp ' . number_format($maxPrice, 0, ',', '.');
                                } else {
                                    $priceDisplay = 'Rp ' . number_format($minPrice, 0, ',', '.');
                                }
                                $originalPrice = $minPrice;
                            } else {
                                if ($product->discount_price) {
                                    $hasPromo = true;
                                    $promoPrice = $product->discount_price;
                                    $originalPrice = $product->price;
                                    $priceDisplay = 'Rp ' . number_format($promoPrice, 0, ',', '.');
                                } else {
                                    $originalPrice = $product->price;
                                    $priceDisplay = 'Rp ' . number_format($originalPrice, 0, ',', '.');
                                }
                            }
                            $priceForJs = $hasPromo ? $promoPrice : $originalPrice;
                            
                            $imageUrl = asset('assets/images/placeholder.webp');
                            if (!empty($product->images)) {
                                if (is_numeric($product->images[0])) {
                                    $media = \Awcodes\Curator\Models\Media::find($product->images[0]);
                                    if ($media) $imageUrl = Storage::url($media->path);
                                } else {
                                    if (Storage::disk('public')->exists($product->images[0])) {
                                        $imageUrl = asset('storage/' . $product->images[0]);
                                    } else {
                                        $imageUrl = asset($product->images[0]);
                                    }
                                }
                            }
                            $image = $imageUrl;
                        @endphp
                        
                        <div class="glass bg-white {{ $isOutOfStock ? 'opacity-50 cursor-not-allowed grayscale-[30%]' : 'hover:shadow-2xl hover:-translate-y-1 cursor-pointer group' }} transition-all duration-300 rounded-2xl overflow-hidden border border-gray-100 relative"
                             @if(!$isOutOfStock)
                             x-data="{ variantsData: {{ $hasVariants ? \Illuminate\Support\Js::from($product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => $product->pos_discount_price ?: ($product->pos_price ?: ($v->price ?: $product->price)), 'stock' => $v->stock])) : 'null' }} }"
                             @click="addProduct({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $priceForJs }}, {{ $hasVariants ? 'true' : 'false' }}, variantsData)"
                             @endif
                             >
                             
                             @if($isOutOfStock)
                                <div class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none">
                                    <span class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg transform -rotate-12">HABIS</span>
                                </div>
                             @endif

                            <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                <img src="{{ $image }}" alt="{{ $product->name }}" class="object-cover w-full h-full {{ !$isOutOfStock ? 'group-hover:scale-105' : '' }} transition-transform duration-500">
                                @if(isset($product->is_best_seller) && $product->is_best_seller)
                                    <span class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-[10px] px-2 py-1 rounded-md font-bold shadow-sm flex items-center gap-1 z-10">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        Terlaris
                                    </span>
                                @endif
                                @if($hasVariants)
                                    <span class="absolute top-2 right-2 bg-gray-900/80 backdrop-blur text-white text-[10px] px-2 py-1 rounded-md font-medium z-10">{{ $product->variants->count() }} Varian</span>
                                @endif
                                <span class="absolute bottom-2 left-2 {{ $isOutOfStock ? 'bg-red-500' : 'bg-brand-500' }} text-white text-[10px] px-2 py-1 rounded-md font-medium shadow-sm z-10">Stok: {{ $computedStock }}</span>
                            </div>
                            <div class="p-3">
                                <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 leading-tight {{ !$isOutOfStock ? 'group-hover:text-brand-600' : '' }} transition-colors">{{ $product->name }}</h3>
                                <div class="mt-2 flex flex-col">
                                    @if($hasPromo)
                                        <span class="text-[10px] text-gray-400 line-through">Rp {{ number_format($originalPrice, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="text-brand-600 font-bold leading-tight">{{ $priceDisplay }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <p class="text-lg">Tidak ada produk ditemukan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Cart Sidebar -->
        <div class="w-full md:w-[400px] lg:w-[450px] bg-white border-l border-gray-200/50 shadow-2xl flex flex-col relative z-20">
            <!-- Cart Header -->
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Keranjang
                </h2>
                <div class="flex items-center gap-1">
                    <button @click="showHoldModal = true" class="relative text-brand-600 hover:bg-brand-50 px-3 py-2 rounded-lg transition-colors flex items-center gap-1" title="Lihat Antrean">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-sm font-bold hidden md:inline">Antrean</span>
                        <span x-show="heldCarts.length > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full shadow-sm" x-text="heldCarts.length"></span>
                    </button>
                    <button @click="clearCart()" x-show="cart.length > 0" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors" title="Kosongkan Keranjang">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-2 space-y-2 bg-gray-50/30">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 p-8 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <p class="font-medium text-gray-500">Keranjang masih kosong</p>
                        <p class="text-sm mt-1">Pilih produk di samping untuk memulai transaksi.</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="index">
                    <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col gap-2 relative group hover:border-brand-200 transition-colors">
                        <div class="flex justify-between items-start pr-6">
                            <h4 class="font-semibold text-sm text-gray-800 leading-tight" x-text="item.name"></h4>
                            <div class="font-bold text-brand-600 text-sm" x-text="'Rp ' + formatMoney(item.price * item.quantity)"></div>
                        </div>
                        
                        <div class="flex justify-between items-center mt-1">
                            <div class="text-xs text-gray-500" x-text="'Rp ' + formatMoney(item.price) + ' / item'"></div>
                            <!-- Qty Controls -->
                            <div class="flex items-center bg-gray-100 rounded-lg p-1">
                                <button @click="updateQty(index, -1)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-brand-600 active:scale-95 transition-all">-</button>
                                <input type="number" x-model.number="item.quantity" class="w-10 text-center bg-transparent border-none focus:ring-0 text-sm font-semibold p-0 mx-1" min="1">
                                <button @click="updateQty(index, 1)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-brand-600 active:scale-95 transition-all">+</button>
                            </div>
                        </div>

                        <!-- Hapus Item -->
                        <button @click="removeItem(index)" class="absolute top-2 right-2 text-gray-300 hover:text-red-500 bg-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Cart Footer (Totals & Checkout) -->
            <div class="bg-white border-t border-gray-100 p-4 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)]">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-semibold text-gray-700" x-text="'Rp ' + formatMoney(subtotal)"></span>
                </div>
                <div class="flex justify-between items-center mb-4 cursor-pointer group" @click="showDiscountModal = true">
                    <span class="text-gray-500 border-b border-dashed border-gray-300 group-hover:border-brand-500 transition-colors">Diskon Manual</span>
                    <span class="font-semibold text-red-500" x-text="'- Rp ' + formatMoney(discount)"></span>
                </div>
                <div class="flex justify-between items-center mb-6 pt-4 border-t border-gray-100">
                    <span class="text-lg font-bold text-gray-800">Total</span>
                    <span class="text-2xl font-black text-brand-600" x-text="'Rp ' + formatMoney(grandTotal)"></span>
                </div>
                
                <div class="flex gap-2">
                    <button 
                        @click="holdCart()"
                        :disabled="cart.length === 0"
                        class="px-4 py-4 rounded-xl font-bold text-gray-700 shadow-sm transition-all active:scale-95 flex items-center justify-center border border-gray-200"
                        :class="cart.length > 0 ? 'bg-white hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed border-transparent'"
                        title="Masukkan ke Antrean">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </button>
                    
                    <button 
                        @click="openCheckoutModal()"
                        :disabled="cart.length === 0"
                        class="flex-1 py-4 rounded-xl font-bold text-lg shadow-xl transition-all active:scale-95 flex justify-center items-center gap-2"
                        :class="cart.length > 0 ? 'bg-brand-600 hover:bg-brand-700 text-white shadow-brand-500/30' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        PROSES PEMBAYARAN
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Pilih Varian Produk -->
        <div x-show="showVariantModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showVariantModal = false" class="glass w-full max-w-lg rounded-2xl p-6 relative">
                 <h3 class="text-xl font-bold mb-1" x-text="currentProductForVariant ? currentProductForVariant.name : 'Pilih Varian'"></h3>
                 <p class="text-gray-500 mb-4 text-sm">Pilih salah satu varian di bawah ini:</p>
                 
                 <div class="space-y-2 mb-6 max-h-[50vh] overflow-y-auto">
                     <template x-for="variant in currentVariants" :key="variant.id">
                         <button @click="addVariantToCart(variant.id, variant.name, variant.price)" 
                                 :disabled="variant.stock <= 0"
                                 class="w-full flex justify-between items-center p-4 border rounded-xl hover:bg-brand-50 hover:border-brand-300 transition-colors text-left"
                                 :class="variant.stock <= 0 ? 'opacity-50 cursor-not-allowed bg-gray-50' : 'bg-white'">
                             <div>
                                 <div class="font-bold text-gray-800" x-text="variant.name"></div>
                                 <div class="text-sm" :class="variant.stock > 0 ? 'text-brand-600' : 'text-red-500'" x-text="variant.stock > 0 ? 'Stok: ' + variant.stock : 'Habis'"></div>
                             </div>
                             <div class="font-black text-gray-800" x-text="'Rp ' + formatMoney(variant.price)"></div>
                         </button>
                     </template>
                 </div>

                 <button @click="showVariantModal = false" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-xl transition-all">Batal</button>
            </div>
        </div>

        <!-- MODAL: Checkout / Payment -->
        <div x-show="showCheckoutModal" 
             x-transition.opacity.duration.300ms
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            
            <div @click.away="if(!isProcessing) showCheckoutModal = false" 
                 x-show="showCheckoutModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-2xl rounded-3xl overflow-hidden flex flex-col shadow-2xl">
                
                <!-- Modal Header -->
                <div class="bg-white/50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Pembayaran</h2>
                    <button @click="showCheckoutModal = false" :disabled="isProcessing" class="text-gray-400 hover:text-gray-600 p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 flex flex-col md:flex-row gap-6 bg-white/80">
                    <!-- Detail Tagihan -->
                    <div class="flex-1 space-y-4">
                        <div class="bg-brand-50 rounded-2xl p-6 border border-brand-100 text-center">
                            <p class="text-sm font-medium text-brand-800 mb-1">Total Tagihan</p>
                            <p class="text-4xl font-black text-brand-600" x-text="'Rp ' + formatMoney(grandTotal)"></p>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Uang Diterima (Rp)</label>
                                <input type="number" x-model.number="cashPaid" @input="calculateChange" class="w-full text-2xl font-bold rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 p-4 bg-gray-50 text-right" placeholder="0">
                            </div>
                            
                            <!-- Preset Uang Pas -->
                            <div class="grid grid-cols-3 gap-2">
                                <button @click="setCashPaid(grandTotal)" class="py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-semibold transition-colors">Uang Pas</button>
                                <button @click="setCashPaid(Math.ceil(grandTotal/50000)*50000)" class="py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-semibold transition-colors" x-text="formatMoney(Math.ceil(grandTotal/50000)*50000)"></button>
                                <button @click="setCashPaid(Math.ceil(grandTotal/100000)*100000)" class="py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-semibold transition-colors" x-text="formatMoney(Math.ceil(grandTotal/100000)*100000)"></button>
                            </div>

                            <div class="bg-gray-100 rounded-xl p-4 flex justify-between items-center mt-2 border border-gray-200">
                                <span class="font-medium text-gray-600">Kembalian</span>
                                <span class="text-2xl font-bold" :class="cashChange >= 0 ? 'text-gray-800' : 'text-red-500'" x-text="'Rp ' + formatMoney(Math.max(0, cashChange))"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Pelanggan (Opsional) -->
                    <div class="w-full md:w-64 space-y-4 border-l border-gray-100 pl-0 md:pl-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelanggan (Opsional)</label>
                            <input type="text" x-model="customerName" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp (Opsional)</label>
                            <input type="text" x-model="customerPhone" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="08...">
                        </div>
                        
                        <div class="pt-4 mt-4 border-t border-gray-100">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                            <div class="space-y-2">
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all" :class="paymentMethod === 'cash' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-gray-200 hover:bg-gray-50'">
                                    <input type="radio" x-model="paymentMethod" value="cash" class="text-brand-600 focus:ring-brand-500">
                                    <span class="ml-2 font-medium">Tunai (Cash)</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all" :class="paymentMethod === 'qris' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-gray-200 hover:bg-gray-50'">
                                    <input type="radio" x-model="paymentMethod" value="qris" class="text-brand-600 focus:ring-brand-500">
                                    <span class="ml-2 font-medium">QRIS / Transfer</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button @click="showCheckoutModal = false" :disabled="isProcessing" class="px-6 py-3 bg-white border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors">Batal</button>
                    <button @click="submitOrder()" 
                            :disabled="isProcessing || (paymentMethod === 'cash' && cashPaid < grandTotal)" 
                            class="px-8 py-3 bg-brand-600 text-white font-bold rounded-xl shadow-lg hover:bg-brand-700 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isProcessing">Bayar & Selesaikan</span>
                        <span x-show="isProcessing" class="flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Tutup Shift -->
        <div x-show="showCloseSession" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showCloseSession = false" class="glass w-full max-w-md rounded-2xl p-8 relative shadow-2xl">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Tutup Shift Kasir</h2>
                <p class="text-gray-500 text-sm mb-6">Hitung uang fisik di laci kasir dan masukkan di bawah ini.</p>
                
                <form wire:submit.prevent="closeSession" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Uang Fisik (Rp)</label>
                        <input type="number" wire:model="actualEndingCash" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-lg py-3 px-4" placeholder="0" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea wire:model="sessionNotes" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm p-3" rows="3" placeholder="Misal: Ada pengeluaran beli lakban Rp 10.000"></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showCloseSession = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl transition-all">Batal</button>
                        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-red-500/30 transition-all">Tutup Shift</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Hold Carts -->
        <div x-show="showHoldModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showHoldModal = false" class="glass w-full max-w-2xl rounded-2xl p-6 relative max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Daftar Antrean Pesanan
                    </h3>
                    <button @click="showHoldModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="overflow-y-auto flex-1 space-y-3">
                    <template x-if="heldCarts.length === 0">
                        <div class="text-center py-10 text-gray-400">
                            <p>Tidak ada antrean pesanan.</p>
                        </div>
                    </template>
                    
                    <template x-for="hold in heldCarts" :key="hold.id">
                        <div class="bg-white border border-gray-100 shadow-sm p-4 rounded-xl flex items-center justify-between gap-4">
                            <div>
                                <div class="font-bold text-gray-800" x-text="hold.name"></div>
                                <div class="text-sm text-gray-500" x-text="hold.cart.length + ' item | Antre sejak ' + hold.time"></div>
                                <div class="font-semibold text-brand-600 mt-1" x-text="'Rp ' + formatMoney(hold.total)"></div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="deleteHeldCart(hold.id)" class="px-4 py-2 bg-red-50 text-red-600 font-semibold rounded-lg hover:bg-red-100 transition-colors">Hapus</button>
                                <button @click="resumeCart(hold.id)" class="px-4 py-2 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">Lanjutkan</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- MODAL: Konfirmasi -->
        <div x-show="showConfirmModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showConfirmModal = false" class="glass w-full max-w-sm rounded-2xl p-6 relative shadow-2xl text-center">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2" x-text="confirmTitle"></h3>
                <p class="text-gray-500 text-sm mb-6" x-text="confirmMessage"></p>
                
                <div class="flex gap-3">
                    <button @click="showConfirmModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="executeConfirm()" class="flex-1 px-4 py-3 bg-brand-600 text-white font-bold rounded-xl hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
        <!-- MODAL: Input Custom -->
        <div x-show="showInputModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showInputModal = false" class="glass w-full max-w-sm rounded-2xl p-6 relative shadow-2xl">
                <h3 class="text-xl font-bold text-gray-800 mb-2" x-text="inputTitle"></h3>
                <p class="text-gray-500 text-sm mb-4" x-text="inputMessage"></p>
                
                <input type="text" id="alpineInputModalField" x-model="inputValue" @keydown.enter="executeInput()" :placeholder="inputPlaceholder" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm py-3 px-4 mb-6">
                
                <div class="flex gap-3">
                    <button @click="showInputModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="executeInput()" class="flex-1 px-4 py-3 bg-brand-600 text-white font-bold rounded-xl hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">Simpan</button>
                </div>
            </div>
        </div>

    @endif

    <!-- Alpine.js Script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posSystem', () => ({
                cart: [],
                discount: 0,
                
                // Modals
                showVariantModal: false,
                showCheckoutModal: false,
                showCloseSession: false,
                showDiscountModal: false,
                
                // Checkout State
                cashPaid: 0,
                cashChange: 0,
                paymentMethod: 'cash',
                customerName: '',
                customerPhone: '',
                isProcessing: false,
                
                // Toasts
                toasts: [],
                toastId: 0,

                currentProductForVariant: null,
                currentVariants: [],
                
                // Printer State
                printerConnected: false,
                printerDevice: null,
                printerType: 'bluetooth',
                printerCharacteristic: null,
                printerPort: null,
                heldCarts: [],
                showHoldModal: false,

                // Confirmation Modal State
                showConfirmModal: false,
                confirmTitle: '',
                confirmMessage: '',
                confirmAction: null,
                
                askConfirm(title, message, callback) {
                    this.confirmTitle = title;
                    this.confirmMessage = message;
                    this.confirmAction = callback;
                    this.showConfirmModal = true;
                },
                
                executeConfirm() {
                    if (this.confirmAction) this.confirmAction();
                    this.showConfirmModal = false;
                },

                // Input Modal State
                showInputModal: false,
                inputTitle: '',
                inputMessage: '',
                inputValue: '',
                inputPlaceholder: '',
                inputAction: null,
                
                askInput(title, message, placeholder, defaultValue, callback) {
                    this.inputTitle = title;
                    this.inputMessage = message;
                    this.inputPlaceholder = placeholder;
                    this.inputValue = defaultValue || '';
                    this.inputAction = callback;
                    this.showInputModal = true;
                    // Focus input after modal renders
                    setTimeout(() => {
                        const el = document.getElementById('alpineInputModalField');
                        if(el) el.focus();
                    }, 50);
                },
                
                executeInput() {
                    if (this.inputAction) this.inputAction(this.inputValue);
                    this.showInputModal = false;
                },

                init() {
                    // Load dari localStorage
                    const storedHold = localStorage.getItem('pos_held_carts');
                    if (storedHold) {
                        try {
                            this.heldCarts = JSON.parse(storedHold);
                        } catch(e) {}
                    }

                    // Menerima event dari Livewire
                    window.addEventListener('session-opened', () => { this.showToast('Sesi kasir berhasil dibuka', 'success'); });
                    window.addEventListener('session-closed', () => { this.showCloseSession = false; this.showToast('Sesi kasir berhasil ditutup', 'success'); });
                    window.addEventListener('notify', (e) => { this.showToast(e.detail[0].message, e.detail[0].type); this.isProcessing = false; });
                    window.addEventListener('checkout-success', (e) => {
                        this.isProcessing = false;
                        this.showCheckoutModal = false;
                        this.clearCart(true);
                        this.showToast('Pembayaran Berhasil! Kembalian: Rp ' + this.formatMoney(e.detail[0].cash_change), 'success');
                    });
                    window.addEventListener('print-receipt', (e) => {
                        this.printBase64(e.detail[0].base64);
                    });
                    window.addEventListener('print-z-report', (e) => {
                        this.printBase64(e.detail[0].base64);
                    });
                },

                saveHeldCarts() {
                    localStorage.setItem('pos_held_carts', JSON.stringify(this.heldCarts));
                },

                holdCart() {
                    if (this.cart.length === 0) return;
                    
                    let defaultName = this.customerName;
                    if (!defaultName) {
                        const firstItem = this.cart[0].name;
                        const otherItems = this.cart.length > 1 ? ` + ${this.cart.length - 1} item` : '';
                        defaultName = `${firstItem}${otherItems}`;
                    }

                    this.askInput(
                        'Simpan ke Antrean',
                        'Berikan catatan, nomor meja, atau nama pelanggan untuk antrean ini:',
                        'Contoh: Meja 4 / Budi',
                        defaultName,
                        (val) => {
                            const holdId = Date.now();
                            const now = new Date();
                            const holdTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                            this.heldCarts.push({
                                id: holdId,
                                time: holdTime,
                                name: val || defaultName,
                                cart: JSON.parse(JSON.stringify(this.cart)),
                                discount: this.discount,
                                customerName: this.customerName,
                                customerPhone: this.customerPhone,
                                total: this.subtotal
                            });
                            
                            this.saveHeldCarts();
                            this.clearCart(true);
                            this.showToast('Pesanan berhasil dimasukkan ke antrean', 'success');
                        }
                    );
                },

                resumeCart(id) {
                    const index = this.heldCarts.findIndex(h => h.id === id);
                    if (index !== -1) {
                        const doResume = () => {
                            const hold = this.heldCarts[index];
                            this.cart = hold.cart;
                            this.discount = hold.discount;
                            this.customerName = hold.customerName || '';
                            this.customerPhone = hold.customerPhone || '';
                            
                            this.heldCarts.splice(index, 1);
                            this.saveHeldCarts();
                            this.showHoldModal = false;
                            this.showToast('Antrean berhasil dilanjutkan', 'success');
                        };

                        if (this.cart.length > 0) {
                            this.askConfirm(
                                'Tumpuk Keranjang?', 
                                'Keranjang Anda saat ini tidak kosong. Jika dilanjutkan, belanjaan saat ini akan terganti oleh antrean yang dipanggil. Lanjutkan?', 
                                doResume
                            );
                        } else {
                            doResume();
                        }
                    }
                },

                deleteHeldCart(id) {
                    this.askConfirm(
                        'Hapus Antrean?', 
                        'Apakah Anda yakin ingin menghapus antrean ini? Data tidak bisa dikembalikan.', 
                        () => {
                            this.heldCarts = this.heldCarts.filter(h => h.id !== id);
                            this.saveHeldCarts();
                        }
                    );
                },

                addProduct(id, name, price, hasVariants, variants = null) {
                    if (hasVariants && variants) {
                        this.currentProductForVariant = { id, name };
                        this.currentVariants = variants;
                        this.showVariantModal = true;
                        return;
                    }
                    this.addToCart(id, null, name, price);
                },

                addVariantToCart(variantId, variantName, variantPrice) {
                    const fullName = this.currentProductForVariant.name + ' - ' + variantName;
                    this.addToCart(this.currentProductForVariant.id, variantId, fullName, variantPrice);
                    this.showVariantModal = false;
                },

                async connectPrinter() {
                    if (this.printerType === 'bluetooth') {
                        try {
                            const device = await navigator.bluetooth.requestDevice({
                                filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
                                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                            });
                            const server = await device.gatt.connect();
                            const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                            const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                            
                            this.printerDevice = device;
                            this.printerCharacteristic = characteristic;
                            this.printerConnected = true;
                            this.showToast('Printer Bluetooth terhubung!', 'success');
                            
                            device.addEventListener('gattserverdisconnected', () => {
                                this.printerConnected = false;
                                this.showToast('Koneksi printer terputus', 'error');
                            });
                        } catch (error) {
                            console.error(error);
                            this.showToast('Gagal Bluetooth: ' + error.message, 'error');
                        }
                    } else if (this.printerType === 'serial') {
                        try {
                            const port = await navigator.serial.requestPort();
                            await port.open({ baudRate: 9600 });
                            this.printerPort = port;
                            this.printerConnected = true;
                            this.showToast('Printer Serial/USB terhubung!', 'success');
                        } catch (error) {
                            console.error(error);
                            this.showToast('Gagal Serial: ' + error.message, 'error');
                        }
                    }
                },

                async printBase64(base64Data) {
                    if (!this.printerConnected) {
                        this.showToast('Gagal mencetak: Printer belum dihubungkan!', 'error');
                        return;
                    }
                    try {
                        const binaryString = window.atob(base64Data);
                        const bytes = new Uint8Array(binaryString.length);
                        for (let i = 0; i < binaryString.length; i++) {
                            bytes[i] = binaryString.charCodeAt(i);
                        }
                        
                        if (this.printerType === 'bluetooth' && this.printerCharacteristic) {
                            const chunkSize = 100;
                            for (let i = 0; i < bytes.length; i += chunkSize) {
                                const chunk = bytes.slice(i, i + chunkSize);
                                await this.printerCharacteristic.writeValue(chunk);
                            }
                        } else if (this.printerType === 'serial' && this.printerPort) {
                            const writer = this.printerPort.writable.getWriter();
                            await writer.write(bytes);
                            writer.releaseLock();
                        }
                    } catch (error) {
                        console.error(error);
                        this.showToast('Kesalahan cetak: ' + error.message, 'error');
                    }
                },

                addToCart(productId, variantId, name, price) {
                    // Cek jika produk sudah ada di keranjang
                    const existingIndex = this.cart.findIndex(item => item.product_id === productId && item.product_variant_id === variantId);
                    if (existingIndex > -1) {
                        this.cart[existingIndex].quantity++;
                    } else {
                        this.cart.unshift({ // Add to top
                            product_id: productId,
                            product_variant_id: variantId,
                            name: name,
                            price: price,
                            quantity: 1
                        });
                    }
                },

                updateQty(index, change) {
                    let newQty = this.cart[index].quantity + change;
                    if (newQty > 0) {
                        this.cart[index].quantity = newQty;
                    }
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                },

                clearCart(force = false) {
                    if (force) {
                        this.cart = [];
                        this.discount = 0;
                        this.cashPaid = 0;
                        return;
                    }

                    this.askConfirm(
                        'Kosongkan Keranjang?',
                        'Semua barang di keranjang akan dihapus. Lanjutkan?',
                        () => {
                            this.cart = [];
                            this.discount = 0;
                            this.cashPaid = 0;
                        }
                    );
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get grandTotal() {
                    return Math.max(0, this.subtotal - this.discount);
                },

                openCheckoutModal() {
                    if(this.cart.length === 0) return;
                    this.cashPaid = this.grandTotal; // Default uang pas
                    this.calculateChange();
                    this.showCheckoutModal = true;
                },

                setCashPaid(amount) {
                    this.cashPaid = amount;
                    this.calculateChange();
                },

                calculateChange() {
                    this.cashChange = this.cashPaid - this.grandTotal;
                },

                submitOrder() {
                    if(this.paymentMethod === 'cash' && this.cashPaid < this.grandTotal) {
                        this.showToast('Uang yang dibayarkan kurang!', 'error');
                        return;
                    }

                    this.isProcessing = true;
                    
                    const payload = {
                        items: this.cart,
                        discount: this.discount,
                        payment_method: this.paymentMethod,
                        cash_paid: this.cashPaid,
                        cash_change: this.cashChange,
                        customer_name: this.customerName,
                        customer_phone: this.customerPhone,
                        payment_details: {
                            type: this.paymentMethod,
                            // UUID/Idempotency key dapat digenerate di sini
                            idempotency_key: crypto.randomUUID()
                        }
                    };

                    // Panggil Livewire backend
                    @this.call('processCheckout', payload);
                },

                formatMoney(amount) {
                    return Number(amount).toLocaleString('id-ID');
                },

                showToast(message, type = 'success') {
                    const id = this.toastId++;
                    this.toasts.push({ id, message, type });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 4000);
                }
            }));
        });
    </script>
</div>
