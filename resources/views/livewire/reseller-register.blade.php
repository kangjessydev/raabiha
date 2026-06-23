<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Daftar Reseller" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen flex items-center justify-center py-20">
            <div class="max-w-[440px] w-full mx-auto px-6">
                
                <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight mb-2 text-center">Daftar Reseller</h1>
                <p class="font-sans text-[14px] text-[#615e57] text-center mb-10">Bergabunglah menjadi mitra Raabiha dan nikmati diskon khusus reseller.</p>

                <form wire:submit.prevent="register" class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Nama Lengkap *</label>
                        <input type="text" wire:model="name" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="Nama Anda">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Email Address *</label>
                        <input type="email" wire:model="email" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="your@email.com">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Password *</label>
                        <input type="password" wire:model="password" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="••••••••">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Konfirmasi Password *</label>
                        <input type="password" wire:model="password_confirmation" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="••••••••">
                    </div>

                    <button type="submit" class="w-full bg-[#064e3b] hover:bg-[#043326] text-white py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors mt-2">
                        <span wire:loading.remove wire:target="register">DAFTAR SEKARANG</span>
                        <span wire:loading wire:target="register">MEMPROSES...</span>
                    </button>
                </form>

                <div class="mt-8 text-center border-t border-[#e5e2de] pt-8">
                    <p class="font-sans text-[13px] text-[#615e57] mb-4">Sudah memiliki akun?</p>
                    <a href="{{ url('/login') }}" class="inline-block font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-8 py-3 hover:bg-[#f0ede9] transition-colors">
                        Log In
                    </a>
                </div>

            </div>
        </main>
    </div>
</div>
