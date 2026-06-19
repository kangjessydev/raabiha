@php
    $metaTitle = $page->meta_title ?: $page->title;
    $metaDescription = $page->meta_description ?: '';
@endphp

<x-layouts.app :title="$metaTitle" :description="$metaDescription" :blank="true">
<main class="site-main bg-[#fcf9f5] min-h-screen">
    
    @if(is_array($page->content) && count($page->content) > 0)
        @foreach($page->content as $sectionBlock)
            @if($sectionBlock['type'] === 'section')
                @php
                    $secData = $sectionBlock['data'];
                    $bgStyle = isset($secData['bg_color']) ? "background-color: {$secData['bg_color']};" : "";
                    if(isset($secData['bg_image']) && !empty($secData['bg_image'])) {
                        $bgStyle .= " background-image: url('" . url('/storage/' . $secData['bg_image']) . "'); background-size: cover; background-position: center;";
                    }
                    $padding = $secData['padding_y'] ?? 'py-16';
                    $maxWidth = $secData['max_width'] ?? 'max-w-5xl';
                @endphp
                <section class="relative {{ $padding }} w-full" style="{{ $bgStyle }}">
                    @if(isset($secData['bg_image']) && !empty($secData['bg_image']))
                        <div class="absolute inset-0 bg-black/40 pointer-events-none"></div>
                    @endif
                    <div class="relative {{ $maxWidth }} mx-auto px-6 z-10 flex flex-col gap-6">
                        @if(isset($secData['widgets']) && is_array($secData['widgets']))
                            @foreach($secData['widgets'] as $widget)
                                @php $wData = $widget['data']; @endphp
                                @switch($widget['type'])
                                    @case('heading')
                                        <div class="flex w-full {{ str_replace('text-', 'justify-', str_replace('text-center', 'justify-center', str_replace('text-right', 'justify-end', str_replace('text-left', 'justify-start', $wData['text_align'] ?? 'text-center')))) }}">
                                            <{{ $wData['tag'] ?? 'h2' }} class="{{ $wData['font_family'] ?? 'font-serif' }} {{ $wData['font_size'] ?? 'text-4xl' }} {{ $wData['text_align'] ?? 'text-center' }}" style="color: {{ $wData['text_color'] ?? '#1c1c1a' }};">
                                                {{ $wData['text'] ?? '' }}
                                            </{{ $wData['tag'] ?? 'h2' }}>
                                        </div>
                                        @break
                                    
                                    @case('text')
                                        <div class="flex w-full {{ str_replace('text-', 'justify-', str_replace('text-center', 'justify-center', str_replace('text-right', 'justify-end', str_replace('text-left', 'justify-start', str_replace('text-justify', 'justify-center', $wData['text_align'] ?? 'text-center'))))) }}">
                                            <div class="prose max-w-none {{ $wData['font_size'] ?? 'text-base' }} {{ $wData['text_align'] ?? 'text-left' }}" style="color: {{ $wData['text_color'] ?? '#615e57' }};">
                                                {!! $wData['content'] ?? '' !!}
                                            </div>
                                        </div>
                                        @break
                                        
                                    @case('button')
                                        <div class="flex w-full {{ $wData['alignment'] ?? 'justify-center' }}">
                                            <a href="{{ $wData['url'] ?? '#' }}" class="inline-block transition-colors duration-300 font-medium px-8 py-4 tracking-wide border" style="background-color: {{ $wData['bg_color'] ?? '#1c1c1a' }}; color: {{ $wData['text_color'] ?? '#ffffff' }}; border-color: {{ $wData['bg_color'] ?? '#1c1c1a' }};">
                                                {{ $wData['text'] ?? 'Click Here' }}
                                            </a>
                                        </div>
                                        @break
                                        
                                    @case('image')
                                        <div class="flex w-full {{ $wData['alignment'] ?? 'justify-center' }}">
                                            <img src="{{ url('/storage/' . ($wData['image'] ?? '')) }}" style="max-width: {{ $wData['max_width'] ?? '100%' }};" alt="Widget Image">
                                        </div>
                                        @break
                                        
                                    @case('features')
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 md:gap-8 mt-4" style="color: {{ $wData['text_color'] ?? '#1c1c1a' }};">
                                            @foreach($wData['items'] ?? [] as $item)
                                                <div class="text-center md:text-left space-y-4">
                                                    @if(isset($item['icon']) && !empty($item['icon']))
                                                        <div class="inline-flex items-center justify-center w-12 h-12 bg-black/5 mb-2 rounded-full border border-black/10">
                                                            @svg($item['icon'], 'w-6 h-6')
                                                        </div>
                                                    @endif
                                                    <h3 class="text-xl font-serif">{{ $item['title'] ?? '' }}</h3>
                                                    <p class="leading-relaxed opacity-80">{{ $item['description'] ?? '' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break
                                        
                                    @case('testimonials')
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-4" style="color: {{ $wData['text_color'] ?? '#1c1c1a' }};">
                                            @foreach($wData['items'] ?? [] as $item)
                                                <div class="bg-black/5 p-8 border border-black/10 text-left rounded-sm backdrop-blur-sm">
                                                    <p class="text-lg md:text-xl font-serif italic mb-8 leading-relaxed opacity-90">"{{ $item['quote'] ?? '' }}"</p>
                                                    <div class="flex items-center gap-4">
                                                        @if(isset($item['avatar']) && !empty($item['avatar']))
                                                            <img src="{{ url('/storage/' . $item['avatar']) }}" alt="{{ $item['name'] ?? '' }}" class="w-12 h-12 rounded-full object-cover">
                                                        @else
                                                            <div class="w-12 h-12 rounded-full bg-black/10 flex items-center justify-center font-serif font-bold opacity-80">
                                                                {{ substr($item['name'] ?? 'A', 0, 1) }}
                                                            </div>
                                                        @endif
                                                        <div class="text-sm font-medium uppercase tracking-wide">{{ $item['name'] ?? '' }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break
                                        
                                    @case('faq')
                                        <div class="space-y-6 mt-4 w-full" x-data="{ active: null }" style="color: {{ $wData['text_color'] ?? '#1c1c1a' }};">
                                            @foreach($wData['questions'] ?? [] as $index => $faq)
                                                <div class="border-b border-black/10 pb-4">
                                                    <button @click="active = (active === {{ $index }} ? null : {{ $index }})" class="flex justify-between items-center w-full text-left font-serif text-lg md:text-xl focus:outline-none">
                                                        <span>{{ $faq['question'] ?? '' }}</span>
                                                        <svg class="w-5 h-5 transform transition-transform duration-300 opacity-70" :class="{ 'rotate-180': active === {{ $index }} }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>
                                                    <div x-show="active === {{ $index }}" x-collapse class="mt-4 leading-relaxed opacity-80">
                                                        {{ $faq['answer'] ?? '' }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break
                                        
                                @endswitch
                            @endforeach
                        @endif
                    </div>
                </section>
            @endif
        @endforeach
    @else
        <section class="py-24 text-center max-w-3xl mx-auto px-6">
            <h1 class="text-4xl font-serif text-[#1c1c1a] mb-6">{{ $page->title }}</h1>
            <p class="text-[#615e57]">Halaman ini belum memiliki konten blok Builder.</p>
        </section>
    @endif

</main>
</x-layouts.app>
