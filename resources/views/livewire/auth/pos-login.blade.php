<div class="min-h-screen bg-slate-50/50 flex flex-col justify-center items-center p-4 sm:p-6 font-sans relative overflow-hidden selection:bg-emerald-500 selection:text-white">
    <!-- Ambient Background Blur Elements -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-100/60 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-100/60 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 space-y-6">
        <!-- Logo & Header (Modern Brand Header) -->
        <div class="text-center space-y-3">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-xl shadow-emerald-600/20 border border-emerald-400/30 mb-1">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Raabiha POS</h1>
                <p class="text-xs font-medium text-gray-500 mt-0.5">Terminal Kasir POS & Auth Access</p>
            </div>
        </div>

        <!-- Flash Session Alert -->
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200/80 text-red-700 text-xs font-semibold flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Modern Filament Card Container -->
        <div class="bg-white/90 backdrop-blur-xl border border-gray-200/80 rounded-3xl p-7 sm:p-9 shadow-xl shadow-gray-200/50">
            <form wire:submit.prevent="login" class="space-y-5">
                <!-- Username / Email Field -->
                <div>
                    <label for="loginInput" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Username / Email Kasir <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="loginInput"
                            wire:model.defer="loginInput"
                            placeholder="Masukkan username atau email"
                            class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-2xl text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all duration-200 font-medium"
                            autofocus
                        />
                    </div>
                    @error('loginInput')
                        <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Password Kasir <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password"
                            wire:model.defer="password"
                            placeholder="Masukkan password"
                            class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-2xl text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all duration-200 font-medium"
                        />
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Checkbox -->
                <div class="flex items-center justify-between text-xs text-gray-600 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer hover:text-gray-900 select-none">
                        <input type="checkbox" wire:model.defer="remember" class="w-4 h-4 rounded-md border-gray-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0">
                        <span class="font-medium">Ingat sesi di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-600/20 active:scale-[0.99] transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-emerald-500/30 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove class="flex items-center gap-2">
                        <span>Masuk ke Terminal POS</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
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

        <!-- Clean Footer (Tanpa Link Portal Admin & Customer) -->
        <div class="text-center text-[11px] text-gray-400 pt-2">
            &copy; {{ date('Y') }} Raabiha Modest Fashion &middot; Terminal POS Kasir
        </div>
    </div>
</div>
