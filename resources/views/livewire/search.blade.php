<main class="site-main bg-[#fcf9f5] min-h-screen pb-24 pt-12 md:pt-16">
    <!-- Header Section -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 mb-12">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif text-[#1c1c1a] mb-4 tracking-tight uppercase">
            Hasil Pencarian
        </h1>
        <p class="text-[#615e57] text-sm font-mono tracking-widest uppercase">
            Kata Kunci: <span class="font-bold text-[#1c1c1a]">"{{ $q }}"</span>
        </p>
        <div class="w-full h-[1px] bg-[#e5e2de] mt-8"></div>
    </section>

    <section class="max-w-[1440px] mx-auto px-6 lg:px-12">
        @if(trim($q) === '')
            <div class="text-center py-24 border border-dashed border-[#e5e2de] bg-white/50">
                <p class="text-[#615e57] font-mono uppercase tracking-widest text-xs mb-4">Masukkan kata kunci pencarian pada kotak pencarian di atas.</p>
            </div>
        @elseif($products->isEmpty() && $posts->isEmpty())
            <div class="text-center py-24 border border-dashed border-[#e5e2de] bg-white/50">
                <p class="text-[#615e57] font-mono uppercase tracking-widest text-xs mb-4">Tidak ada produk atau artikel yang cocok dengan "{{ $q }}".</p>
                <a href="{{ url('/shop') }}" wire:navigate class="inline-block border border-[#1c1c1a] px-6 py-3 text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-[#f2efe8] transition-colors">Lihat Semua Produk</a>
            </div>
        @else
            <div class="flex flex-col gap-16">
                <!-- Products Results -->
                @if($products->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-serif tracking-widest uppercase text-[#1c1c1a] mb-8 pb-3 border-b border-[#e5e2de] flex justify-between items-end">
                            <span>Produk Terkait ({{ $products->count() }})</span>
                            <a href="{{ url('/shop?search=' . urlencode($q)) }}" wire:navigate class="text-[10px] font-mono tracking-widest text-[#064e3b] hover:underline uppercase">Lihat di Katalog &rarr;</a>
                        </h2>
                        
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 lg:gap-x-6 gap-y-12 w-full">
                            @foreach ($products as $index => $product)
                                <a href="{{ url('/product/' . $product->slug) }}" wire:navigate class="group block">
                                    <div class="aspect-[4/5] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                                        @if ($product->promo_rules)
                                            <div class="absolute top-3 right-3 bg-[#1a1a1a] text-white text-[9px] px-2 py-1 uppercase tracking-widest z-10">Sale</div>
                                        @endif
                                        @if($product->images && count($product->images) > 0)
                                            @php
                                                $prodMedia = \Awcodes\Curator\Models\Media::find($product->images[0]);
                                            @endphp
                                            @if($prodMedia)
                                                <img src="{{ Storage::url($prodMedia->path) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->name }}" />
                                            @else
                                                <img src="{{ asset('assets/images/gallery-' . (($index % 3) + 1) . '.png') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->name }}" />
                                            @endif
                                        @else
                                            <img src="{{ asset('assets/images/gallery-' . (($index % 3) + 1) . '.png') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->name }}" />
                                        @endif
                                    </div>
                                    <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1">{{ $product->name }}</h3>
                                    <div class="text-[13px] text-[#525252]">Rp{{ number_format($product->effective_price, 0, ',', '.') }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Blog/Article Results -->
                @if($posts->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-serif tracking-widest uppercase text-[#1c1c1a] mb-8 pb-3 border-b border-[#e5e2de] flex justify-between items-end">
                            <span>Artikel & Jurnal ({{ $posts->count() }})</span>
                            <a href="{{ url('/blog') }}" wire:navigate class="text-[10px] font-mono tracking-widest text-[#064e3b] hover:underline uppercase">Buka Blog &rarr;</a>
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full">
                            @foreach($posts as $index => $post)
                                <article class="group cursor-pointer">
                                    <a href="{{ url('/blog/' . $post->slug) }}" wire:navigate class="block">
                                        <div class="w-full aspect-[16/10] overflow-hidden mb-4 bg-[#e5e2de]">
                                            @if($post->image && $media = \Awcodes\Curator\Models\Media::find($post->image))
                                                <img src="{{ Storage::url($media->path) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                            @else
                                                <img src="{{ asset('assets/images/gallery-' . (($index % 3) + 3) . '.png') }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                            @endif
                                        </div>
                                        <p class="text-[#615e57] text-[9px] font-mono tracking-[0.2em] uppercase mb-2">
                                            {{ $post->category ? $post->category->name : 'Uncategorized' }}
                                        </p>
                                        <h3 class="text-sm md:text-base font-serif text-[#1c1c1a] mb-2 group-hover:text-[#064e3b] transition-colors line-clamp-2">
                                            {{ $post->title }}
                                        </h3>
                                        <p class="text-[#615e57] text-xs leading-relaxed mb-4 line-clamp-2">
                                            {{ $post->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}
                                        </p>
                                        <span class="text-[#1c1c1a] text-[9px] font-mono tracking-widest uppercase border-b border-[#1c1c1a] pb-0.5 inline-block group-hover:text-[#064e3b] group-hover:border-[#064e3b] transition-colors">
                                            Baca Selengkapnya &rarr;
                                        </span>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </section>
</main>
