<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Daftar Akun" backUrl="/login" />
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

                <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight mb-2 text-center">Register</h1>
                <p class="font-sans text-[14px] text-[#615e57] text-center mb-10">Buat akun untuk checkout yang lebih cepat.</p>

                <form wire:submit.prevent="register" class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Nama Lengkap *</label>
                        <input type="text" wire:model="name" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors @error('name') border-red-500 @enderror" placeholder="Jane Doe">
                        @error('name')
                            <span class="text-xs text-red-500 font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Email Address *</label>
                        <input type="email" wire:model="email" class="w-full h-12 bg-transparent border border-[#e5e2de] px-4 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors @error('email') border-red-500 @enderror" placeholder="your@email.com">
                        @error('email')
                            <span class="text-xs text-red-500 font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div x-data="{ 
                        password: @entangle('password'), 
                        showPassword: false,
                        get strength() {
                            let pwd = this.password || '';
                            if (!pwd) return 0;
                            let score = 0;
                            if (pwd.length >= 8) score++;
                            if (pwd.match(/[a-z]/) && pwd.match(/[A-Z]/)) score++;
                            if (pwd.match(/\d/)) score++;
                            if (pwd.match(/[^a-zA-Z\d]/)) score++;
                            return score;
                        },
                        get strengthText() {
                            let s = this.strength;
                            if (s === 0) return '';
                            if (s === 1) return 'Sangat Lemah';
                            if (s === 2) return 'Lemah';
                            if (s === 3) return 'Sedang';
                            return 'Kuat';
                        },
                        get strengthColor() {
                            let s = this.strength;
                            if (s === 1) return 'bg-red-500';
                            if (s === 2) return 'bg-orange-500';
                            if (s === 3) return 'bg-yellow-500';
                            return 'bg-green-600';
                        },
                        get textClass() {
                            let s = this.strength;
                            if (s === 1) return 'text-red-500';
                            if (s === 2) return 'text-orange-500';
                            if (s === 3) return 'text-yellow-600 dark:text-yellow-500';
                            return 'text-green-600';
                        }
                    }" class="flex flex-col gap-2 relative">
                        <label class="font-mono text-[9px] uppercase tracking-widest text-[#615e57]">Password *</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" wire:model="password" class="w-full h-12 bg-transparent border border-[#e5e2de] pl-4 pr-12 font-sans text-sm focus:outline-none focus:border-[#064e3b] transition-colors @error('password') border-red-500 @enderror" placeholder="Minimal 8 karakter">
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
                        
                        <!-- Password Strength Indicator -->
                        <template x-if="password && password.length > 0">
                            <div class="mt-1 space-y-1.5">
                                <div class="flex justify-between items-center text-[10px] font-mono uppercase tracking-wider">
                                    <span class="text-gray-500">Kekuatan Password:</span>
                                    <span :class="textClass" class="font-bold" x-text="strengthText"></span>
                                </div>
                                <div class="grid grid-cols-4 gap-1.5 h-1 bg-[#e5e2de] rounded-full overflow-hidden">
                                    <div :class="strength >= 1 ? strengthColor : 'bg-transparent'" class="h-full rounded-full transition-all duration-300"></div>
                                    <div :class="strength >= 2 ? strengthColor : 'bg-transparent'" class="h-full rounded-full transition-all duration-300"></div>
                                    <div :class="strength >= 3 ? strengthColor : 'bg-transparent'" class="h-full rounded-full transition-all duration-300"></div>
                                    <div :class="strength >= 4 ? strengthColor : 'bg-transparent'" class="h-full rounded-full transition-all duration-300"></div>
                                </div>
                            </div>
                        </template>

                        @error('password')
                            <span class="text-xs text-red-500 font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div x-data="{ agree: @entangle('agree_terms') }" class="flex flex-col gap-2">
                        <div class="flex items-start gap-3">
                            <div class="flex items-center h-5 mt-0.5">
                                <input type="checkbox" id="agree_terms" x-model="agree" class="w-4 h-4 text-[#064e3b] bg-transparent border-[#e5e2de] focus:ring-[#064e3b] rounded-sm transition-colors cursor-pointer">
                            </div>
                            <label for="agree_terms" class="font-sans text-[12px] text-[#615e57] leading-relaxed cursor-pointer">
                                Saya telah membaca dan menyetujui <a href="{{ url('/syarat-ketentuan') }}" target="_blank" class="underline text-[#064e3b]">Syarat dan Ketentuan</a> serta <a href="{{ url('/kebijakan-privasi') }}" target="_blank" class="underline text-[#064e3b]">Kebijakan Privasi</a>.
                            </label>
                        </div>
                        @error('agree_terms')
                            <span class="text-xs text-red-500 font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div x-data="{ agree: @entangle('agree_terms') }">
                        <button type="submit" :disabled="!agree" :class="agree ? 'bg-[#1c1c1a] hover:bg-[#000000] text-white' : 'bg-[#e5e2de] text-[#a3a19b] cursor-not-allowed'" class="w-full py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors flex items-center justify-center gap-2">
                            <span wire:loading wire:target="register" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>DAFTAR SEKARANG</span>
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center border-t border-[#e5e2de] pt-8">
                    <p class="font-sans text-[13px] text-[#615e57] mb-4">Sudah memiliki akun?</p>
                    <a href="{{ url('/login') }}" class="inline-block font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-8 py-3 hover:bg-[#f0ede9] transition-colors">
                        Log In di Sini
                    </a>
                </div>

            </div>
        </main>
    </div>
</div>
