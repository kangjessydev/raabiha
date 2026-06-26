<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Masuk Akun" backUrl="/" />
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

                <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight mb-2 text-center">Log In</h1>
                <p class="font-sans text-[14px] text-[#615e57] text-center mb-10">Selamat datang kembali di Raabiha.</p>

                @if (session()->has('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-[#064e3b] text-sm font-sans">
                        {{ session('success') }}
                    </div>
                @endif

                <form wire:submit.prevent="login" class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Username / Email *</label>
                        <input type="text" wire:model="loginInput" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors @error('loginInput') border-red-500 @enderror" placeholder="Username atau email Anda">
                        @error('loginInput')
                            <span class="text-xs text-red-500 font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div x-data="{ showPassword: false }" class="flex flex-col gap-2">
                        <div class="flex justify-between items-center">
                            <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Password *</label>
                            <a href="{{ route('password.request') }}" class="font-mono text-[9px] uppercase tracking-widest text-[#615e57] hover:text-[#1c1c1a] underline decoration-[#e5e2de] underline-offset-4">Lupa Password?</a>
                        </div>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" wire:model="password" class="w-full h-12 bg-transparent border border-[#e5e2de] pl-4 pr-12 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors @error('password') border-red-500 @enderror" placeholder="••••••••">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#615e57] hover:text-[#1c1c1a] focus:outline-none">
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="text-xs text-red-500 font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="remember" wire:model="remember" class="w-4 h-4 accent-[#064e3b] rounded border-[#e5e2de]">
                        <label for="remember" class="font-sans text-xs text-[#615e57] cursor-pointer">Ingat Saya</label>
                    </div>

                    <button type="submit" class="w-full bg-[#064e3b] hover:bg-[#043326] text-white py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors mt-2 flex items-center justify-center gap-2">
                        <span wire:loading wire:target="login" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>MASUK</span>
                    </button>
                </form>

                <div class="mt-8 text-center border-t border-[#e5e2de] pt-8">
                    <p class="font-sans text-[13px] text-[#615e57] mb-4">Belum memiliki akun?</p>
                    <a href="{{ url('/register') }}" class="inline-block font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-8 py-3 hover:bg-[#f0ede9] transition-colors">
                        Buat Akun
                    </a>
                </div>

            </div>
        </main>
    </div>
</div>
