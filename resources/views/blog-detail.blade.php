<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Detail Blog" backUrl="/blog" transparent="true" :share="true" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pt-0 md:pt-0 pb-0">

        {{-- ===== HERO SECTION (Full Bleed on Mobile) ===== --}}
        <div class="relative w-full aspect-square md:aspect-[21/9] overflow-hidden">
            <img src="{{ asset('assets/images/gallery-3.png') }}" alt="Featured Article" class="w-full h-full object-cover">
            {{-- Gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-black/10"></div>
            {{-- Text content pinned bottom --}}
            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-16 max-w-4xl">
                <p class="text-white/70 text-[9px] font-mono tracking-[0.2em] uppercase mb-3">Design Process &mdash; Sep 24, 2024</p>
                <h1 class="text-2xl md:text-5xl lg:text-7xl font-serif text-white mb-3 leading-tight">
                    The Geometry of Modesty
                </h1>
                <p class="text-white/80 text-sm md:text-base leading-relaxed max-w-xl hidden md:block">
                    Mengeksplorasi titik temu antara siluet tradisional dan struktur arsitektural modern dalam koleksi terbaru Raabiha.
                </p>
            </div>
        </div>

        {{-- ===== CONTENT LAYOUT ===== --}}
        <section class="max-w-6xl mx-auto px-6 lg:px-12 py-10 md:py-24">
            <div class="flex flex-col md:flex-row gap-10 md:gap-24">
                
                {{-- Left Meta Column --}}
                <div class="w-full md:w-48 shrink-0">
                    <div class="sticky top-32 flex flex-row md:flex-col justify-between md:justify-start flex-wrap gap-4 md:gap-0 md:space-y-8 border-b border-[#e5e2de] md:border-none pb-6 md:pb-0 mb-6 md:mb-0">
                        <div>
                            <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mb-1">Written by</p>
                            <p class="text-[#1c1c1a] font-serif italic text-base md:text-lg">Fatima Al-Sayed</p>
                        </div>
                        <div>
                            <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mb-1">Published</p>
                            <p class="text-[#615e57] text-sm">Sep 24, 2024</p>
                        </div>
                        <div class="hidden md:block">
                            <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mb-2 md:mb-3">Share</p>
                            <div class="flex flex-row md:flex-col gap-4 md:gap-2">
                                <a href="#" class="text-[#615e57] hover:text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest transition-colors">Twitter</a>
                                <a href="#" class="text-[#615e57] hover:text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest transition-colors">LinkedIn</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Article Content --}}
                <article class="flex-1 max-w-3xl prose prose-neutral prose-p:text-[#615e57] prose-p:leading-relaxed prose-headings:font-serif prose-headings:text-[#1c1c1a] prose-headings:font-normal prose-a:text-[#064e3b] prose-a:no-underline hover:prose-a:underline">
                    
                    <p class="text-lg md:text-2xl text-[#1c1c1a] leading-relaxed mb-10">
                        Arsitektur bukan hanya soal membangun ruang fisik, tetapi juga ruang psikologis. Di Raabiha, kami melihat garmen sebagai &ldquo;mini-arsitektur&rdquo; yang memberikan batas, kenyamanan, dan pernyataan bagi pemakainya.
                    </p>

                    <div class="my-10 md:my-16 w-full not-prose">
                        <img src="{{ asset('assets/images/gallery-5.png') }}" alt="Green texture" class="w-full h-auto aspect-[16/9] object-cover">
                        <p class="text-center text-[#615e57] text-[9px] font-mono tracking-[0.1em] mt-3 uppercase">Figure 01. Studi Tekstur Struktural</p>
                    </div>

                    <h2 class="text-2xl md:text-4xl mt-12 md:mt-16 mb-6 md:mb-8 font-serif">Precision in Drapery</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Tantangan dalam mendesain modest fashion adalah bagaimana menciptakan ruang tanpa membuat siluet terlihat berat. Kami menemukan jawabannya melalui potongan bias dan manipulasi kain tiga dimensi. Alih-alih menyembunyikan, kami &ldquo;membingkai&rdquo;.
                    </p>

                    {{-- Pull Quote --}}
                    <blockquote class="not-prose my-12 md:my-16 text-center px-0">
                        <p class="text-2xl md:text-4xl lg:text-5xl font-serif text-[#064e3b] leading-tight italic max-w-2xl mx-auto">
                            &ldquo;Modesty is not the absence of presence, but the presence of architectural intent.&rdquo;
                        </p>
                        <footer class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mt-6">&mdash; Creative Director, Raabiha</footer>
                    </blockquote>

                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Setiap lipatan memiliki tujuan. Seperti pilar yang menopang atap, setiap jahitan menopang struktur pakaian, memberikan kebebasan bergerak sekaligus mempertahankan bentuk yang sempurna.
                    </p>

                    <h2 class="text-2xl md:text-4xl mt-12 md:mt-16 mb-6 font-serif">Structure Without Constraint</h2>
                    <p class="text-[#615e57] leading-relaxed mb-6">
                        Material adalah arsitektur yang dapat disentuh. Pilihan tekstil menentukan apakah sebuah pakaian berdiri sendiri atau mengalir mengikuti gerakan — dan dalam keduanya, terdapat tujuan estetis yang sama.
                    </p>

                    <div class="my-10 md:my-16 w-full not-prose">
                        <img src="{{ asset('assets/images/blog-objects.png') }}" alt="Objects detail" class="w-full h-auto aspect-[16/9] object-cover">
                        <p class="text-center text-[#615e57] text-[9px] font-mono tracking-[0.1em] mt-3 uppercase">Figure 02. Detail Material Finishing</p>
                    </div>

                    <p class="text-[#615e57] leading-relaxed">
                        Kami terus bereksperimen dengan material-material non-konvensional untuk dunia modest fashion — boiled wool, canvas teknis, hingga tenun tangan lokal — karena percaya bahwa pilihan material adalah pernyataan nilai yang sama kuatnya dengan siluet itu sendiri.
                    </p>
                    
                </article>
            </div>
        </section>

        {{-- ===== EMERALD NEWSLETTER BANNER (Desktop only — hidden on mobile per UI guidelines) ===== --}}
        <section class="hidden md:block max-w-[1440px] mx-auto px-6 lg:px-12 pb-24">
            <div class="bg-[#064e3b] px-8 py-16 md:p-24 flex flex-col md:flex-row justify-between items-center gap-12">
                <div class="max-w-md">
                    <h2 class="text-4xl font-serif text-white mb-4">Join the Dialogue</h2>
                    <p class="text-white/80 text-sm leading-relaxed">
                        Terima pembaruan eksklusif mengenai esai desain terbaru dan proses di balik layar Raabiha.
                    </p>
                </div>
                <form class="w-full max-w-md flex flex-col gap-4">
                    <input type="email" placeholder="YOUR EMAIL" class="w-full bg-transparent border-b border-white/30 pb-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:border-white transition-colors">
                    <button type="button" class="w-full bg-white text-[#064e3b] px-8 py-4 text-[11px] font-mono tracking-widest uppercase hover:bg-[#f0ede9] transition-colors mt-2">Subscribe</button>
                </form>
            </div>
        </section>

        {{-- ===== RELATED ARTICLES ===== --}}
        <section class="bg-[#f0ede9] py-16 md:py-32 px-6 lg:px-12">
            <div class="max-w-[1440px] mx-auto">
                <p class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-8 md:mb-12 text-center border-b border-[#d1cec9] pb-4 max-w-xs mx-auto">
                    Continue Reading
                </p>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-12">
                    {{-- Related 1 --}}
                    <article class="group cursor-pointer">
                        <a href="/blog/the-geometry-of-modesty" wire:navigate class="block">
                            <div class="w-full aspect-[4/5] overflow-hidden mb-4 md:mb-6">
                                <img src="{{ asset('assets/images/blog-white-suit.png') }}" alt="White suit" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            </div>
                            <p class="text-[#064e3b] text-[9px] font-mono tracking-[0.2em] uppercase mb-2">Lifestyle</p>
                            <h3 class="text-base md:text-2xl font-serif text-[#1c1c1a] mb-2 md:mb-3 group-hover:text-[#064e3b] transition-colors leading-snug">The Weight of Silence: Designing for Stillness</h3>
                            <p class="text-[#615e57] text-xs leading-relaxed mb-4 line-clamp-2 hidden md:block">
                                Mencari ketenangan di tengah bisingnya dunia modern melalui pilihan berbusana yang disengaja.
                            </p>
                        </a>
                    </article>

                    {{-- Related 2 --}}
                    <article class="group cursor-pointer">
                        <a href="/blog/the-bespoke-journey" wire:navigate class="block">
                            <div class="w-full aspect-[4/5] overflow-hidden mb-4 md:mb-6">
                                <img src="{{ asset('assets/images/blog-coat.png') }}" alt="Grey coat" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            </div>
                            <p class="text-[#064e3b] text-[9px] font-mono tracking-[0.2em] uppercase mb-2">Process</p>
                            <h3 class="text-base md:text-2xl font-serif text-[#1c1c1a] mb-2 md:mb-3 group-hover:text-[#064e3b] transition-colors leading-snug">The Bespoke Journey: From Sketch to Sanctuary</h3>
                            <p class="text-[#615e57] text-xs leading-relaxed mb-4 line-clamp-2 hidden md:block">
                                Mengintip langsung ke dalam atelier kami dan melihat perjalanan panjang sebuah konsep.
                            </p>
                        </a>
                    </article>

                    {{-- Related 3 --}}
                    <article class="group cursor-pointer hidden md:block">
                        <a href="/blog/architectural-echoes" wire:navigate class="block">
                            <div class="w-full aspect-[4/5] overflow-hidden mb-6">
                                <img src="{{ asset('assets/images/blog-pool.png') }}" alt="Pool" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            </div>
                            <p class="text-[#064e3b] text-[9px] font-mono tracking-[0.2em] uppercase mb-3">Design</p>
                            <h3 class="text-2xl font-serif text-[#1c1c1a] mb-3 group-hover:text-[#064e3b] transition-colors leading-snug">Architectural Echoes: Islamic Patterns in Modernity</h3>
                            <p class="text-[#615e57] text-xs leading-relaxed mb-4 line-clamp-2">
                                Reinterpretasi geometri sakral ke dalam potongan minimalis kontemporer.
                            </p>
                        </a>
                    </article>
                </div>
            </div>
        </section>

    </main>
    </div>
</x-layouts.app>
