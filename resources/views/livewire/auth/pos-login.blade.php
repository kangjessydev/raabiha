<div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center p-4 sm:p-6 font-sans">
    <div class="w-full max-w-md space-y-6">
        <!-- Logo & Header -->
        <div class="text-center space-y-1.5">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Raabiha POS</h1>
            <p class="text-xs font-medium text-gray-500">Masuk ke akun kasir Anda</p>
        </div>

        <!-- Flash Session Alert -->
        @if (session('error'))
            <div
                class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-medium flex items-center gap-2">
                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Container (Standard Filament Card) -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 sm:p-8 shadow-sm">
            <form wire:submit.prevent="login" class="space-y-5">
                <!-- Username / Email Field -->
                <div>
                    <label for="loginInput" class="block text-xs font-medium text-gray-700 mb-1.5">
                        Username / Email <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="loginInput" wire:model.defer="loginInput"
                        placeholder="Masukkan username atau email"
                        class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition duration-150"
                        autofocus />
                    @error('loginInput')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-xs font-medium text-gray-700 mb-1.5">
                        Kata Sandi <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" wire:model.defer="password" placeholder="Masukkan kata sandi"
                        class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition duration-150" />
                    @error('password')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Checkbox -->
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" wire:model.defer="remember"
                            class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-lg shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer flex items-center justify-center gap-2">
                    <svg wire:loading class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove>Masuk</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </form>
        </div>

        <!-- Clean Footer -->
        <div class="text-center text-xs text-gray-400 pt-2">
            &copy; {{ date('Y') }} Raabiha
        </div>
    </div>
</div>