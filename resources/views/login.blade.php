<x-layouts.app title="Masuk" robots="noindex, nofollow">
    <x-slot:header>
        <x-global.mobile-subnav title="Masuk Akun" backUrl="/" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen flex items-center justify-center py-20">
            <div class="max-w-[440px] w-full mx-auto px-6">
                
                <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight mb-2 text-center">Log In</h1>
                <p class="font-sans text-[14px] text-[#615e57] text-center mb-10">Selamat datang kembali di Raabiha.</p>

                <form class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Email Address</label>
                        <input type="email" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="your@email.com">
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center">
                            <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Password</label>
                            <a href="#" class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] hover:text-[#1c1c1a] underline decoration-[#e5e2de] underline-offset-4">Lupa Password?</a>
                        </div>
                        <input type="password" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="••••••••">
                    </div>

                    <button type="button" onclick="window.location.href='/account'" class="w-full bg-[#064e3b] hover:bg-[#043326] text-white py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors mt-2">
                        MASUK
                    </button>
                </form>

                <div class="mt-8 text-center border-t border-[#e5e2de] pt-8">
                    <p class="font-sans text-[13px] text-[#615e57] mb-4">Belum memiliki akun?</p>
                    <a href="{{ url('/register') }}" wire:navigate.hover class="inline-block font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-8 py-3 hover:bg-[#f0ede9] transition-colors">
                        Buat Akun
                    </a>
                </div>

            </div>
        </main>
    </div>
</x-layouts.app>
