<x-layouts.app title="Galeri">
<main class="site-main bg-[#fcf9f5] min-h-screen pb-0">
    @php
        $rawContent = \App\Models\SiteSetting::where('key', 'gallery_content')->value('value');
        $blocks = $rawContent ? json_decode($rawContent, true) : [];
        if(!is_array($blocks)) $blocks = [];
        
        // Helper untuk render image Curator or Fallback
        $resolveImage = function($curatorId) {
            if ($curatorId) {
                $media = \Awcodes\Curator\Models\Media::find($curatorId);
                if ($media) return $media->url;
            }
            return 'https://placehold.co/800x800/e5e2de/615e57?text=Image+Not+Available';
        };
    @endphp

    @forelse($blocks as $block)
        @if($block['type'] === 'hero')
            <!-- Header Section -->
            <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-16 md:py-24 border-b border-[#e5e2de]">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-8">
                    <h1 class="text-5xl md:text-6xl lg:text-[80px] font-serif text-[#1c1c1a] leading-[0.9] tracking-tight uppercase">
                        {!! $block['data']['title'] ?? 'ARCHITECTURAL<br>MODESTY<br><span class="normal-case italic text-[#615e57] text-4xl md:text-5xl lg:text-[70px]">A Visual Study.</span>' !!}
                    </h1>
                    <div class="max-w-xs pb-2">
                        @if(!empty($block['data']['pre_title']))
                            <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mb-4">{{ $block['data']['pre_title'] }}</p>
                        @endif
                        @if(!empty($block['data']['description']))
                            <p class="text-[#615e57] text-sm leading-relaxed">
                                {{ $block['data']['description'] }}
                            </p>
                        @endif
                    </div>
                </div>
            </section>

        @elseif($block['type'] === 'masonry_layout')
            <!-- Masonry/Grid Gallery Section -->
            <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-12 md:py-20">
                <div class="space-y-6 md:space-y-10">
                    <!-- Row 1 -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-10">
                        <div class="md:col-span-7 relative group overflow-hidden">
                            <img src="{{ $resolveImage($block['data']['image_1'] ?? null) }}" alt="Gallery" class="w-full h-full object-cover aspect-[3/4] md:aspect-auto group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="md:col-span-5 flex flex-col gap-6 md:gap-10">
                            <div class="w-full h-1/2 overflow-hidden group">
                                <img src="{{ $resolveImage($block['data']['image_2'] ?? null) }}" alt="Gallery" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            </div>
                            <div class="w-full h-1/2 overflow-hidden group">
                                <img src="{{ $resolveImage($block['data']['image_3'] ?? null) }}" alt="Gallery" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 Full Width Landscape -->
                    <div class="w-full overflow-hidden group">
                        <img src="{{ $resolveImage($block['data']['image_4'] ?? null) }}" alt="Gallery" class="w-full h-auto object-cover aspect-[21/9] md:aspect-[3/1] group-hover:scale-105 transition-transform duration-700">
                    </div>

                    <!-- Row 3 -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-10">
                        <div class="flex flex-col gap-6">
                            <div class="overflow-hidden group h-[85%]">
                                <img src="{{ $resolveImage($block['data']['image_5'] ?? null) }}" alt="Gallery" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            </div>
                            <div class="p-2 border-l border-[#1c1c1a] pl-4 flex-1">
                                <h3 class="text-lg font-serif text-[#1c1c1a] mb-1 italic">{{ $block['data']['image_5_title'] ?? 'The Fluidity' }}</h3>
                                <p class="text-[#615e57] text-xs leading-relaxed">{{ $block['data']['image_5_desc'] ?? 'Elemen gerak dalam siluet hitam elegan.' }}</p>
                            </div>
                        </div>
                        <div class="overflow-hidden group h-full">
                            <img src="{{ $resolveImage($block['data']['image_6'] ?? null) }}" alt="Gallery" class="w-full h-full object-cover aspect-[4/5] md:aspect-auto group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="flex flex-col gap-6 md:gap-10 h-full">
                            <div class="flex-1 flex flex-col justify-center items-center text-center px-6 py-12 md:py-0 border border-[#e5e2de] bg-white aspect-[4/3] md:aspect-auto">
                                <p class="text-[#1c1c1a] font-serif text-xl leading-snug italic max-w-[200px]">
                                    "{{ $block['data']['quote_text'] ?? 'Kami tidak hanya merancang pakaian, kami mengkonstruksi ruang di sekitar tubuh.' }}"
                                </p>
                            </div>
                            <div class="flex-1 overflow-hidden group">
                                <img src="{{ $resolveImage($block['data']['image_7'] ?? null) }}" alt="Gallery" class="w-full h-full object-cover grayscale opacity-80 group-hover:scale-105 transition-transform duration-700">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        @elseif($block['type'] === 'statement')
            <!-- Statement Section -->
            <section class="bg-white py-24 md:py-40 text-center px-6">
                @if(!empty($block['data']['pre_title']))
                <p class="text-[#064e3b] text-[10px] font-mono tracking-[0.3em] uppercase mb-8">{{ $block['data']['pre_title'] }}</p>
                @endif
                <h2 class="text-4xl md:text-5xl lg:text-7xl font-serif text-[#1c1c1a] leading-[1.1] tracking-tight max-w-4xl mx-auto">
                    {!! $block['data']['title'] ?? 'DIMANA <span class="italic text-[#615e57]">STRUKTUR</span><br>BERTEMU DENGAN<br>KETENANGAN.' !!}
                </h2>
            </section>

        @elseif($block['type'] === 'feature_split')
            <!-- Bottom Feature Section -->
            <section class="bg-[#fcf9f5] max-w-[1440px] mx-auto px-6 lg:px-12 py-20 md:py-32">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-20 items-center">
                    <div class="order-2 md:order-1 flex flex-col justify-center max-w-md mx-auto md:mx-0">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-8 h-px bg-[#1c1c1a]"></div>
                            <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase">{{ $block['data']['pre_title'] ?? 'Materiality' }}</p>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-serif text-[#1c1c1a] mb-6">{{ $block['data']['title'] ?? 'Sentuhan yang Bercerita.' }}</h3>
                        <p class="text-[#615e57] text-sm leading-relaxed mb-10">
                            {{ $block['data']['description'] ?? 'Kami menggunakan tekstur yang merespons cahaya—menciptakan bayangan dramatis pada setiap lipatan.' }}
                        </p>
                        @if(!empty($block['data']['features']))
                        <div class="flex gap-12 border-t border-[#e5e2de] pt-6">
                            @foreach($block['data']['features'] as $feature)
                            <div>
                                <p class="text-[#1c1c1a] text-[10px] font-mono font-bold uppercase tracking-[0.1em] mb-1">{{ $feature['label'] }}</p>
                                <p class="text-[#615e57] text-sm italic font-serif">{{ $feature['value'] }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="order-1 md:order-2 overflow-hidden group">
                        <img src="{{ $resolveImage($block['data']['image'] ?? null) }}" alt="Feature" class="w-full h-auto aspect-[3/4] object-cover grayscale opacity-90 group-hover:scale-105 transition-transform duration-700">
                    </div>
                </div>
            </section>
        @endif
    @empty
        <!-- Default State if builder is empty -->
        <section class="py-24 text-center">
            <h2 class="text-2xl text-[#1c1c1a]">Konten Galeri belum dikonfigurasi.</h2>
        </section>
    @endforelse

</main>
</x-layouts.app>
