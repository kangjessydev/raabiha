<main class="site-main bg-[#fcf9f5] min-h-screen pb-0">

    <!-- Hero Featured Article -->
    @if($featuredPost)
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-8 md:py-12">
        <a href="{{ url('/blog/' . $featuredPost->slug) }}" class="block relative w-full aspect-[4/3] md:aspect-[21/9] overflow-hidden group">
            @if($featuredPost->image && $media = \Awcodes\Curator\Models\Media::find($featuredPost->image))
                <img src="{{ Storage::url($media->path) }}" alt="{{ $featuredPost->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            @else
                <img src="{{ asset('assets/images/blog-hero.webp') }}" alt="{{ $featuredPost->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            @endif
            <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors duration-500"></div>
            <div class="absolute inset-0 p-8 md:p-16 flex flex-col justify-end max-w-3xl">
                <p class="text-white/80 text-[10px] font-mono tracking-[0.2em] uppercase mb-4 md:mb-6">
                    {{ $featuredPost->category ? $featuredPost->category->name : 'Uncategorized' }}
                </p>
                <h1 class="text-xl md:text-5xl lg:text-6xl font-serif text-white mb-3 md:mb-6 leading-tight">
                    {{ $featuredPost->title }}
                </h1>
                <p class="hidden md:block text-white/90 text-sm md:text-base leading-relaxed mb-8 max-w-xl">
                    {{ $featuredPost->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($featuredPost->content), 160) }}
                </p>
                <span class="text-white text-[11px] font-mono tracking-widest uppercase border-b border-white pb-1 self-start hover:text-[#064e3b] hover:border-[#064e3b] transition-colors">
                    Read Article &rarr;
                </span>
            </div>
        </a>
    </section>
    @endif

    <!-- Latest Observations Header -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-8 border-b border-[#e5e2de] mb-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <{{ $featuredPost ? 'h2' : 'h1' }} class="text-3xl md:text-4xl font-serif text-[#1c1c1a]">
                {{ $category !== 'all' ? 'Kategori: ' . ($categories->firstWhere('slug', $category)->name ?? $category) : 'Latest Observations' }}
            </{{ $featuredPost ? 'h2' : 'h1' }}>
            <div class="flex gap-6 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 scrollbar-hide text-[10px] font-mono tracking-widest uppercase text-[#615e57]">
                <button wire:key="cat-all" wire:click="setCategory('all')" class="whitespace-nowrap {{ $category === 'all' ? 'text-[#1c1c1a] font-bold border-b border-[#1c1c1a] pb-1' : 'hover:text-[#1c1c1a] transition-colors' }}">All</button>
                @foreach($categories as $cat)
                    <button wire:key="cat-{{ $cat->id }}" wire:click="setCategory('{{ $cat->slug }}')" class="whitespace-nowrap {{ $category === $cat->slug ? 'text-[#1c1c1a] font-bold border-b border-[#1c1c1a] pb-1' : 'hover:text-[#1c1c1a] transition-colors' }}">{{ $cat->name }}</button>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Blog Grid -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 pb-24">
        @if($posts->isEmpty())
            <div class="py-24 text-center border-t border-[#e5e2de]">
                <p class="text-[#615e57] text-lg italic font-serif">Belum ada artikel dalam kategori ini.</p>
                <p class="text-[#a3a09b] text-xs font-mono uppercase tracking-widest mt-2">Silakan pilih kategori lainnya</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-x-20 md:gap-y-32">
                
                @php
                    $postsToLoop = $featuredPost ? $posts->skip(1)->values() : $posts;
                @endphp
                
                <!-- Left Column -->
                <div class="space-y-16 md:space-y-32">
                    @foreach($postsToLoop as $index => $post)
                        @if($index % 2 == 0)
                            <article wire:key="post-left-{{ $post->id }}" class="group cursor-pointer">
                                <a href="{{ url('/blog/' . $post->slug) }}" class="block">
                                    <div class="w-full aspect-[4/5] overflow-hidden mb-6">
                                        @if($post->image && $media = \Awcodes\Curator\Models\Media::find($post->image))
                                            <img src="{{ Storage::url($media->path) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                        @else
                                            <img src="{{ asset('assets/images/gallery-' . (($index % 3) + 3) . '.png') }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                        @endif
                                    </div>
                                    <p class="text-[#615e57] text-[9px] font-mono tracking-[0.2em] uppercase mb-3">{{ $post->category ? $post->category->name : 'Uncategorized' }}</p>
                                    <h3 class="text-lg md:text-3xl font-serif text-[#1c1c1a] mb-3 md:mb-4 group-hover:text-[#064e3b] transition-colors">{{ $post->title }}</h3>
                                    <p class="hidden md:block text-[#615e57] text-sm leading-relaxed mb-6">
                                        {{ $post->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}
                                    </p>
                                    <span class="text-[#1c1c1a] text-[10px] font-mono tracking-widest uppercase border-b border-[#1c1c1a] pb-1">Read Article &rarr;</span>
                                </a>
                            </article>
                        @endif
                    @endforeach
                </div>

                <!-- Right Column (Offset) -->
                <div class="space-y-16 md:space-y-32 md:pt-32">
                    @foreach($postsToLoop as $index => $post)
                        @if($index % 2 != 0)
                            <article wire:key="post-right-{{ $post->id }}" class="group cursor-pointer">
                                <a href="{{ url('/blog/' . $post->slug) }}" class="block">
                                    <div class="w-full aspect-[4/5] overflow-hidden mb-6 {{ $index % 4 == 3 ? 'bg-[#f0ede9]' : '' }}">
                                        @if($post->image && $media = \Awcodes\Curator\Models\Media::find($post->image))
                                            <img src="{{ Storage::url($media->path) }}" alt="{{ $post->title }}" class="w-full h-full object-cover {{ $index % 4 == 3 ? 'mix-blend-multiply' : '' }} group-hover:scale-105 transition-transform duration-700">
                                        @else
                                            @php
                                                $fallbackImg = $index % 4 == 3 ? 'blog-objects.webp' : 'gallery-2.webp';
                                            @endphp
                                            <img src="{{ asset('assets/images/' . $fallbackImg) }}" alt="{{ $post->title }}" class="w-full h-full object-cover {{ $index % 4 == 3 ? 'mix-blend-multiply' : '' }} group-hover:scale-105 transition-transform duration-700">
                                        @endif
                                    </div>
                                    <p class="text-[#615e57] text-[9px] font-mono tracking-[0.2em] uppercase mb-3">{{ $post->category ? $post->category->name : 'Uncategorized' }}</p>
                                    <h3 class="text-lg md:text-3xl font-serif text-[#1c1c1a] mb-3 md:mb-4 group-hover:text-[#064e3b] transition-colors">{{ $post->title }}</h3>
                                    <p class="hidden md:block text-[#615e57] text-sm leading-relaxed mb-6">
                                        {{ $post->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}
                                    </p>
                                    <span class="text-[#1c1c1a] text-[10px] font-mono tracking-widest uppercase border-b border-[#1c1c1a] pb-1">Read Article &rarr;</span>
                                </a>
                            </article>
                        @endif
                    @endforeach
                </div>
                
            </div>
            
            <div class="mt-16">
                {{ $posts->links() }}
            </div>
        @endif
    </section>

</main>
