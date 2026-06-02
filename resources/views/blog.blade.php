<x-layouts.app>
<main class="site-main bg-[#fcf9f5] min-h-screen pb-0">

    <!-- Hero Featured Article -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-8 md:py-12">
        <a href="{{ url('/blog/sample-article') }}" wire:navigate class="block relative w-full aspect-[4/3] md:aspect-[21/9] overflow-hidden group">
            <img src="{{ asset('assets/images/blog-hero.png') }}" alt="Featured Article" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors duration-500"></div>
            <div class="absolute inset-0 p-8 md:p-16 flex flex-col justify-end max-w-3xl">
                <p class="text-white/80 text-[10px] font-mono tracking-[0.2em] uppercase mb-4 md:mb-6">Design Process</p>
                <h1 class="text-xl md:text-5xl lg:text-6xl font-serif text-white mb-3 md:mb-6 leading-tight">
                    The Geometry of Modesty: Fall 2024 Design Philosophy
                </h1>
                <p class="hidden md:block text-white/90 text-sm md:text-base leading-relaxed mb-8 max-w-xl">
                    Mengeksplorasi titik temu antara siluet tradisional dan struktur arsitektural modern dalam koleksi terbaru Raabiha.
                </p>
                <span class="text-white text-[11px] font-mono tracking-widest uppercase border-b border-white pb-1 self-start hover:text-[#064e3b] hover:border-[#064e3b] transition-colors">
                    Read Article &rarr;
                </span>
            </div>
        </a>
    </section>

    <!-- Latest Observations Header -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-8 border-b border-[#e5e2de] mb-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <h2 class="text-3xl md:text-4xl font-serif text-[#1c1c1a]">Latest Observations</h2>
            <div class="flex gap-6 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 scrollbar-hide text-[10px] font-mono tracking-widest uppercase text-[#615e57]">
                <a href="#" class="text-[#1c1c1a] font-bold border-b border-[#1c1c1a] pb-1 whitespace-nowrap">All</a>
                <a href="#" class="hover:text-[#1c1c1a] transition-colors whitespace-nowrap">Design</a>
                <a href="#" class="hover:text-[#1c1c1a] transition-colors whitespace-nowrap">Lifestyle</a>
                <a href="#" class="hover:text-[#1c1c1a] transition-colors whitespace-nowrap">Process</a>
                <a href="#" class="hover:text-[#1c1c1a] transition-colors whitespace-nowrap">Brand</a>
            </div>
        </div>
    </section>

    <!-- Blog Grid -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 pb-24">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-x-20 md:gap-y-32">
            
            <!-- Left Column -->
            <div class="space-y-16 md:space-y-32">
                <!-- Article 1 -->
                <article class="group cursor-pointer">
                    <a href="{{ url('/blog/sample-article') }}" wire:navigate class="block">
                        <div class="w-full aspect-[4/5] overflow-hidden mb-6">
                            <img src="{{ asset('assets/images/gallery-3.png') }}" alt="Silk Texture" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <p class="text-[#615e57] text-[9px] font-mono tracking-[0.2em] uppercase mb-3">Atelier & Craft</p>
                        <h3 class="text-lg md:text-3xl font-serif text-[#1c1c1a] mb-3 md:mb-4 group-hover:text-[#064e3b] transition-colors">Precision in Pattern Making: The Structural Sleeve</h3>
                        <p class="hidden md:block text-[#615e57] text-sm leading-relaxed mb-6">
                            Di balik desain silk premium Raabiha, terdapat perhitungan matematis untuk menciptakan drapery yang sempurna tanpa mengorbankan volume.
                        </p>
                        <span class="text-[#1c1c1a] text-[10px] font-mono tracking-widest uppercase border-b border-[#1c1c1a] pb-1">Read Article &rarr;</span>
                    </a>
                </article>

                <!-- Article 2 -->
                <article class="group cursor-pointer">
                    <a href="{{ url('/blog/sample-article') }}" wire:navigate class="block">
                        <div class="w-full aspect-[4/3] overflow-hidden mb-6">
                            <img src="{{ asset('assets/images/blog-coat.png') }}" alt="Grey Coat" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <p class="text-[#615e57] text-[9px] font-mono tracking-[0.2em] uppercase mb-3">Style & Styling</p>
                        <h3 class="text-lg md:text-3xl font-serif text-[#1c1c1a] mb-3 md:mb-4 group-hover:text-[#064e3b] transition-colors">The Art of Visual Layering</h3>
                        <p class="hidden md:block text-[#615e57] text-sm leading-relaxed mb-6">
                            Bagaimana menggabungkan tekstur wol berat dengan siluet ramping untuk menciptakan kedalaman desain yang elegan.
                        </p>
                        <span class="text-[#1c1c1a] text-[10px] font-mono tracking-widest uppercase border-b border-[#1c1c1a] pb-1">Read Article &rarr;</span>
                    </a>
                </article>
            </div>

            <!-- Right Column (Offset) -->
            <div class="space-y-16 md:space-y-32 md:pt-32">
                <!-- Article 3 -->
                <article class="group cursor-pointer">
                    <a href="{{ url('/blog/sample-article') }}" wire:navigate class="block">
                        <div class="w-full aspect-[4/5] overflow-hidden mb-6">
                            <img src="{{ asset('assets/images/gallery-2.png') }}" alt="White Architecture" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <p class="text-[#615e57] text-[9px] font-mono tracking-[0.2em] uppercase mb-3">Architecture</p>
                        <h3 class="text-lg md:text-3xl font-serif text-[#1c1c1a] mb-3 md:mb-4 group-hover:text-[#064e3b] transition-colors">Brutalist Spaces and the Point of Design</h3>
                        <p class="hidden md:block text-[#615e57] text-sm leading-relaxed mb-6">
                            Mengapa beton dan kekosongan menjadi inspirasi utama di balik estetika Modest Architectural kami.
                        </p>
                        <span class="text-[#1c1c1a] text-[10px] font-mono tracking-widest uppercase border-b border-[#1c1c1a] pb-1">Read Article &rarr;</span>
                    </a>
                </article>

                <!-- Article 4 -->
                <article class="group cursor-pointer">
                    <a href="{{ url('/blog/sample-article') }}" wire:navigate class="block">
                        <div class="w-full aspect-square overflow-hidden mb-6 bg-[#f0ede9]">
                            <img src="{{ asset('assets/images/blog-objects.png') }}" alt="Green objects on desk" class="w-full h-full object-cover mix-blend-multiply group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <p class="text-[#615e57] text-[9px] font-mono tracking-[0.2em] uppercase mb-3">Behind the Scenes</p>
                        <h3 class="text-lg md:text-3xl font-serif text-[#1c1c1a] mb-3 md:mb-4 group-hover:text-[#064e3b] transition-colors">Behind the Bespoke: Our Fitting Process</h3>
                        <p class="hidden md:block text-[#615e57] text-sm leading-relaxed mb-6">
                            Melihat lebih dekat standar ketat dan perhatian terhadap detail dalam proses pembuatan busana made-to-order kami.
                        </p>
                        <span class="text-[#1c1c1a] text-[10px] font-mono tracking-widest uppercase border-b border-[#1c1c1a] pb-1">Read Article &rarr;</span>
                    </a>
                </article>
            </div>

        </div>
    </section>


</main>
</x-layouts.app>
