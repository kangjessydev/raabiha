<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Detail Blog" backUrl="/blog" transparent="true" :share="true" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pt-0 md:pt-0 pb-0">

        {{-- ===== HERO SECTION (Full Bleed on Mobile) ===== --}}
        <div class="relative w-full aspect-square md:aspect-[21/9] overflow-hidden">
            @if($post->image && $media = \Awcodes\Curator\Models\Media::find($post->image))
                <img src="{{ Storage::url($media->path) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            @else
                <img src="{{ asset('assets/images/gallery-3.png') }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            @endif
            {{-- Gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-black/10"></div>
            {{-- Text content pinned bottom --}}
            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-16 max-w-4xl">
                <p class="text-white/70 text-[9px] font-mono tracking-[0.2em] uppercase mb-3">
                    {{ $post->category ? $post->category->name : 'Uncategorized' }} &mdash; {{ $post->published_at ? $post->published_at->format('M d, Y') : 'Draft' }}
                </p>
                <h1 class="text-2xl md:text-5xl lg:text-7xl font-serif text-white mb-3 leading-tight">
                    {{ $post->title }}
                </h1>
                <p class="text-white/80 text-sm md:text-base leading-relaxed max-w-xl hidden md:block">
                    {{ $post->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 160) }}
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
                            <p class="text-[#1c1c1a] font-serif italic text-base md:text-lg">{{ $post->author ? $post->author->name : 'Raabiha Team' }}</p>
                        </div>
                        <div>
                            <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mb-1">Published</p>
                            <p class="text-[#615e57] text-sm">{{ $post->published_at ? $post->published_at->format('M d, Y') : 'Unknown' }}</p>
                        </div>
                        <div class="hidden md:block">
                            <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mb-2 md:mb-3">Share</p>
                            <div class="flex flex-row md:flex-col gap-4 md:gap-2">
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($post->title) }}" target="_blank" class="text-[#615e57] hover:text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest transition-colors">Twitter</a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}&title={{ urlencode($post->title) }}" target="_blank" class="text-[#615e57] hover:text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest transition-colors">LinkedIn</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Article Content --}}
                <article class="flex-1 max-w-3xl prose prose-neutral prose-p:text-[#615e57] prose-p:leading-relaxed prose-headings:font-serif prose-headings:text-[#1c1c1a] prose-headings:font-normal prose-a:text-[#064e3b] prose-a:no-underline hover:prose-a:underline">
                    
                    {!! $post->content !!}
                    
                    {{-- Article Footer (Share & Comments UI) --}}
                    <div class="mt-16 md:mt-24 pt-8 md:pt-12 border-t border-[#e5e2de] not-prose">
                        {{-- Share Section (Bottom) --}}
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-16">
                            <div>
                                <p class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-3">Share This Article</p>
                                <div class="flex gap-6">
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($post->title) }}" target="_blank" class="text-[#615e57] hover:text-[#064e3b] text-sm font-medium transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                        Twitter
                                    </a>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" class="text-[#615e57] hover:text-[#064e3b] text-sm font-medium transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        Facebook
                                    </a>
                                    <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' - ' . request()->fullUrl()) }}" target="_blank" class="text-[#615e57] hover:text-[#064e3b] text-sm font-medium transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Comments UI --}}
                        <div class="mt-8" id="comments-section">
                            <h3 class="text-2xl font-serif text-[#1c1c1a] mb-8">Comments ({{ $comments->count() + $comments->sum(fn($c) => $c->replies->count()) }})</h3>
                            
                            @if (session()->has('message'))
                                <div class="bg-[#e8f5e9] text-[#064e3b] p-4 mb-8 text-sm font-mono border border-[#064e3b]/20">
                                    {{ session('message') }}
                                </div>
                            @endif

                            {{-- Comment Form --}}
                            @if(!$replyTo)
                                <form wire:submit="postComment" class="mb-12">
                                    <div class="mb-6">
                                        <label class="block text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-2">Leave a thought</label>
                                        <textarea wire:model="commentContent" required rows="4" placeholder="Share your perspective on this piece..." class="w-full bg-transparent border border-[#d1cec9] p-4 text-sm text-[#1c1c1a] placeholder:text-[#a3a09b] focus:outline-none focus:border-[#064e3b] focus:ring-1 focus:ring-[#064e3b] transition-all resize-none"></textarea>
                                        @error('commentContent') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    @guest
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                        <div>
                                            <input wire:model="name" required type="text" placeholder="Name" class="w-full bg-transparent border-b border-[#d1cec9] py-3 text-sm text-[#1c1c1a] placeholder:text-[#a3a09b] focus:outline-none focus:border-[#064e3b] transition-colors">
                                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <input wire:model="email" required type="email" placeholder="Email" class="w-full bg-transparent border-b border-[#d1cec9] py-3 text-sm text-[#1c1c1a] placeholder:text-[#a3a09b] focus:outline-none focus:border-[#064e3b] transition-colors">
                                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    @else
                                    <div class="mb-6 text-sm text-[#615e57] font-mono">
                                        Commenting as <span class="text-[#1c1c1a] font-bold">{{ auth()->user()->name }}</span>
                                    </div>
                                    @endguest

                                    <button type="submit" class="bg-[#1c1c1a] text-white px-8 py-4 text-[11px] font-mono tracking-widest uppercase hover:bg-[#064e3b] transition-colors">
                                        Post Comment
                                    </button>
                                </form>
                            @endif

                            {{-- Comments List --}}
                            <div class="space-y-8">
                                @forelse($comments as $comment)
                                    <div class="border-t border-[#e5e2de] pt-8">
                                        <div class="flex items-start gap-4 mb-4">
                                            <div class="w-10 h-10 bg-[#f0ede9] rounded-full flex items-center justify-center text-[#064e3b] font-serif font-bold shrink-0">
                                                {{ substr($comment->guest_name ?? 'A', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="text-[#1c1c1a] font-bold text-sm">{{ $comment->guest_name }}</h4>
                                                    @if($comment->user_id && current(array_filter($post->author->toArray())) && $comment->user_id == $post->user_id)
                                                        <span class="bg-[#064e3b] text-white text-[8px] px-2 py-0.5 uppercase tracking-widest font-mono">Author</span>
                                                    @endif
                                                </div>
                                                <p class="text-[#a3a09b] text-[10px] font-mono mt-1">{{ $comment->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <p class="text-[#615e57] text-sm leading-relaxed mb-3">
                                            {{ $comment->content }}
                                        </p>
                                        @if($comment->is_edited_by_admin)
                                            <div class="mb-3 text-[9px] font-mono text-[#064e3b] bg-[#e8f5e9] px-2 py-1 inline-block">
                                                * Diubah oleh Admin: {{ $comment->admin_edit_reason }}
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-4 mb-4">
                                            <button wire:click="setReply({{ $comment->id }})" class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest border-b border-[#1c1c1a] hover:text-[#064e3b] hover:border-[#064e3b] transition-colors">Reply</button>
                                        </div>

                                        {{-- Render Form Balasan tepat di bawah komentar yang sedang di-reply --}}
                                        @if($replyTo === $comment->id)
                                            <div class="mt-4 mb-6 bg-[#fcf9f5] border border-[#e5e2de] p-6">
                                                <div class="flex items-center justify-between mb-4 text-xs font-mono text-[#615e57]">
                                                    <span>Membalas komentar {{ $comment->guest_name }}</span>
                                                    <button type="button" wire:click="cancelReply" class="text-red-500 hover:text-red-700">&times; Batal</button>
                                                </div>
                                                <form wire:submit="postComment">
                                                    <div class="mb-4">
                                                        <textarea wire:model="commentContent" required rows="3" placeholder="Tulis balasan Anda..." class="w-full bg-transparent border border-[#d1cec9] p-3 text-sm text-[#1c1c1a] placeholder:text-[#a3a09b] focus:outline-none focus:border-[#064e3b] focus:ring-1 focus:ring-[#064e3b] transition-all resize-none"></textarea>
                                                        @error('commentContent') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                    </div>
                                                    
                                                    @guest
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                        <div>
                                                            <input wire:model="name" required type="text" placeholder="Nama" class="w-full bg-transparent border-b border-[#d1cec9] py-2 text-sm text-[#1c1c1a] placeholder:text-[#a3a09b] focus:outline-none focus:border-[#064e3b] transition-colors">
                                                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                        </div>
                                                        <div>
                                                            <input wire:model="email" required type="email" placeholder="Email" class="w-full bg-transparent border-b border-[#d1cec9] py-2 text-sm text-[#1c1c1a] placeholder:text-[#a3a09b] focus:outline-none focus:border-[#064e3b] transition-colors">
                                                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    @else
                                                    <div class="mb-4 text-xs text-[#615e57] font-mono">
                                                        Membalas sebagai <span class="text-[#1c1c1a] font-bold">{{ auth()->user()->name }}</span>
                                                    </div>
                                                    @endguest

                                                    <button type="submit" class="bg-[#1c1c1a] text-white px-6 py-3 text-[10px] font-mono tracking-widest uppercase hover:bg-[#064e3b] transition-colors">
                                                        Kirim Balasan
                                                    </button>
                                                </form>
                                            </div>
                                        @endif

                                        {{-- Replies --}}
                                        @if($comment->replies->count() > 0)
                                            <div class="mt-6 ml-8 md:ml-14 space-y-6 border-l-2 border-[#f0ede9] pl-6 md:pl-8">
                                                @foreach($comment->replies as $reply)
                                                    <div>
                                                        <div class="flex items-start gap-3 mb-3">
                                                            <div class="w-8 h-8 bg-[#f0ede9] rounded-full flex items-center justify-center text-[#064e3b] font-serif font-bold shrink-0 text-xs">
                                                                {{ substr($reply->guest_name ?? 'A', 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <div class="flex items-center gap-2">
                                                                    <h4 class="text-[#1c1c1a] font-bold text-xs">{{ $reply->guest_name }}</h4>
                                                                    @if($reply->user_id && current(array_filter($post->author->toArray())) && $reply->user_id == $post->user_id)
                                                                        <span class="bg-[#064e3b] text-white text-[8px] px-2 py-0.5 uppercase tracking-widest font-mono">Author</span>
                                                                    @endif
                                                                </div>
                                                                <p class="text-[#a3a09b] text-[9px] font-mono mt-0.5">{{ $reply->created_at->diffForHumans() }}</p>
                                                            </div>
                                                        </div>
                                                        <p class="text-[#615e57] text-xs leading-relaxed">
                                                            {{ $reply->content }}
                                                        </p>
                                                        @if($reply->is_edited_by_admin)
                                                            <div class="mt-2 mb-1 text-[9px] font-mono text-[#064e3b] bg-[#e8f5e9] px-2 py-1 inline-block">
                                                                * Diubah oleh Admin: {{ $reply->admin_edit_reason }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    {{-- Dummy Empty State --}}
                                    <div class="py-12 text-center border-t border-[#e5e2de]">
                                        <p class="text-[#615e57] text-sm italic font-serif">Be the first to share your thoughts.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
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
        @if($relatedPosts->count() > 0)
        <section class="bg-[#f0ede9] py-16 md:py-32 px-6 lg:px-12">
            <div class="max-w-[1440px] mx-auto">
                <p class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-8 md:mb-12 text-center border-b border-[#d1cec9] pb-4 max-w-xs mx-auto">
                    Continue Reading
                </p>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-12">
                    @foreach($relatedPosts as $index => $related)
                    <article class="group cursor-pointer {{ $index == 2 ? 'hidden md:block' : '' }}">
                        <a href="{{ url('/blog/' . $related->slug) }}" wire:navigate class="block">
                            <div class="w-full aspect-[4/5] overflow-hidden mb-4 md:mb-6">
                                @if($related->image && $media = \Awcodes\Curator\Models\Media::find($related->image))
                                    <img src="{{ Storage::url($media->path) }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                @else
                                    @php
                                        $fallbackImg = ['blog-white-suit.png', 'blog-coat.png', 'blog-pool.png'][$index % 3];
                                    @endphp
                                    <img src="{{ asset('assets/images/' . $fallbackImg) }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                @endif
                            </div>
                            <p class="text-[#064e3b] text-[9px] font-mono tracking-[0.2em] uppercase mb-2">{{ $related->category ? $related->category->name : 'Uncategorized' }}</p>
                            <h3 class="text-base md:text-2xl font-serif text-[#1c1c1a] mb-2 md:mb-3 group-hover:text-[#064e3b] transition-colors leading-snug">{{ $related->title }}</h3>
                            <p class="text-[#615e57] text-xs leading-relaxed mb-4 line-clamp-2 hidden md:block">
                                {{ $related->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($related->content), 80) }}
                            </p>
                        </a>
                    </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

    </main>
    </div>
</div>
