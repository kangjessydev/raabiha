<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Lupa Password" backUrl="/login" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen flex items-center justify-center py-20">
            <div class="max-w-[440px] w-full mx-auto px-6">
                
                <div class="mb-8 text-center">
                    <a href="/" class="inline-flex items-center gap-2 text-xs font-mono tracking-widest text-[#615e57] hover:text-[#1c1c1a] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        KEMBALI KE BERANDA
                    </a>
                </div>

                <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight mb-2 text-center">Reset Password</h1>
                <p class="font-sans text-[14px] text-[#615e57] text-center mb-10">Masukkan alamat email Anda untuk menerima tautan reset password.</p>

                @if (session()->has('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-[#064e3b] text-sm font-sans">
                        {{ session('status') }}
                    </div>
                @endif

                <form wire:submit.prevent="sendResetLink" class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Email Address *</label>
                        <input type="email" wire:model="email" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors @error('email') border-red-500 @enderror" placeholder="your@email.com">
                        @error('email')
                            <span class="text-xs text-red-500 font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="w-full bg-[#1c1c1a] hover:bg-[#000000] text-white py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors flex items-center justify-center gap-2">
                        <span wire:loading wire:target="sendResetLink" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>KIRIM TAUTAN RESET</span>
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
