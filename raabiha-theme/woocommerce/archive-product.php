<?php
/**
 * Custom WooCommerce Shop Archive
 *
 * @package RaabihaTheme
 */

get_header();
?>

<main class="site-main bg-[#fcf9f5] min-h-screen pb-24 pt-12 md:pt-16">

    <!-- Header Section -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12 mb-12">
        <h1 class="text-5xl md:text-6xl lg:text-7xl font-serif text-[#1c1c1a] mb-6 tracking-tight uppercase">
            Katalog Produk
        </h1>
        <p class="text-[#615e57] text-sm md:text-base leading-relaxed max-w-2xl mb-12">
            Architectural silhouettes designed for the contemporary modest lifestyle. Melding urban utility with high-fashion restraint.
        </p>
        <div class="w-full h-[1px] bg-[#e5e2de]"></div>
    </section>

    <!-- Main Shop Layout -->
    <section class="max-w-[1440px] mx-auto px-6 lg:px-12">
        <div class="flex flex-col lg:flex-row gap-12 lg:gap-16">
            
            <!-- Sidebar Filters (Mobile Bottomsheet & Desktop Sidebar) -->
            <div id="filter-backdrop" class="fixed inset-0 bg-black/50 z-[60] hidden lg:hidden opacity-0 transition-opacity duration-300"></div>
            
            <aside id="shop-filter-sidebar" class="
                fixed inset-x-0 bottom-0 z-[70] bg-[#fcf9f5] p-6 rounded-t-2xl shadow-[0_-10px_40px_rgba(0,0,0,0.1)] 
                transform translate-y-full transition-transform duration-300
                lg:static lg:transform-none lg:z-auto lg:bg-transparent lg:p-0 lg:rounded-none lg:shadow-none lg:block
                w-full lg:w-64 shrink-0 max-h-[85vh] lg:max-h-none overflow-y-auto lg:overflow-visible
            ">
                <!-- Mobile Bottomsheet Handle & Title -->
                <div class="flex justify-between items-center mb-6 lg:hidden">
                    <h3 class="text-[#1c1c1a] text-sm font-mono font-bold tracking-[0.2em] uppercase">Filter Produk</h3>
                    <button id="filter-close-btn" class="text-[#1c1c1a] p-2 hover:bg-[#e5e2de] rounded-full transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Urutkan (Mobile Only) -->
                <div class="mb-10 lg:hidden">
                    <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Urutkan</h4>
                    <div class="flex flex-col gap-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 bg-[#1c1c1a] border border-[#1c1c1a] flex items-center justify-center transition-colors">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest font-bold">Terbaru</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">Harga Terendah</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">Harga Tertinggi</span>
                        </label>
                    </div>
                </div>

                <!-- Ukuran -->
                <div class="mb-10">
                    <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Ukuran</h4>
                    <div class="grid grid-cols-2 gap-y-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">XS/S</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">M/L</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 bg-[#1c1c1a] border border-[#1c1c1a] flex items-center justify-center transition-colors">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest font-bold">Oversized</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">Uni-Size</span>
                        </label>
                    </div>
                </div>

                <!-- Warna -->
                <div class="mb-10">
                    <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Warna</h4>
                    <div class="flex flex-col gap-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 bg-[#1c1c1a] border border-[#1c1c1a] flex items-center justify-center transition-colors shrink-0">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="w-3 h-3 bg-[#333333] shrink-0 border border-black/10"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest font-bold">Charcoal</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors shrink-0"></div>
                            <div class="w-3 h-3 bg-[#d9d5cd] shrink-0 border border-black/10"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">Slate Sand</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors shrink-0"></div>
                            <div class="w-3 h-3 bg-[#c99a8b] shrink-0 border border-black/10"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">Dusty Rose</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors shrink-0"></div>
                            <div class="w-3 h-3 bg-[#f2f2f2] shrink-0 border border-black/10"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">Off-White</span>
                        </label>
                    </div>
                </div>

                <!-- Kategori -->
                <div class="mb-10">
                    <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Kategori</h4>
                    <div class="flex flex-col gap-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">Outerwear</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 bg-[#1c1c1a] border border-[#1c1c1a] flex items-center justify-center transition-colors">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest font-bold">Essential Tees</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">Modest Bottoms</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors"></div>
                            <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest">Headwear</span>
                        </label>
                    </div>
                </div>

                <!-- Harga -->
                <div class="mb-10">
                    <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Harga</h4>
                    <div class="mt-6 relative">
                        <div class="w-full h-1 bg-[#d1cec9]"></div>
                        <div class="absolute top-0 left-0 w-full h-1 bg-[#d1cec9]"></div>
                        <div class="absolute top-0 left-0 w-2/3 h-1 bg-[#1c1c1a]"></div>
                        <div class="absolute top-1/2 left-0 w-3 h-3 bg-[#1c1c1a] -translate-y-1/2"></div>
                        <div class="absolute top-1/2 left-2/3 w-3 h-3 bg-[#1c1c1a] -translate-y-1/2"></div>
                    </div>
                    <div class="flex justify-between mt-4">
                        <span class="text-[#1c1c1a] text-[9px] font-mono tracking-widest uppercase">0 IDR</span>
                        <span class="text-[#1c1c1a] text-[9px] font-mono tracking-widest uppercase">5.000.000 IDR</span>
                    </div>
                </div>
            </aside>

            <!-- Product Grid Area -->
            <div class="flex-1">
                
                <!-- Top Bar -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
                    
                    <!-- Mobile Toolbar (Icon Buttons & Search) -->
                    <div class="flex items-center gap-3 w-full lg:w-auto">
                        <!-- Filter Icon Button (Mobile Only) -->
                        <button id="mobile-filter-btn" class="lg:hidden flex items-center justify-center w-8 h-8 text-[#1c1c1a] hover:text-[#615e57] transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        </button>
                        
                        <!-- Grid/List Toggle (Mobile & Desktop) -->
                        <div class="flex items-center border border-[#e5e2de] text-[#1c1c1a] shrink-0">
                            <button class="p-2 border-r border-[#e5e2de] bg-[#f2efe8] hover:bg-[#e5e2de] transition-colors">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/></svg>
                            </button>
                            <button class="p-2 hover:bg-[#e5e2de] transition-colors text-[#a3a3a3]">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4V6zm0 5h16v2H4v-2zm0 5h16v2H4v-2z"/></svg>
                            </button>
                        </div>
                        
                        <!-- Search Bar -->
                        <div class="relative flex-1 lg:w-64">
                            <input type="text" placeholder="Cari produk..." class="w-full border border-[#e5e2de] px-4 py-2.5 text-[10px] font-mono tracking-widest uppercase bg-[#fcf9f5] focus:outline-none focus:border-[#1c1c1a] transition-colors">
                            <button class="absolute right-0 top-0 h-full px-3 text-[#615e57] hover:text-[#1c1c1a] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>
                        </div>
                        
                        <span class="text-[#615e57] text-[10px] font-mono uppercase tracking-widest hidden lg:inline-block ml-3">88 Produk Ditemukan</span>
                    </div>

                    <!-- Sort Dropdown (Desktop Only) -->
                    <div class="hidden lg:flex items-center gap-4">
                        <span class="text-[#615e57] text-[10px] font-mono uppercase tracking-widest">Urutkan:</span>
                        <div class="border border-[#1c1c1a] px-3 py-2 flex items-center gap-6 cursor-pointer">
                            <span class="text-[#1c1c1a] text-[10px] font-mono font-bold uppercase tracking-widest">Terbaru</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-x-4 lg:gap-x-6 gap-y-12">
                    <?php
                    if ( woocommerce_product_loop() ) {
                        while ( have_posts() ) {
                            the_post();
                            wc_get_template_part( 'content', 'product' );
                        }
                    } else {
                        echo '<p class="col-span-3 text-center text-[#615e57] font-mono py-12">Belum ada produk.</p>';
                    }
                    ?>
                </div>

                <!-- Infinite Scroll Loader -->
                <div class="mt-16 flex flex-col items-center justify-center gap-4 py-8">
                    <div class="w-6 h-6 border-2 border-[#e5e2de] border-t-[#1c1c1a] rounded-full animate-spin"></div>
                    <span class="text-[#615e57] text-[10px] font-mono font-bold tracking-[0.2em] uppercase">Memuat Produk...</span>
                </div>

            </div>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtn = document.getElementById('mobile-filter-btn');
    const filterCloseBtn = document.getElementById('filter-close-btn');
    const filterSidebar = document.getElementById('shop-filter-sidebar');
    const filterBackdrop = document.getElementById('filter-backdrop');

    function openFilter() {
        if (!filterBackdrop || !filterSidebar) return;
        filterBackdrop.classList.remove('hidden');
        // Force reflow
        void filterBackdrop.offsetWidth;
        filterBackdrop.classList.remove('opacity-0');
        
        filterSidebar.classList.remove('translate-y-full');
        document.body.style.overflow = 'hidden';
    }

    function closeFilter() {
        if (!filterBackdrop || !filterSidebar) return;
        filterSidebar.classList.add('translate-y-full');
        filterBackdrop.classList.add('opacity-0');
        
        setTimeout(() => {
            filterBackdrop.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    }

    if (filterBtn) filterBtn.addEventListener('click', openFilter);
    if (filterCloseBtn) filterCloseBtn.addEventListener('click', closeFilter);
    if (filterBackdrop) filterBackdrop.addEventListener('click', closeFilter);
});
</script>

<?php get_footer(); ?>
