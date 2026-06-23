<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Reset Password" backUrl="/login" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen flex items-center justify-center py-20">
            <div class="max-w-[440px] w-full mx-auto px-6">
                
                <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight mb-2 text-center">Reset Password</h1>
                <p class="font-sans text-[14px] text-[#615e57] text-center mb-10">Silakan masukkan password baru Anda.</p>

                <form wire:submit.prevent="resetPassword" class="flex flex-col gap-6">
                    <input type="hidden" wire:model="token">

                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Email Address *</label>
                        <input type="email" wire:model="email" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors @error('email') border-red-500 @enderror" placeholder="your@email.com">
                        @error('email')
                            <span class="text-xs text-red-500 font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Password Baru *</label>
                        <input type="password" wire:model="password" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors @error('password') border-red-500 @enderror" placeholder="Minimal 8 karakter">
                        @error('password')
                            <span class="text-xs text-red-500 font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Konfirmasi Password Baru *</label>
                        <input type="password" wire:model="password_confirmation" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors" placeholder="Ulangi password baru">
                    </div>

                    <button type="submit" class="w-full bg-[#1c1c1a] hover:bg-[#000000] text-white py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors flex items-center justify-center gap-2">
                        <span wire:loading wire:target="resetPassword" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>SIMPAN PASSWORD BARU</span>
                    </button>
                </form>

                <div class="mt-8 text-center border-t border-[#e5e2de] pt-8">
                    <a href="{{ url('/login') }}" class="inline-block font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-8 py-3 hover:bg-[#f0ede9] transition-colors">
                        Kembali ke Log In
                    </a>
                </div>

            </div>
        </main>
    </div>
</div>
