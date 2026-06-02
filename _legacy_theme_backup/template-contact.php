<?php
/**
 * Template Name: Lokasi & Kontak
 *
 * @package RaabihaTheme
 */

get_header();
?>

<main class="site-main bg-[#fcf9f5] min-h-screen pb-0">
    
    <!-- Hero Section -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-12 md:py-20">
        <div class="relative w-full aspect-[4/3] md:aspect-[21/9] overflow-hidden">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/contact-hero.png" alt="Raabiha Store Location" class="w-full h-full object-cover">
            
            <!-- Locations Badge -->
            <div class="absolute bottom-0 left-0 bg-white px-8 py-6 max-w-sm hidden md:block">
                <h1 class="text-4xl font-serif text-[#1c1c1a] mb-2">LOCATIONS</h1>
                <p class="text-[9px] font-mono tracking-[0.2em] uppercase text-[#615e57]">
                    RAABIHA <span class="mx-2">|</span> ARCHITECTURAL MODESTY
                </p>
            </div>
            <!-- Mobile Badge -->
            <div class="absolute bottom-0 left-0 bg-white px-6 py-4 max-w-[80%] md:hidden">
                <h1 class="text-3xl font-serif text-[#1c1c1a] mb-1">LOCATIONS</h1>
                <p class="text-[8px] font-mono tracking-[0.2em] uppercase text-[#615e57]">
                    RAABIHA | ARCHITECTURAL MODESTY
                </p>
            </div>
        </div>
    </section>

    <!-- Two Column Section -->
    <section class="max-w-7xl mx-auto px-6 lg:px-12 py-12 md:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-32">
            
            <!-- Left Column: Locations -->
            <div class="space-y-16">
                <!-- Location 1 -->
                <div>
                    <div class="inline-block bg-[#1c1c1a] text-white text-[9px] uppercase tracking-[0.2em] px-4 py-1.5 mb-6">
                        FLAGSHIP STORE #1
                    </div>
                    <h2 class="text-3xl font-serif text-[#1c1c1a] mb-6">Jakarta, Indonesia</h2>
                    
                    <p class="text-[#615e57] text-sm leading-relaxed mb-8 max-w-sm">
                        SCBD District 8, Treasury Tower Level 12<br>
                        Sudirman Central Business District, Jakarta Selatan
                    </p>
                    
                    <div class="mb-6">
                        <h3 class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-2 font-bold">Hours</h3>
                        <p class="text-[#615e57] text-sm leading-relaxed">
                            Mon — Sat: 10:00 — 20:00<br>
                            Sun: 11:00 — 18:00
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-2 font-bold">Contact</h3>
                        <p class="text-[#615e57] text-sm leading-relaxed">
                            +62 21 555 0192<br>
                            jakarta@raabiha.com
                        </p>
                    </div>
                </div>

                <!-- Location 2 -->
                <div>
                    <div class="inline-block bg-[#e5e2de] text-[#1c1c1a] text-[9px] uppercase tracking-[0.2em] px-4 py-1.5 mb-6 font-medium">
                        FLAGSHIP STORE #2
                    </div>
                    <h2 class="text-3xl font-serif text-[#1c1c1a] mb-6">London, UK</h2>
                    
                    <p class="text-[#615e57] text-sm leading-relaxed mb-8 max-w-sm">
                        12 Savile Row, Mayfair<br>
                        London W1S 3PQ, United Kingdom
                    </p>
                    
                    <div class="mb-6">
                        <h3 class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-2 font-bold">Hours</h3>
                        <p class="text-[#615e57] text-sm leading-relaxed">
                            Mon — Fri: 09:00 — 19:00<br>
                            Sat — Sun: Closed
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-2 font-bold">Contact</h3>
                        <p class="text-[#615e57] text-sm leading-relaxed">
                            +44 20 7946 0148<br>
                            london@raabiha.com
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Inquiries -->
            <div>
                <h2 class="text-4xl font-serif text-[#1c1c1a] mb-12">Inquiries</h2>
                
                <form class="space-y-10">
                    <!-- Name -->
                    <div class="flex flex-col">
                        <label for="name" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">Full Name</label>
                        <input type="text" id="name" placeholder="ENTER YOUR NAME" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors rounded-none">
                    </div>
                    
                    <!-- Email -->
                    <div class="flex flex-col">
                        <label for="email" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">Email Address</label>
                        <input type="email" id="email" placeholder="EMAIL@EXAMPLE.COM" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors rounded-none">
                    </div>
                    
                    <!-- Subject -->
                    <div class="flex flex-col relative">
                        <label for="subject" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">Subject</label>
                        <select id="subject" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] transition-colors appearance-none rounded-none cursor-pointer">
                            <option>GENERAL INQUIRY</option>
                            <option>WHOLESALE</option>
                            <option>PRESS & MEDIA</option>
                        </select>
                        <!-- Custom Select Arrow -->
                        <div class="absolute right-0 bottom-4 pointer-events-none text-[#615e57]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    
                    <!-- Message -->
                    <div class="flex flex-col">
                        <label for="message" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold">Message</label>
                        <textarea id="message" rows="4" placeholder="HOW CAN WE ASSIST YOU?" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors resize-none rounded-none"></textarea>
                    </div>
                    
                    <!-- Submit -->
                    <button type="button" class="w-full bg-[#064e3b] text-white text-[11px] font-mono uppercase tracking-[0.2em] py-5 hover:bg-[#1c1c1a] transition-colors mt-4">
                        Kirim Pesan
                    </button>
                </form>
            </div>
            
        </div>
    </section>

    <!-- Map Frame Section -->
    <section class="max-w-7xl mx-auto px-6 lg:px-12 py-12 md:py-20 mb-8 md:mb-16">
        <div class="w-full aspect-[4/3] md:aspect-[21/9] bg-[#e5e2de] flex items-center justify-center shadow-sm relative overflow-hidden group">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.856588478794!2d107.59155307499749!3d-7.026138092975612!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68ebe9654e5b13%3A0x9da124ecee2a6509!2sRaabiha!5e0!3m2!1sid!2sid!4v1779961654809!5m2!1sid!2sid" class="absolute inset-0 w-full h-full border-0 grayscale opacity-80 hover:grayscale-0 hover:opacity-100 transition-all duration-700" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

</main>

<?php get_footer(); ?>
