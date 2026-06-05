<x-layouts.app :title="$page->title">
<main class="site-main bg-[#fcf9f5] min-h-screen pt-24 pb-20">
    
    <!-- Page Header -->
    <section class="max-w-4xl mx-auto px-6 text-center mb-16">
        <p class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4">Informasi</p>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif text-[#1c1c1a] leading-tight mb-6">
            {{ $page->title }}
        </h1>
        <p class="text-[#615e57] text-sm md:text-base">Terakhir diperbarui: {{ $page->updated_at->isoFormat('D MMMM Y') }}</p>
    </section>

    <!-- Page Content -->
    <section class="max-w-3xl mx-auto px-6">
        <div class="bg-white border border-[#e5e2de] p-8 md:p-12 shadow-sm text-[#1c1c1a] space-y-6 leading-relaxed prose prose-stone max-w-none">
            {!! $page->content !!}
        </div>
    </section>

</main>
</x-layouts.app>
