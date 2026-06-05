@php
    $metaTitle = $page->meta_title ?: $page->title;
    $metaDescription = $page->meta_description ?: '';
@endphp

<x-layouts.app :title="$metaTitle" :description="$metaDescription">
<main class="site-main bg-[#fcf9f5] min-h-screen">
    
    @if(is_array($page->content) && count($page->content) > 0)
        @foreach($page->content as $block)
            @switch($block['type'])
                @case('hero')
                    <section class="relative bg-[#1c1c1a] text-[#fcf9f5] overflow-hidden py-24 md:py-32 lg:py-48">
                        @if(isset($block['data']['background_image']) && !empty($block['data']['background_image']))
                            <div class="absolute inset-0 opacity-40">
                                <img src="{{ url('/storage/' . $block['data']['background_image']) }}" alt="Hero Background" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="relative max-w-5xl mx-auto px-6 text-center z-10">
                            <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif leading-tight mb-6">{{ $block['data']['headline'] ?? '' }}</h1>
                            @if(isset($block['data']['subheadline']))
                                <p class="text-lg md:text-xl text-[#e5e2de] mb-10 max-w-2xl mx-auto">{{ $block['data']['subheadline'] }}</p>
                            @endif
                            @if(isset($block['data']['button_text']) && isset($block['data']['button_link']))
                                <a href="{{ $block['data']['button_link'] }}" class="inline-block bg-[#fcf9f5] text-[#1c1c1a] hover:bg-[#e5e2de] transition-colors duration-300 font-medium px-8 py-4 tracking-wide">
                                    {{ $block['data']['button_text'] }}
                                </a>
                            @endif
                        </div>
                    </section>
                    @break

                @case('features')
                    <section class="py-20 md:py-28 bg-white border-y border-[#e5e2de]">
                        <div class="max-w-6xl mx-auto px-6">
                            @if(isset($block['data']['items']) && is_array($block['data']['items']))
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 md:gap-8">
                                    @foreach($block['data']['items'] as $item)
                                        <div class="text-center md:text-left space-y-4">
                                            @if(isset($item['icon']) && !empty($item['icon']))
                                                <div class="inline-flex items-center justify-center w-12 h-12 bg-[#fcf9f5] text-[#1c1c1a] mb-2 rounded-full border border-[#e5e2de]">
                                                    @svg($item['icon'], 'w-6 h-6')
                                                </div>
                                            @endif
                                            <h3 class="text-xl font-serif text-[#1c1c1a]">{{ $item['title'] ?? '' }}</h3>
                                            <p class="text-[#615e57] leading-relaxed">{{ $item['description'] ?? '' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                    @break

                @case('testimonials')
                    <section class="py-20 md:py-28 bg-[#fcf9f5]">
                        <div class="max-w-6xl mx-auto px-6 text-center">
                            <h2 class="text-3xl md:text-4xl font-serif text-[#1c1c1a] mb-16">Apa Kata Pelanggan Kami</h2>
                            @if(isset($block['data']['items']) && is_array($block['data']['items']))
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    @foreach($block['data']['items'] as $item)
                                        <div class="bg-white p-8 border border-[#e5e2de] text-left">
                                            <p class="text-[#1c1c1a] text-lg md:text-xl font-serif italic mb-8 leading-relaxed">"{{ $item['quote'] ?? '' }}"</p>
                                            <div class="flex items-center gap-4">
                                                @if(isset($item['avatar']) && !empty($item['avatar']))
                                                    <img src="{{ url('/storage/' . $item['avatar']) }}" alt="{{ $item['name'] ?? '' }}" class="w-12 h-12 rounded-full object-cover">
                                                @else
                                                    <div class="w-12 h-12 rounded-full bg-[#e5e2de] flex items-center justify-center text-[#615e57] font-serif font-bold">
                                                        {{ substr($item['name'] ?? 'A', 0, 1) }}
                                                    </div>
                                                @endif
                                                <div class="text-sm font-medium text-[#1c1c1a] uppercase tracking-wide">{{ $item['name'] ?? '' }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                    @break

                @case('faq')
                    <section class="py-20 md:py-28 bg-white border-y border-[#e5e2de]">
                        <div class="max-w-3xl mx-auto px-6">
                            <h2 class="text-3xl md:text-4xl font-serif text-[#1c1c1a] mb-12 text-center">Pertanyaan Umum</h2>
                            @if(isset($block['data']['questions']) && is_array($block['data']['questions']))
                                <div class="space-y-6" x-data="{ active: null }">
                                    @foreach($block['data']['questions'] as $index => $faq)
                                        <div class="border-b border-[#e5e2de] pb-4">
                                            <button @click="active = (active === {{ $index }} ? null : {{ $index }})" class="flex justify-between items-center w-full text-left font-serif text-lg md:text-xl text-[#1c1c1a] focus:outline-none">
                                                <span>{{ $faq['question'] ?? '' }}</span>
                                                <svg class="w-5 h-5 text-[#615e57] transform transition-transform duration-300" :class="{ 'rotate-180': active === {{ $index }} }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="active === {{ $index }}" x-collapse class="mt-4 text-[#615e57] leading-relaxed">
                                                {{ $faq['answer'] ?? '' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                    @break

                @case('call_to_action')
                    <section class="py-24 md:py-32 bg-[#1c1c1a] text-[#fcf9f5] text-center px-6">
                        <div class="max-w-3xl mx-auto">
                            <h2 class="text-4xl md:text-5xl font-serif mb-6 leading-tight">{{ $block['data']['title'] ?? '' }}</h2>
                            @if(isset($block['data']['description']))
                                <p class="text-lg md:text-xl text-[#e5e2de] mb-10">{{ $block['data']['description'] }}</p>
                            @endif
                            @if(isset($block['data']['button_text']) && isset($block['data']['button_link']))
                                <a href="{{ $block['data']['button_link'] }}" class="inline-block bg-[#fcf9f5] text-[#1c1c1a] hover:bg-[#e5e2de] transition-colors duration-300 font-medium px-8 py-4 tracking-wide text-lg">
                                    {{ $block['data']['button_text'] }}
                                </a>
                            @endif
                        </div>
                    </section>
                    @break

            @endswitch
        @endforeach
    @else
        <section class="py-24 text-center max-w-3xl mx-auto px-6">
            <h1 class="text-4xl font-serif text-[#1c1c1a] mb-6">{{ $page->title }}</h1>
            <p class="text-[#615e57]">Halaman ini belum memiliki konten blok Builder.</p>
        </section>
    @endif

</main>
</x-layouts.app>
