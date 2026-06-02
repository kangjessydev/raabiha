<?php
/**
 * Template Name: Galeri Page
 *
 * @package RaabihaTheme
 */

get_header();
?>

<main class="site-main bg-[#fcf9f5] min-h-screen pb-0">
    
    <!-- Header Section -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-16 md:py-24 border-b border-[#e5e2de]">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-8">
            <h1 class="text-5xl md:text-6xl lg:text-[80px] font-serif text-[#1c1c1a] leading-[0.9] tracking-tight uppercase">
                ARCHITECTURAL<br>MODESTY<br>
                <span class="normal-case italic text-[#615e57] text-4xl md:text-5xl lg:text-[70px]">A Visual Study.</span>
            </h1>
            <div class="max-w-xs pb-2">
                <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase mb-4">Gallery Curation</p>
                <p class="text-[#615e57] text-sm leading-relaxed">
                    Evolusi dari desain atelier ke mahakarya struktural. Menjelajahi ruang, tekstur, dan bentuk melalui lensa arsitektural Raabiha.
                </p>
            </div>
        </div>
    </section>

    <!-- Masonry/Grid Gallery Section -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 py-12 md:py-20">
        <div class="space-y-6 md:space-y-10">
            
            <!-- Row 1 -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-10">
                <!-- Left Large Portrait -->
                <div class="md:col-span-7 relative group overflow-hidden">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/gallery-1.png" alt="Model in white" class="w-full h-full object-cover aspect-[3/4] md:aspect-auto group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute bottom-6 left-6 bg-[#1c1c1a] text-white text-[9px] font-mono uppercase tracking-[0.2em] px-3 py-1.5 shadow-sm">
                        VOL 1. / 2024
                    </div>
                </div>
                <!-- Right Stacked -->
                <div class="md:col-span-5 flex flex-col gap-6 md:gap-10">
                    <div class="w-full h-1/2 overflow-hidden group">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/gallery-5.png" alt="Green texture" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    </div>
                    <div class="w-full h-1/2 overflow-hidden group">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/gallery-3.png" alt="Fabric texture" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    </div>
                </div>
            </div>

            <!-- Row 2 Full Width Landscape -->
            <div class="w-full overflow-hidden group">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/gallery-2.png" alt="Interior space" class="w-full h-auto object-cover aspect-[21/9] md:aspect-[3/1] group-hover:scale-105 transition-transform duration-700">
            </div>

            <!-- Row 3 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-10">
                <!-- Col 1 -->
                <div class="flex flex-col gap-6">
                    <div class="overflow-hidden group h-[85%]">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/gallery-4.png" alt="Model in black" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    </div>
                    <div class="p-2 border-l border-[#1c1c1a] pl-4 flex-1">
                        <h3 class="text-lg font-serif text-[#1c1c1a] mb-1 italic">The Fluidity</h3>
                        <p class="text-[#615e57] text-xs leading-relaxed">Elemen gerak dalam siluet hitam elegan.</p>
                    </div>
                </div>
                <!-- Col 2 -->
                <div class="overflow-hidden group h-full">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-atelier.png" alt="Wardrobe" class="w-full h-full object-cover aspect-[4/5] md:aspect-auto group-hover:scale-105 transition-transform duration-700">
                </div>
                <!-- Col 3 -->
                <div class="flex flex-col gap-6 md:gap-10 h-full">
                    <div class="flex-1 flex flex-col justify-center items-center text-center px-6 py-12 md:py-0 border border-[#e5e2de] bg-white aspect-[4/3] md:aspect-auto">
                        <p class="text-[#1c1c1a] font-serif text-xl leading-snug italic max-w-[200px]">
                            "Kami tidak hanya merancang pakaian, kami mengkonstruksi ruang di sekitar tubuh."
                        </p>
                    </div>
                    <div class="flex-1 overflow-hidden group">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/contact-hero.png" alt="Stairs" class="w-full h-full object-cover grayscale opacity-80 group-hover:scale-105 transition-transform duration-700">
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Statement Section -->
    <section class="bg-white py-24 md:py-40 text-center px-6">
        <p class="text-[#064e3b] text-[10px] font-mono tracking-[0.3em] uppercase mb-8">The Philosophy</p>
        <h2 class="text-4xl md:text-5xl lg:text-7xl font-serif text-[#1c1c1a] leading-[1.1] tracking-tight max-w-4xl mx-auto">
            DIMANA <span class="italic text-[#615e57]">STRUKTUR</span><br>
            BERTEMU DENGAN<br>
            KETENANGAN.
        </h2>
    </section>

    <!-- Bottom Feature Section -->
    <section class="bg-[#fcf9f5] max-w-[1440px] mx-auto px-6 lg:px-12 py-20 md:py-32">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-20 items-center">
            <div class="order-2 md:order-1 flex flex-col justify-center max-w-md mx-auto md:mx-0">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-8 h-px bg-[#1c1c1a]"></div>
                    <p class="text-[#1c1c1a] text-[9px] font-mono tracking-[0.2em] uppercase">Materiality</p>
                </div>
                <h3 class="text-3xl md:text-4xl font-serif text-[#1c1c1a] mb-6">Sentuhan yang Bercerita.</h3>
                <p class="text-[#615e57] text-sm leading-relaxed mb-10">
                    Kami menggunakan tekstur yang merespons cahaya—menciptakan bayangan dramatis pada setiap lipatan. Interaksi antara siluet yang kaku dan material yang mengalir menghasilkan keseimbangan visual yang sempurna.
                </p>
                <div class="flex gap-12 border-t border-[#e5e2de] pt-6">
                    <div>
                        <p class="text-[#1c1c1a] text-[10px] font-mono font-bold uppercase tracking-[0.1em] mb-1">Fabric</p>
                        <p class="text-[#615e57] text-sm italic font-serif">Silk Blend Premium</p>
                    </div>
                    <div>
                        <p class="text-[#1c1c1a] text-[10px] font-mono font-bold uppercase tracking-[0.1em] mb-1">Origin</p>
                        <p class="text-[#615e57] text-sm italic font-serif">West Java, ID</p>
                    </div>
                </div>
            </div>
            <div class="order-1 md:order-2 overflow-hidden group">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-atelier.png" alt="Textured Jacket" class="w-full h-auto aspect-[3/4] object-cover grayscale opacity-90 group-hover:scale-105 transition-transform duration-700">
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
