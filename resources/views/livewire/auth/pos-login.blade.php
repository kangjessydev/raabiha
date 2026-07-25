<div class="min-h-screen bg-slate-950 flex flex-col justify-center items-center p-4 selection:bg-indigo-500 selection:text-white relative overflow-hidden font-sans">
    <!-- Subtle Background Elements -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-500 shadow-xl shadow-indigo-500/20 mb-4 border border-white/10">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Terminal POS Kasir</h1>
            <p class="text-sm text-slate-400 mt-1">Toko Raabiha • Sistem Kasir & Loyalti Digital</p>
        </div>

        <!-- Session Message / Flash Error -->
        @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Login Container -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl">
            <form wire:submit.prevent="login" class="space-y-5">
                <!-- Field Username / Email -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Username atau Email Kasir</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.defer="loginInput"
                            placeholder="Masukkan username atau email"
                            class="w-full pl-11 pr-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition duration-200 text-sm"
                            autofocus
                        />
                    </div>
                    @error('loginInput')
                        <p class="mt-1.5 text-xs text-rose-400 flex items-center gap-1">
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Field Password -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Password Kasir</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            wire:model.defer="password"
                            placeholder="Masukkan password"
                            class="w-full pl-11 pr-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition duration-200 text-sm"
                        />
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-400 flex items-center gap-1">
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Checkbox Remember -->
                <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer hover:text-slate-300">
                        <input type="checkbox" wire:model.defer="remember" class="w-4 h-4 rounded border-slate-800 bg-slate-950 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                        <span>Ingat sesi di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-semibold rounded-xl shadow-lg shadow-indigo-600/25 active:scale-[0.99] transition duration-200 flex items-center justify-center gap-2 text-sm cursor-pointer"
                >
                    <span wire:loading.remove>Masuk ke POS</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memproses...</span>
                    </span>
                </button>
            </form>
        </div>

        <!-- Navigation Links Footer -->
        <div class="mt-8 text-center space-y-2">
            <div class="flex justify-center items-center gap-4 text-xs text-slate-500">
                <a href="/admin/login" class="hover:text-slate-300 transition underline underline-offset-4">Portal Admin Panel</a>
                <span>•</span>
                <a href="/login" class="hover:text-slate-300 transition underline underline-offset-4">Login Pelanggan E-Commerce</a>
            </div>
            <p class="text-[11px] text-slate-600">© {{ date('Y') }} Raabiha Store POS Terminal. Safe & Secure System.</p>
        </div>
    </div>
</div>
