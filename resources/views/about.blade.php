<x-layouts.app title="Tentang Kami" description="Pelajari lebih lanjut tentang Raabiha, brand modest fashion modern premium yang menggabungkan desain minimalis struktural dengan kenyamanan urban.">
    <main class="site-main bg-[#fcf9f5] min-h-screen pt-12 md:pt-24 pb-0">
        @php
            // Helper untuk render image Curator or Fallback
            $resolveImage = function ($curatorId, $fallback = null) {
                if ($curatorId) {
                    $media = \Awcodes\Curator\Models\Media::find($curatorId);
                    if ($media)
                        return $media->url;
                }
                return $fallback ?: 'https://placehold.co/800x800/e5e2de/615e57?text=Image+Not+Available';
            };

            // Load Settings
            $aboutAtelierTag = \App\Models\SiteSetting::where('key', 'about_atelier_tag')->value('value') ?: 'Filosofi Brand';
            $aboutAtelierTitle = \App\Models\SiteSetting::where('key', 'about_atelier_title')->value('value') ?: 'Arsitektur dalam Kesopanan.';
            $aboutAtelierDescription = \App\Models\SiteSetting::where('key', 'about_atelier_description')->value('value') ?: 'Raabiha mendefinisikan kembali modest fashion melalui lensa minimalisme struktural. Setiap jahitan adalah komitmen terhadap presisi intelektual dan estetika urban yang mengakar pada nilai-nilai tradisi yang luhur.';
            $aboutAtelierImage = \App\Models\SiteSetting::where('key', 'about_atelier_image')->value('value');
            $aboutAtelierBadge = \App\Models\SiteSetting::where('key', 'about_atelier_badge')->value('value') ?: 'Atelier Process';

            $aboutVisionTitle = \App\Models\SiteSetting::where('key', 'about_vision_title')->value('value') ?: 'Menjadi Mercusuar Global untuk Estetika Modest Modern.';
            $aboutVisionDescription = \App\Models\SiteSetting::where('key', 'about_vision_description')->value('value') ?: 'Kami melihat masa depan di mana busana santun bukan hanya tentang penutupan, melainkan tentang perayaan keberanian artistik dan integritas struktural.';
            $aboutValuesTitle = \App\Models\SiteSetting::where('key', 'about_values_title')->value('value') ?: 'Nilai Inti';

            $rawValues = \App\Models\SiteSetting::where('key', 'about_values')->value('value');
            $aboutValues = $rawValues ? json_decode($rawValues, true) : [];
            if (!is_array($aboutValues) || empty($aboutValues)) {
                $aboutValues = [
                    [
                        'title' => 'Keahlian Tanpa Kompromi',
                        'description' => 'Memberdayakan pengrajin lokal untuk menghasilkan kualitas haute couture yang dapat diakses secara global.'
                    ],
                    [
                        'title' => 'Evolusi Berkelanjutan',
                        'description' => 'Inovasi terus-menerus dalam penggunaan kain ramah lingkungan dan teknik konstruksi tanpa limbah.'
                    ]
                ];
            }

            $aboutTimelineTitle = \App\Models\SiteSetting::where('key', 'about_timeline_title')->value('value') ?: 'Perjalanan Kami';
            $aboutTimelineSubtitle = \App\Models\SiteSetting::where('key', 'about_timeline_subtitle')->value('value') ?: 'Evolusi dari bisnis kecil menjadi perusahaan besar';

            $rawTimeline = \App\Models\SiteSetting::where('key', 'about_timeline')->value('value');
            $aboutTimeline = $rawTimeline ? json_decode($rawTimeline, true) : [];
            if (!is_array($aboutTimeline) || empty($aboutTimeline)) {
                $aboutTimeline = [
                    [
                        'year' => '2018',
                        'title' => 'Awal Mula',
                        'description' => 'Didirikan sebagai atelier kecil di jantung Jakarta, berfokus pada pesanan kustom terbatas dengan material premium.'
                    ],
                    [
                        'year' => '2020',
                        'title' => 'Ekspansi Regional',
                        'description' => 'Meluncurkan koleksi Ready-to-Wear pertama yang langsung mendapat pengakuan di Asia Tenggara karena siluetnya yang unik.'
                    ],
                    [
                        'year' => '2024',
                        'title' => 'Butik Global',
                        'description' => 'Membuka flagship store digital dan fisik pertama di Dubai dan London, mengukuhkan posisi sebagai pemimpin modest luxury.'
                    ]
                ];
            }

            $aboutQuoteText = \App\Models\SiteSetting::where('key', 'about_quote_text')->value('value') ?: 'Kesopanan bukan tentang menyembunyikan, tapi tentang mengungkapkan karakter melalui ketenangan.';
        @endphp

        <!-- Hero Section -->
        <section class="max-w-7xl mx-auto px-6 lg:px-12 mb-24 md:mb-32">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Image (Left on Desktop, Top on Mobile) -->
                <div class="relative order-2 md:order-1">
                    <div
                        class="absolute top-4 left-4 bg-[#1c1c1a] text-white text-[9px] uppercase tracking-widest px-3 py-1.5 z-10">
                        {{ $aboutAtelierBadge }}
                    </div>
                    <img src="{{ $resolveImage($aboutAtelierImage, asset('assets/images/about-atelier.webp')) }}"
                        alt="Raabiha Atelier Process"
                        class="w-full h-auto object-cover shadow-lg aspect-[4/3] md:aspect-auto">
                </div>

                <!-- Text (Right on Desktop, Bottom on Mobile) -->
                <div class="order-1 md:order-2 flex flex-col justify-center">
                    <p class="text-[#064e3b] text-[10px] font-mono uppercase tracking-[0.2em] mb-4">
                        {{ $aboutAtelierTag }}
                    </p>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif text-[#1c1c1a] leading-tight mb-6">
                        {!! nl2br(e($aboutAtelierTitle)) !!}
                    </h1>
                    <p class="text-[#615e57] text-sm md:text-base leading-relaxed max-w-lg">
                        {{ $aboutAtelierDescription }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Vision & Values Section -->
        <section class="max-w-7xl mx-auto px-6 lg:px-12 mb-24 md:mb-32">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16 lg:gap-24">

                <!-- Vision -->
                <div>
                    <p class="text-[#064e3b] text-[10px] font-mono uppercase tracking-[0.2em] mb-4">Visi Kami</p>
                    <h2 class="text-3xl md:text-4xl font-serif text-[#1c1c1a] leading-tight mb-6">
                        {{ $aboutVisionTitle }}
                    </h2>
                    <p class="text-[#615e57] text-sm md:text-base leading-relaxed">
                        {{ $aboutVisionDescription }}
                    </p>
                </div>

                <!-- Core Values -->
                <div class="flex flex-col justify-center mt-4 md:mt-0">
                    <p class="text-[#064e3b] text-[10px] font-mono uppercase tracking-[0.2em] mb-4 md:mb-6">
                        {{ $aboutValuesTitle }}
                    </p>
                    <div class="space-y-8">
                        @foreach($aboutValues as $val)
                            <div>
                                <h3 class="text-xl font-serif italic text-[#1c1c1a] mb-2">{{ $val['title'] }}</h3>
                                <p class="text-[#615e57] text-sm leading-relaxed">
                                    {{ $val['description'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- Timeline Section -->
        <section class="bg-[#f4f0eb] py-20 md:py-32">
            <div class="max-w-5xl mx-auto px-6 lg:px-12">

                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 md:mb-24 gap-4">
                    <h2 class="text-3xl md:text-4xl font-serif text-[#1c1c1a]">{{ $aboutTimelineTitle }}</h2>
                    <p
                        class="text-[#615e57] text-[10px] font-mono uppercase tracking-widest max-w-[200px] text-left md:text-right leading-loose">
                        {{ $aboutTimelineSubtitle }}
                    </p>
                </div>

                <!-- Timeline Container -->
                <div class="relative">
                    <!-- Vertical Line -->
                    <div class="absolute left-[11px] md:left-1/2 top-2 bottom-0 w-px bg-[#e5e2de] md:-translate-x-1/2">
                    </div>

                    <div class="space-y-16 md:space-y-24">
                        @foreach($aboutTimeline as $index => $item)
                            <!-- Alternate alignment in desktop -->
                            @php
                                $isEven = $index % 2 === 1;
                            @endphp
                            <div
                                class="relative flex flex-col md:flex-row items-start md:items-center justify-between group">
                                <!-- Dot -->
                                <div
                                    class="absolute {{ $isEven ? 'left-[3.5px] md:left-1/2 top-[3px]' : 'absolute left-[7px] md:left-1/2 top-2' }} md:top-1/2 {{ $isEven ? 'w-[15px] h-[15px]' : 'w-2 h-2' }} rounded-full bg-[#1c1c1a] flex items-center justify-center md:-translate-x-1/2 md:-translate-y-1/2 transition-all duration-300 group-hover:scale-110 ring-4 ring-[#f4f0eb]">
                                    @if($isEven)
                                        <div class="w-1.5 h-1.5 bg-[#f4f0eb] rounded-full"></div>
                                    @endif
                                </div>

                                <!-- Left Column -->
                                @if(!$isEven)
                                    <div class="w-full md:w-[45%] pl-10 md:pl-0 md:text-right mb-2 md:mb-0">
                                        <h3 class="text-xl font-serif text-[#1c1c1a] mb-2">{{ $item['title'] }}</h3>
                                        <p class="text-[#615e57] text-sm leading-relaxed">
                                            {{ $item['description'] }}
                                        </p>
                                    </div>
                                    <div class="w-full md:w-[45%] pl-10 md:pl-0 flex items-center md:justify-start">
                                        <span
                                            class="text-[#064e3b] font-mono text-sm tracking-widest bg-[#064e3b]/10 px-3 py-1 rounded-full">{{ $item['year'] }}</span>
                                    </div>
                                @else
                                    <div
                                        class="w-full md:w-[45%] pl-10 md:pl-0 flex items-center md:justify-end order-2 md:order-1 mt-3 md:mt-0">
                                        <span
                                            class="text-white font-mono text-sm tracking-widest bg-[#064e3b] px-3 py-1 rounded-full shadow-sm">{{ $item['year'] }}</span>
                                    </div>
                                    <div class="w-full md:w-[45%] pl-10 md:pl-0 md:text-left order-1 md:order-2">
                                        <h3 class="text-xl font-serif text-[#1c1c1a] mb-2">{{ $item['title'] }}</h3>
                                        <p class="text-[#615e57] text-sm leading-relaxed">
                                            {{ $item['description'] }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- Quote Section -->
        <section class="max-w-4xl mx-auto px-6 lg:px-12 py-24 md:py-32 text-center">
            <div
                class="inline-block bg-[#1c1c1a] text-white text-[9px] uppercase tracking-widest px-4 py-2 mb-8 shadow-sm">
                DARI HATI UNTUKMU
            </div>
            <h2
                class="text-2xl md:text-3xl lg:text-4xl font-serif text-[#1c1c1a] leading-snug max-w-3xl mx-auto italic">
                "{{ $aboutQuoteText }}"
            </h2>
        </section>

    </main>
</x-layouts.app>