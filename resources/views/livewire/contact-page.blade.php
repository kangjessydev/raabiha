<main class="site-main bg-[#fcf9f5] min-h-screen pb-0">
    @php
        // Helper untuk render image Curator or Fallback
        $resolveImage = function($curatorId, $fallback = null) {
            if ($curatorId) {
                $media = \Awcodes\Curator\Models\Media::find($curatorId);
                if ($media) return $media->url;
            }
            return $fallback ?: 'https://placehold.co/800x800/e5e2de/615e57?text=Image+Not+Available';
        };

        // Load Settings
        $contactHeroImage = \App\Models\SiteSetting::where('key', 'contact_hero_image')->value('value');
        $contactTitle = \App\Models\SiteSetting::where('key', 'contact_title')->value('value') ?: 'LOCATIONS';
        $contactSubtitle = \App\Models\SiteSetting::where('key', 'contact_subtitle')->value('value') ?: 'RAABIHA | ARCHITECTURAL MODESTY';

        $rawLocations = \App\Models\SiteSetting::where('key', 'contact_locations')->value('value');
        $locations = $rawLocations ? json_decode($rawLocations, true) : [];
        if (!is_array($locations) || empty($locations)) {
            $locations = [
                [
                    'badge' => 'FLAGSHIP STORE #1',
                    'name' => 'Jakarta, Indonesia',
                    'address' => "SCBD District 8, Treasury Tower Level 12\nSudirman Central Business District, Jakarta Selatan",
                    'hours' => "Mon — Sat: 10:00 — 20:00\nSun: 11:00 — 18:00",
                    'contact' => "+62 21 555 0192\njakarta@raabiha.com"
                ],
                [
                    'badge' => 'FLAGSHIP STORE #2',
                    'name' => 'London, UK',
                    'address' => "12 Savile Row, Mayfair\nLondon W1S 3PQ, United Kingdom",
                    'hours' => "Mon — Fri: 09:00 — 19:00\nSat — Sun: Closed",
                    'contact' => "+44 20 7946 0148\nlondon@raabiha.com"
                ]
            ];
        }

        $contactGmaps = \App\Models\SiteSetting::where('key', 'contact_gmaps_embed')->value('value') ?: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.856588478794!2d107.59155307499749!3d-7.026138092975612!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68ebe9654e5b13%3A0x9da124ecee2a6509!2sRaabiha!5e0!3m2!1sid!2sid!4v1779961654809!5m2!1sid!2sid';
        if (preg_match('/src="([^"]+)"/', $contactGmaps, $matches)) {
            $contactGmaps = $matches[1];
        }
    @endphp

    <!-- Hero Section -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-12 md:py-20">
        <div class="relative w-full aspect-[4/3] md:aspect-[21/9] overflow-hidden">
            <img src="{{ $resolveImage($contactHeroImage, asset('assets/images/contact-hero.webp')) }}" alt="Raabiha Store Location" class="w-full h-full object-cover">
            
            <!-- Locations Badge -->
            <div class="absolute bottom-0 left-0 bg-white px-8 py-6 max-w-sm hidden md:block">
                <h1 class="text-4xl font-serif text-[#1c1c1a] mb-2">{{ $contactTitle }}</h1>
                <p class="text-[9px] font-mono tracking-[0.2em] uppercase text-[#615e57]">
                    {{ $contactSubtitle }}
                </p>
            </div>
            <!-- Mobile Badge -->
            <div class="absolute bottom-0 left-0 bg-white px-6 py-4 max-w-[80%] md:hidden">
                <h1 class="text-3xl font-serif text-[#1c1c1a] mb-1">{{ $contactTitle }}</h1>
                <p class="text-[8px] font-mono tracking-[0.2em] uppercase text-[#615e57]">
                    {{ $contactSubtitle }}
                </p>
            </div>
        </div>
    </section>

    <!-- Two Column Section -->
    <section class="max-w-7xl mx-auto px-6 lg:px-12 py-12 md:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-32">
            
            <!-- Left Column: Locations -->
            <div class="space-y-16">
                @foreach($locations as $index => $loc)
                    <div>
                        <div class="inline-block {{ $index === 0 ? 'bg-[#1c1c1a] text-white' : 'bg-[#e5e2de] text-[#1c1c1a]' }} text-[9px] uppercase tracking-[0.2em] px-4 py-1.5 mb-6 font-medium">
                            {{ $loc['badge'] }}
                        </div>
                        <h2 class="text-3xl font-serif text-[#1c1c1a] mb-6">{{ $loc['name'] }}</h2>
                        
                        <p class="text-[#615e57] text-sm leading-relaxed mb-8 max-w-sm">
                            {!! nl2br(e($loc['address'])) !!}
                        </p>
                        
                        <div class="mb-6">
                            <h3 class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-2 font-bold">Hours</h3>
                            <p class="text-[#615e57] text-sm leading-relaxed">
                                {!! nl2br(e($loc['hours'])) !!}
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-2 font-bold">Contact</h3>
                            <p class="text-[#615e57] text-sm leading-relaxed">
                                {!! nl2br(e($loc['contact'])) !!}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right Column: Inquiries -->
            <div>
                <h2 class="text-4xl font-serif text-[#1c1c1a] mb-8">Inquiries</h2>
                
                @if (session()->has('success'))
                    <div class="bg-[#064e3b]/10 border border-[#064e3b]/20 text-[#064e3b] px-6 py-4 mb-8">
                        <p class="font-sans text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="bg-red-900/10 border border-red-900/20 text-red-900 px-6 py-4 mb-8">
                        <p class="font-sans text-sm">{{ session('error') }}</p>
                    </div>
                @endif
                
                <form wire:submit="submit" class="space-y-10">
                    <!-- Name -->
                    <div class="flex flex-col">
                        <label for="name" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">Full Name</label>
                        <input type="text" id="name" wire:model="name" placeholder="ENTER YOUR NAME" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors rounded-none">
                        @error('name') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Preferred Channel (Inline Radio Selector) -->
                    <div class="flex flex-col">
                        <label class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">Pilih Metode Balasan</label>
                        <div class="flex gap-8">
                            <label class="flex items-center gap-2 cursor-pointer font-sans text-sm text-[#1c1c1a]">
                                <input type="radio" wire:model.live="channel" value="email" class="w-4 h-4 text-[#064e3b] border-[#e5e2de] focus:ring-[#064e3b]">
                                <span>Alamat Email</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer font-sans text-sm text-[#1c1c1a]">
                                <input type="radio" wire:model.live="channel" value="whatsapp" class="w-4 h-4 text-[#064e3b] border-[#e5e2de] focus:ring-[#064e3b]">
                                <span>Nomor WhatsApp</span>
                            </label>
                        </div>
                    </div>

                    <!-- Contact Details (Dynamic Input) -->
                    <div>
                        @if($channel === 'email')
                            <div class="flex flex-col">
                                <label for="email" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">
                                    Email Address <span class="text-red-500 font-sans text-sm">*</span>
                                </label>
                                <input type="email" id="email" wire:model="email" placeholder="EMAIL@EXAMPLE.COM" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors rounded-none">
                                @error('email') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="flex flex-col">
                                <label for="phone" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">
                                    WhatsApp Number <span class="text-red-500 font-sans text-sm">*</span>
                                </label>
                                <input type="text" id="phone" wire:model="phone" placeholder="+62..." class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors rounded-none">
                                @error('phone') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                    
                    <!-- Subject -->
                    <div class="flex flex-col relative">
                        <label for="subject" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">Subject</label>
                        <select id="subject" wire:model="subject" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] transition-colors appearance-none rounded-none cursor-pointer">
                            <option value="">SELECT SUBJECT</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-0 bottom-4 pointer-events-none text-[#615e57]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        @error('subject') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Message -->
                    <div class="flex flex-col">
                        <label for="message" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">Message</label>
                        <textarea id="message" wire:model="message" rows="4" placeholder="HOW CAN WE ASSIST YOU?" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors resize-none rounded-none"></textarea>
                        @error('message') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" class="w-full bg-[#064e3b] text-white text-[11px] font-mono uppercase tracking-[0.2em] py-5 hover:bg-[#1c1c1a] transition-colors mt-4">
                        <span wire:loading.remove wire:target="submit">Kirim Pesan</span>
                        <span wire:loading wire:target="submit">Memproses...</span>
                    </button>
                </form>
            </div>
            
        </div>
    </section>

    <!-- Map Frame Section -->
    <section class="max-w-7xl mx-auto px-6 lg:px-12 py-12 md:py-20 mb-8 md:mb-16">
        <div class="w-full aspect-[4/3] md:aspect-[21/9] bg-[#e5e2de] flex items-center justify-center shadow-sm relative overflow-hidden group">
            <iframe src="{{ $contactGmaps }}" class="absolute inset-0 w-full h-full border-0 grayscale opacity-80 hover:grayscale-0 hover:opacity-100 transition-all duration-700" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

</main>
