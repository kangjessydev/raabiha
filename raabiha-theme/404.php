<?php
/**
 * 404.php — Custom 404 Not Found Template
 *
 * Designed with a premium modern dark aesthetic matching Raabiha Store.
 * Used for general missing pages and masked admin URLs.
 *
 * @package RaabihaTheme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="raabiha-404-container min-h-screen bg-[#0b0f19] flex items-center justify-center px-4 relative overflow-hidden font-sans">
    <!-- Glowing decorative background lights -->
    <div class="absolute top-1/4 left-1/4 w-[300px] h-[300px] bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-[300px] h-[300px] bg-purple-500/10 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-md w-full text-center relative z-10 py-16">
        <!-- Error Code Title -->
        <h1 class="text-7xl md:text-8xl font-extrabold tracking-tighter bg-gradient-to-r from-indigo-400 via-purple-400 to-indigo-500 bg-clip-text text-transparent select-none animate-pulse">
            404
        </h1>
        
        <!-- Main Message -->
        <h2 class="text-lg md:text-xl font-bold text-slate-100 mt-6 tracking-tight">
            Halaman Tidak Ditemukan
        </h2>
        
        <p class="text-xs md:text-sm text-slate-400 mt-3 leading-relaxed max-w-sm mx-auto">
            Maaf, halaman yang Anda tuju tidak tersedia, telah dihapus, atau sedang dalam mode pengamanan sistem ketat.
        </p>

        <!-- CTA Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a 
                href="<?php echo esc_url( home_url( '/' ) ); ?>" 
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg text-xs font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200"
            >
                Kembali ke Beranda
            </a>
            
            <?php if ( is_user_logged_in() ) : ?>
                <a 
                    href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" 
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg text-xs font-semibold border border-slate-700 bg-slate-800/40 text-slate-300 hover:bg-slate-800 hover:text-slate-100 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200"
                >
                    Dashboard Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* Inline styles fallback for 404 block */
    .raabiha-404-container {
        font-family: 'Outfit', 'Inter', system-ui, sans-serif;
    }
    /* Simple tailwind CSS helper fallbacks */
    .min-h-screen { min-height: 100vh; }
    .bg-\[\#0b0f19\] { background-color: #0b0f19; }
    .flex { display: flex; }
    .items-center { align-items: center; }
    .justify-center { justify-content: center; }
    .px-4 { padding-left: 1rem; padding-right: 1rem; }
    .relative { position: relative; }
    .overflow-hidden { overflow: hidden; }
    .max-w-md { max-width: 28rem; }
    .w-full { width: 100%; }
    .text-center { text-align: center; }
    .relative { position: relative; }
    .z-10 { z-index: 10; }
    .py-16 { padding-top: 4rem; padding-bottom: 4rem; }
    .text-7xl { font-size: 4.5rem; }
    @media (min-width: 768px) {
        .text-7xl { font-size: 6rem; }
    }
    .font-extrabold { font-weight: 800; }
    .tracking-tighter { letter-spacing: -0.05em; }
    .bg-gradient-to-r { background-image: linear-gradient(to right, var(--tw-gradient-stops)); }
    .from-indigo-400 { --tw-gradient-from: #818cf8; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(129, 140, 248, 0)); }
    .via-purple-400 { --tw-gradient-stops: var(--tw-gradient-from), #c084fc, var(--tw-gradient-to, rgba(192, 132, 252, 0)); }
    .to-indigo-500 { --tw-gradient-to: #6366f1; }
    .bg-clip-text { -webkit-background-clip: text; background-clip: text; }
    .text-transparent { color: transparent; }
    .select-none { user-select: none; }
    .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    .text-lg { font-size: 1.125rem; }
    @media (min-width: 768px) {
        .text-lg { font-size: 1.25rem; }
    }
    .font-bold { font-weight: 700; }
    .text-slate-100 { color: #f1f5f9; }
    .mt-6 { margin-top: 1.5rem; }
    .tracking-tight { letter-spacing: -0.025em; }
    .text-xs { font-size: 0.75rem; }
    @media (min-width: 768px) {
        .text-xs { font-size: 0.875rem; }
    }
    .text-slate-400 { color: #94a3b8; }
    .mt-3 { margin-top: 0.75rem; }
    .leading-relaxed { line-height: 1.625; }
    .max-w-sm { max-width: 24rem; }
    .mx-auto { margin-left: auto; margin-right: auto; }
    .mt-8 { margin-top: 2rem; }
    .flex-col { flex-direction: column; }
    @media (min-width: 640px) {
        .flex-col { flex-direction: row; }
    }
    .gap-3 { gap: 0.75rem; }
    .inline-flex { display: inline-flex; }
    .px-5 { padding-left: 1.25rem; padding-right: 1.25rem; }
    .py-2\.5 { padding-top: 0.625rem; padding-bottom: 0.625rem; }
    .rounded-lg { border-radius: 0.5rem; }
    .font-semibold { font-weight: 600; }
    .from-indigo-500 { --tw-gradient-from: #6366f1; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(99, 102, 241, 0)); }
    .to-purple-600 { --tw-gradient-to: #7c3aed; }
    .text-white { color: #fff; }
    .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1); }
    .border { border-width: 1px; }
    .border-slate-700 { border-color: #334155; }
    .bg-slate-800\/40 { background-color: rgba(30, 41, 59, 0.4); }
    .text-slate-300 { color: #cbd5e1; }
    .transition-all { transition-property: all; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
    .duration-200 { transition-duration: 200ms; }
</style>

<?php
get_footer();
