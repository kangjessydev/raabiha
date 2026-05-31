<?php
/**
 * Template Name: About Page
 *
 * @package RaabihaTheme
 */

get_header();
?>

<main class="site-main bg-[#fcf9f5] min-h-screen pt-12 md:pt-24 pb-0">
    
    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-6 lg:px-12 mb-24 md:mb-32">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20 items-center">
            <!-- Image (Left on Desktop, Top on Mobile) -->
            <div class="relative order-2 md:order-1">
                <div class="absolute top-4 left-4 bg-[#1c1c1a] text-white text-[9px] uppercase tracking-widest px-3 py-1.5 z-10">
                    Atelier Process
                </div>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-atelier.png" alt="Raabiha Atelier Process" class="w-full h-auto object-cover shadow-lg aspect-[4/3] md:aspect-auto">
            </div>
            
            <!-- Text (Right on Desktop, Bottom on Mobile) -->
            <div class="order-1 md:order-2 flex flex-col justify-center">
                <p class="text-[#064e3b] text-[10px] font-mono uppercase tracking-[0.2em] mb-4">Filosofi Brand</p>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif text-[#1c1c1a] leading-tight mb-6">
                    Arsitektur dalam <br> <span class="italic text-[#064e3b]">Kesopanan.</span>
                </h1>
                <p class="text-[#615e57] text-sm md:text-base leading-relaxed max-w-lg">
                    Raabiha mendefinisikan kembali modest fashion melalui lensa minimalisme struktural. Setiap jahitan adalah komitmen terhadap presisi intelektual dan estetika urban yang mengakar pada nilai-nilai tradisi yang luhur.
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
                    Menjadi Mercusuar Global untuk Estetika Modest Modern.
                </h2>
                <p class="text-[#615e57] text-sm md:text-base leading-relaxed">
                    Kami melihat masa depan di mana busana santun bukan hanya tentang penutupan, melainkan tentang perayaan keberanian artistik dan integritas struktural.
                </p>
            </div>

            <!-- Core Values -->
            <div class="flex flex-col justify-center mt-4 md:mt-0">
                <p class="text-[#064e3b] text-[10px] font-mono uppercase tracking-[0.2em] mb-4 md:mb-6">Nilai Inti</p>
                <div class="space-y-8">
                    <div>
                        <h3 class="text-xl font-serif italic text-[#1c1c1a] mb-2">Keahlian Tanpa Kompromi</h3>
                        <p class="text-[#615e57] text-sm leading-relaxed">
                            Memberdayakan pengrajin lokal untuk menghasilkan kualitas haute couture yang dapat diakses secara global.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-xl font-serif italic text-[#1c1c1a] mb-2">Evolusi Berkelanjutan</h3>
                        <p class="text-[#615e57] text-sm leading-relaxed">
                            Inovasi terus-menerus dalam penggunaan kain ramah lingkungan dan teknik konstruksi tanpa limbah.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="bg-[#f4f0eb] py-20 md:py-32">
        <div class="max-w-5xl mx-auto px-6 lg:px-12">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 md:mb-24 gap-4">
                <h2 class="text-3xl md:text-4xl font-serif text-[#1c1c1a]">Perjalanan Kami</h2>
                <p class="text-[#615e57] text-[10px] font-mono uppercase tracking-widest max-w-[200px] text-left md:text-right leading-loose">
                    Evolusi dari atelier kecil ke pemimpin global.
                </p>
            </div>

            <!-- Timeline Container -->
            <div class="relative">
                <!-- Vertical Line -->
                <div class="absolute left-[11px] md:left-1/2 top-2 bottom-0 w-px bg-[#e5e2de] md:-translate-x-1/2"></div>
                
                <div class="space-y-16 md:space-y-24">
                    
                    <!-- Item 1 -->
                    <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between group">
                        <!-- Dot -->
                        <div class="absolute left-[7px] md:left-1/2 top-2 md:top-1/2 w-2 h-2 rounded-full bg-[#1c1c1a] md:-translate-x-1/2 md:-translate-y-1/2 transition-all duration-300 group-hover:scale-150 ring-4 ring-[#f4f0eb]"></div>
                        
                        <!-- Content Left (Mobile: full width with pl-10) -->
                        <div class="w-full md:w-[45%] pl-10 md:pl-0 md:text-right mb-2 md:mb-0">
                            <h3 class="text-xl font-serif text-[#1c1c1a] mb-2">Awal Mula</h3>
                            <p class="text-[#615e57] text-sm leading-relaxed">
                                Didirikan sebagai atelier kecil di jantung Jakarta, berfokus pada pesanan kustom terbatas dengan material premium.
                            </p>
                        </div>
                        <!-- Year Right -->
                        <div class="w-full md:w-[45%] pl-10 md:pl-0 flex items-center md:justify-start">
                            <span class="text-[#064e3b] font-mono text-sm tracking-widest bg-[#064e3b]/10 px-3 py-1 rounded-full">2018</span>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between group">
                        <!-- Dot -->
                        <div class="absolute left-[7px] md:left-1/2 top-2 md:top-1/2 w-2 h-2 rounded-full bg-[#1c1c1a] md:-translate-x-1/2 md:-translate-y-1/2 transition-all duration-300 group-hover:scale-150 ring-4 ring-[#f4f0eb]"></div>
                        
                        <!-- Year Left -->
                        <div class="w-full md:w-[45%] pl-10 md:pl-0 flex items-center md:justify-end order-2 md:order-1 mt-3 md:mt-0">
                            <span class="text-[#064e3b] font-mono text-sm tracking-widest bg-[#064e3b]/10 px-3 py-1 rounded-full">2020</span>
                        </div>
                        <!-- Content Right -->
                        <div class="w-full md:w-[45%] pl-10 md:pl-0 md:text-left order-1 md:order-2">
                            <h3 class="text-xl font-serif text-[#1c1c1a] mb-2">Ekspansi Regional</h3>
                            <p class="text-[#615e57] text-sm leading-relaxed">
                                Meluncurkan koleksi Ready-to-Wear pertama yang langsung mendapat pengakuan di Asia Tenggara karena siluetnya yang unik.
                            </p>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between group">
                        <!-- Dot (Active/Green) -->
                        <div class="absolute left-[3.5px] md:left-1/2 top-[3px] md:top-1/2 w-[15px] h-[15px] rounded-full bg-[#064e3b] flex items-center justify-center md:-translate-x-1/2 md:-translate-y-1/2 transition-all duration-300 ring-4 ring-[#f4f0eb]">
                            <div class="w-1.5 h-1.5 bg-[#f4f0eb] rounded-full"></div>
                        </div>
                        
                        <!-- Content Left -->
                        <div class="w-full md:w-[45%] pl-10 md:pl-0 md:text-right mb-2 md:mb-0">
                            <h3 class="text-xl font-serif text-[#1c1c1a] mb-2">Butik Global</h3>
                            <p class="text-[#615e57] text-sm leading-relaxed">
                                Membuka flagship store digital dan fisik pertama di Dubai dan London, mengukuhkan posisi sebagai pemimpin modest luxury.
                            </p>
                        </div>
                        <!-- Year Right -->
                        <div class="w-full md:w-[45%] pl-10 md:pl-0 flex items-center md:justify-start">
                            <span class="text-white font-mono text-sm tracking-widest bg-[#064e3b] px-3 py-1 rounded-full shadow-sm">2024</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Quote Section -->
    <section class="max-w-4xl mx-auto px-6 lg:px-12 py-24 md:py-32 text-center">
        <div class="inline-block bg-[#1c1c1a] text-white text-[9px] uppercase tracking-widest px-4 py-2 mb-8 shadow-sm">
            Statement Kreatif
        </div>
        <h2 class="text-2xl md:text-3xl lg:text-4xl font-serif text-[#1c1c1a] leading-snug max-w-3xl mx-auto italic">
            "Kesopanan bukan tentang menyembunyikan, tapi tentang mengungkapkan karakter melalui ketenangan."
        </h2>
    </section>

</main>

<?php get_footer(); ?>
