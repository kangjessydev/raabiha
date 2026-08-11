<div x-data="posSystem()" wire:poll.5s="checkTakeoverRequest" class="h-screen w-full flex flex-col md:flex-row overflow-hidden bg-gray-50/50 relative">
    @php
        $posLogoSetting = \App\Models\SiteSetting::where('key', 'pos_ui_logo')->first();
        $posReceiptLogoSetting = \App\Models\SiteSetting::where('key', 'pos_receipt_logo_enabled')->first();
        $posReceiptLogoEnabled = filter_var($posReceiptLogoSetting?->value ?? false, FILTER_VALIDATE_BOOLEAN);

        $posLogoUrl = asset('assets/images/pos-logo-icon.png');
        if ($posLogoSetting && $posLogoSetting->value) {
            $media = \Awcodes\Curator\Models\Media::find($posLogoSetting->value);
            if ($media) {
                $posLogoUrl = $media->url;
            }
        }
    @endphp
    <!-- Sleek POS Fullscreen Splash Loading Screen -->
    <div x-data="{ isInitializing: true }" 
         x-init="setTimeout(() => isInitializing = false, 300)"
         x-show="isInitializing" 
         x-cloak
         x-transition:leave="transition ease-out duration-300"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-98"
         class="fixed inset-0 z-[100] bg-white flex flex-col items-center justify-center font-sans">
        <div class="flex flex-col items-center space-y-4 text-center">
            <!-- Brand Logo Icon (Gambar 1) -->
            <div class="w-16 h-16 flex items-center justify-center">
                <img src="{{ $posLogoUrl }}" alt="Raabiha Logo" class="w-16 h-16 object-contain animate-pulse">
            </div>
            <!-- Title & Status Text -->
            <div>
                <h2 class="text-base font-bold text-gray-950 tracking-tight">Kasir POS Raabiha</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Memuat sistem kasir & memulihkan keranjang...</p>
            </div>
            <!-- Loading Indicator -->
            <div class="w-32 h-1 bg-gray-100 rounded-full overflow-hidden relative mt-2">
                <div class="absolute left-0 top-0 bottom-0 bg-emerald-600 rounded-full animate-pulse w-full"></div>
            </div>
        </div>
    </div>

    <!-- Global Processing Spinner Overlay -->
    <div x-show="isProcessing && !previewReceiptData" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[150] flex flex-col items-center justify-center bg-gray-900/40 backdrop-blur-sm font-sans">
        <div class="bg-white p-6 rounded-2xl shadow-xl flex flex-col items-center max-w-sm w-full mx-4 border border-gray-100">
            <svg class="animate-spin h-10 w-10 text-emerald-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <h3 class="text-gray-900 font-bold text-lg mb-1">Memproses Transaksi</h3>
            <p class="text-gray-500 text-sm text-center">Mohon tunggu sebentar, sistem sedang memproses pembayaran dan menyiapkan struk Anda...</p>
        </div>
    </div>

    <!-- MODAL GLOBAL: Otorisasi PIN Supervisor (Clean Filament Native Style) -->
    <div x-show="showSupervisorPinModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[120] flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4 font-sans" style="display: none;">
        <div @click.away="showSupervisorPinModal = false"
             x-show="showSupervisorPinModal"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white border border-gray-200 rounded-xl p-6 sm:p-8 shadow-xl w-full max-w-md space-y-5 text-center">
            
            <div class="space-y-1">
                <h3 class="text-xl font-bold tracking-tight text-gray-900">Otorisasi Supervisor</h3>
                <p class="text-xs font-medium text-gray-500" x-text="supervisorReasonMessage || 'Tindakan ini memerlukan verifikasi PIN Supervisor/Manager.'"></p>
            </div>

            <form @submit.prevent="submitSupervisorAuth()" class="space-y-4 text-left">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">1. Pilih Supervisor yang Bertugas <span class="text-red-500">*</span></label>
                    <select x-model="selectedSupervisorId" class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 text-sm font-medium py-2.5 px-3.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition duration-150">
                        <option value="">-- Pilih Supervisor / Manager --</option>
                        <template x-for="sup in supervisors" :key="sup.id">
                            <option :value="sup.id" x-text="sup.name + ' (' + sup.role + ')'"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-medium text-gray-700">2. Masukkan 6-Digit PIN Supervisor <span class="text-red-500">*</span></label>
                        <button type="button" @click="showSupervisorPinText = !showSupervisorPinText" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 focus:outline-none cursor-pointer flex items-center gap-1" tabindex="-1">
                            <span x-text="showSupervisorPinText ? 'Sembunyikan' : 'Lihat PIN'"></span>
                        </button>
                    </div>
                    <div class="relative cursor-pointer" @click="$refs.supervisorPinField.focus()">
                        <input 
                            x-ref="supervisorPinField"
                            type="text" 
                            id="posSupervisorPinField" 
                            maxlength="6" 
                            pattern="[0-9]*" 
                            inputmode="numeric" 
                            :value="supervisorPinInput"
                            @input="supervisorPinInput = $event.target.value.replace(/\D/g, '').slice(0, 6); supervisorErrorMessage = ''"
                            class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10"
                        />
                        <div class="grid grid-cols-6 gap-2">
                            <template x-for="i in 6" :key="i">
                                <div class="h-11 rounded-lg border text-center flex items-center justify-center text-lg font-bold transition-all duration-150"
                                    :class="{
                                        'border-red-500 ring-2 ring-red-500/20 bg-red-50/50 text-red-600': supervisorErrorMessage,
                                        'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/40': !supervisorErrorMessage && (supervisorPinInput || '').length === i - 1,
                                        'border-emerald-600 bg-white text-emerald-700 shadow-xs': !supervisorErrorMessage && (supervisorPinInput || '').length >= i,
                                        'border-gray-300 bg-gray-50/60 text-gray-400': !supervisorErrorMessage && (supervisorPinInput || '').length < i - 1
                                    }">
                                    <span x-show="!showSupervisorPinText && String(supervisorPinInput || '').length >= i" class="w-2.5 h-2.5 rounded-full inline-block" :class="supervisorErrorMessage ? 'bg-red-500 animate-pulse' : 'bg-emerald-600'"></span>
                                    <span x-show="showSupervisorPinText && String(supervisorPinInput || '').length >= i" class="font-bold text-base" :class="supervisorErrorMessage ? 'text-red-600' : 'text-emerald-700'" x-text="String(supervisorPinInput || '')[i - 1]"></span>
                                    <span x-show="String(supervisorPinInput || '').length < i" class="w-2 h-2 rounded-full inline-block" :class="supervisorErrorMessage ? 'bg-red-300' : 'bg-gray-300'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div x-show="supervisorErrorMessage" class="text-xs text-red-600 font-medium mt-1.5" x-text="supervisorErrorMessage"></div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showSupervisorPinModal = false" class="flex-1 py-2 px-4 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-medium text-sm rounded-lg shadow-sm transition duration-150 cursor-pointer">Batal</button>
                    <button type="submit" :disabled="!selectedSupervisorId || !supervisorPinInput || String(supervisorPinInput).trim().length !== 6"
                            wire:loading.attr="disabled"
                            wire:target="verifySupervisorPin"
                            class="flex-1 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-medium text-sm rounded-lg shadow-sm transition duration-150 cursor-pointer flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="verifySupervisorPin">Verifikasi PIN</span>
                        <span wire:loading.flex wire:target="verifySupervisorPin" class="items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memeriksa...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Device Blocked Overlay -->
    @if($deviceBlocked)
    <div x-data="{ takeoverMode: null, codeInput: '', supervisorId: '', supervisorPin: '' }" 
         @takeover-rejected.window="takeoverMode = null; codeInput = '';"
         class="fixed inset-0 z-[110] bg-gray-900/95 flex flex-col items-center justify-center font-sans text-white p-6">
        <div wire:ignore class="bg-white text-gray-900 rounded-2xl p-8 max-w-md w-full shadow-2xl text-center relative overflow-hidden">
            
            <!-- Default View -->
            <div x-show="!takeoverMode" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-xl font-bold mb-2">Perangkat Lain Sedang Aktif</h2>
                <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                    Akun kasir Anda terdeteksi sedang digunakan pada perangkat lain. Untuk mengambil alih sesi ini, silakan pilih metode otorisasi.
                </p>
                <div class="flex flex-col gap-3">
                    <button type="button" @click="takeoverMode = 'code'; $wire.requestTakeover()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl shadow-xs transition duration-150 flex items-center justify-center cursor-pointer">
                        Minta Kode Ambil Alih
                    </button>
                    <button type="button" @click="takeoverMode = 'supervisor'" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-xl shadow-xs transition duration-150 flex items-center justify-center cursor-pointer">
                        Bypass (PIN Supervisor)
                    </button>
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl transition duration-150 text-center mt-2">
                        Kembali ke Admin
                    </a>
                </div>
            </div>

            <!-- Code Mode -->
            <div x-show="takeoverMode === 'code'" style="display:none;" wire:poll.3s="checkTakeoverStatus" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <button type="button" @click="takeoverMode = null" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </button>
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-xl font-bold mb-2">Masukkan Kode</h2>
                <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                    Persetujuan telah dikirim ke perangkat aktif. Jika disetujui, masukkan 6 digit kode yang muncul di layar perangkat tersebut.
                </p>
                <input type="text" x-model="codeInput" maxlength="6" class="w-full text-center text-2xl tracking-[0.5em] font-bold py-3 border-2 border-gray-300 rounded-xl mb-4 focus:border-emerald-500 focus:ring-emerald-500 transition outline-none" placeholder="------" />
                <button type="button" @click="$wire.submitTakeoverCode(codeInput)" :disabled="codeInput.length !== 6" class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold py-3 px-4 rounded-xl shadow-xs transition duration-150 flex items-center justify-center gap-2 cursor-pointer">
                    <span wire:loading.remove wire:target="submitTakeoverCode">Verifikasi Kode</span>
                    <span wire:loading.flex wire:target="submitTakeoverCode" class="items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memeriksa...
                    </span>
                </button>
            </div>

            <!-- Supervisor Mode -->
            <div x-show="takeoverMode === 'supervisor'" style="display:none;" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <button type="button" @click="takeoverMode = null" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </button>
                <div class="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h2 class="text-xl font-bold mb-2">Otorisasi Supervisor</h2>
                <p class="text-gray-500 text-sm mb-4 leading-relaxed">
                    Masukkan PIN Supervisor untuk mengambil alih sesi secara paksa. Aksi ini akan dicatat ke dalam audit.
                </p>
                
                <div class="text-left mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Supervisor</label>
                    <select x-model="supervisorId" class="w-full rounded-lg border border-gray-300 bg-white py-2.5 px-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                        <option value="">-- Pilih Supervisor --</option>
                        @foreach(\App\Models\User::where('is_pos_supervisor', true)->get() as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="text-left mb-6">
                    <label class="block text-xs font-medium text-gray-700 mb-1">PIN Supervisor (6 Digit)</label>
                    <input type="password" x-model="supervisorPin" maxlength="6" class="w-full text-center text-xl tracking-[0.5em] font-bold py-2.5 border-2 border-gray-300 rounded-xl focus:border-emerald-500 focus:ring-emerald-500 transition outline-none" placeholder="------" />
                </div>

                <button type="button" @click="$wire.forceTakeoverWithSupervisor(supervisorId, supervisorPin)" :disabled="!supervisorId || supervisorPin.length !== 6" class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-bold py-3 px-4 rounded-xl shadow-xs transition duration-150 flex items-center justify-center gap-2 cursor-pointer">
                    <span wire:loading.remove wire:target="forceTakeoverWithSupervisor">Bypass Sesi</span>
                    <span wire:loading.flex wire:target="forceTakeoverWithSupervisor" class="items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memeriksa...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Takeover Request / Code Notification Modal (Device A) -->
    @if($takeoverRequestedByOther || $generatedTakeoverCode)
    <div class="fixed inset-0 z-[110] bg-gray-900/90 flex flex-col items-center justify-center font-sans text-white p-6">
        <div class="bg-white text-gray-900 rounded-2xl p-8 max-w-md w-full shadow-2xl text-center relative overflow-hidden">
            
            @if($takeoverRequestedByOther)
            <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h2 class="text-xl font-bold mb-2">Permintaan Ambil Alih Sesi</h2>
            <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                Terdapat perangkat lain yang mencoba mengambil alih sesi kasir ini. Jika Anda menyetujuinya, sesi di perangkat ini akan diputus.
            </p>
            <div class="flex gap-3">
                <button type="button" wire:click="rejectTakeoverRequest" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl transition cursor-pointer">
                    Tolak
                </button>
                <button type="button" wire:click="approveTakeoverRequest" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl transition cursor-pointer flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="approveTakeoverRequest">Izinkan</span>
                    <span wire:loading.flex wire:target="approveTakeoverRequest" class="items-center">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                </button>
            </div>
            @elseif($generatedTakeoverCode)
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-xl font-bold mb-2">Kode Otorisasi</h2>
            <p class="text-gray-500 text-sm mb-4 leading-relaxed">
                Berikan 6 digit angka ini ke perangkat baru untuk menyelesaikan proses ambil alih sesi.
            </p>
            <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl py-6 mb-6">
                <span class="text-4xl tracking-[0.2em] font-black text-gray-900">{{ $generatedTakeoverCode }}</span>
            </div>
            <p class="text-xs text-red-500 font-medium">Sesi ini akan otomatis diblokir saat perangkat baru berhasil memasukkan kode.</p>
            @endif
        </div>
    </div>
    @endif

    <style>
    @keyframes pinShake {
        0%, 100% { transform: translateX(0); }
        15%, 45%, 75% { transform: translateX(-8px); }
        30%, 60%, 90% { transform: translateX(8px); }
    }
    .animate-pin-shake {
        animation: pinShake 0.45s ease-in-out both;
    }
    </style>

    <!-- Notifications Toast (with Close Button & Status Color Variants) -->
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        <template x-for="(toast, index) in toasts" :key="toast.id">
            <div x-show="true" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="pointer-events-auto px-4 py-3 rounded-xl flex items-center justify-between gap-3 shadow-lg border text-sm font-semibold transition-all"
                 :class="{
                     'border-red-300 bg-red-50 text-red-900': toast.type === 'error',
                     'border-amber-300 bg-amber-50 text-amber-900': toast.type === 'warning',
                     'border-sky-300 bg-sky-50 text-sky-900': toast.type === 'info',
                     'border-emerald-300 bg-emerald-50 text-emerald-900': toast.type === 'success' || !['error', 'warning', 'info'].includes(toast.type)
                 }">
                <div class="flex items-center gap-2">
                    <template x-if="toast.type === 'error'">
                        <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="w-4 h-4 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="toast.type === 'success' || !['error', 'warning', 'info'].includes(toast.type)">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <span x-text="toast.message"></span>
                </div>
                <button type="button" @click="removeToast(toast.id)" class="p-1 rounded-md text-gray-400 hover:text-gray-700 hover:bg-black/5 transition cursor-pointer" title="Tutup Notifikasi">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Cek PIN & Session -->
    @if(!$hasPosPin)
        <!-- Overlay Buat PIN POS (Clean Filament Native Style) -->
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4 font-sans">
            <div class="bg-white border border-gray-200 rounded-xl p-6 sm:p-8 shadow-xl w-full max-w-md space-y-5 text-center">
                <div class="space-y-1">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Buat PIN POS 6-Digit</h2>
                    <p class="text-xs font-medium text-gray-500">Untuk keamanan transaksi kasir, Anda wajib membuat PIN POS 6-digit pertama Anda sebelum menggunakan aplikasi POS.</p>
                </div>

                <form wire:submit.prevent="saveInitialPosPin" 
                      @pin-creation-failed.window="
                          isPinError = true;
                          setTimeout(() => {
                              pin1 = '';
                              pin2 = '';
                              $wire.set('posPinInput', '');
                              $wire.set('posPinConfirm', '');
                              isPinError = false;
                              $refs.inputPin1.focus();
                          }, 600);
                      "
                      class="space-y-5 text-left" 
                      x-data="{
                          pin1: $wire.entangle('posPinInput'),
                          pin2: $wire.entangle('posPinConfirm'),
                          showPin1: false,
                          showPin2: false,
                          isPinError: false,
                          updatePin1(val) {
                              this.isPinError = false;
                              let clean = val.replace(/\D/g, '').slice(0, 6);
                              this.pin1 = clean;
                          },
                          updatePin2(val) {
                              this.isPinError = false;
                              let clean = val.replace(/\D/g, '').slice(0, 6);
                              this.pin2 = clean;
                          }
                      }" x-init="$refs.inputPin1.focus()">
                    <!-- PIN POS Baru -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-medium text-gray-700">
                                PIN POS Baru (6 Digit) <span class="text-red-500">*</span>
                            </label>
                            <button type="button" @click="showPin1 = !showPin1" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 focus:outline-none cursor-pointer flex items-center gap-1" tabindex="-1">
                                <span x-text="showPin1 ? 'Sembunyikan' : 'Lihat PIN'"></span>
                            </button>
                        </div>

                        <!-- 6 Boxes Indicator -->
                        <div class="relative cursor-pointer" @click="$refs.inputPin1.focus()">
                            <input 
                                x-ref="inputPin1"
                                type="text" 
                                id="posPinInput"
                                maxlength="6" 
                                pattern="[0-9]*" 
                                inputmode="numeric" 
                                :value="pin1"
                                @input="updatePin1($event.target.value)"
                                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10"
                                autofocus
                            />
                            <div class="grid grid-cols-6 gap-2.5" :class="isPinError ? 'animate-pin-shake' : ''">
                                <template x-for="i in 6" :key="i">
                                    <div 
                                        class="h-12 rounded-lg border text-center flex items-center justify-center text-lg font-bold transition-all duration-150"
                                        :class="{
                                            'border-red-500 ring-2 ring-red-500/20 bg-red-50/50 text-red-600': isPinError,
                                            'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/40': !isPinError && (pin1 || '').length === i - 1,
                                            'border-emerald-600 bg-white text-emerald-700 shadow-xs': !isPinError && (pin1 || '').length >= i,
                                            'border-gray-300 bg-gray-50/60 text-gray-400': !isPinError && (pin1 || '').length < i - 1
                                        }"
                                    >
                                        <template x-if="(pin1 || '').length >= i">
                                            <span x-show="!showPin1" class="w-3 h-3 rounded-full inline-block" :class="isPinError ? 'bg-red-500 animate-pulse' : 'bg-emerald-600'"></span>
                                        </template>
                                        <template x-if="(pin1 || '').length >= i">
                                            <span x-show="showPin1" class="font-bold text-lg" :class="isPinError ? 'text-red-600' : 'text-emerald-700'" x-text="(pin1 || '')[i - 1]"></span>
                                        </template>
                                        <template x-if="(pin1 || '').length < i">
                                            <span class="w-2 h-2 rounded-full inline-block" :class="isPinError ? 'bg-red-300' : 'bg-gray-300'"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('posPinInput') <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Konfirmasi PIN POS -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-medium text-gray-700">
                                Konfirmasi PIN POS (6 Digit) <span class="text-red-500">*</span>
                            </label>
                            <button type="button" @click="showPin2 = !showPin2" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 focus:outline-none cursor-pointer flex items-center gap-1" tabindex="-1">
                                <span x-text="showPin2 ? 'Sembunyikan' : 'Lihat PIN'"></span>
                            </button>
                        </div>

                        <!-- 6 Boxes Indicator -->
                        <div class="relative cursor-pointer" @click="$refs.inputPin2.focus()">
                            <input 
                                x-ref="inputPin2"
                                type="text" 
                                id="posPinConfirm"
                                maxlength="6" 
                                pattern="[0-9]*" 
                                inputmode="numeric" 
                                :value="pin2"
                                @input="updatePin2($event.target.value)"
                                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10"
                            />
                            <div class="grid grid-cols-6 gap-2.5" :class="isPinError ? 'animate-pin-shake' : ''">
                                <template x-for="i in 6" :key="i">
                                    <div 
                                        class="h-12 rounded-lg border text-center flex items-center justify-center text-lg font-bold transition-all duration-150"
                                        :class="{
                                            'border-red-500 ring-2 ring-red-500/20 bg-red-50/50 text-red-600': isPinError,
                                            'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/40': !isPinError && (pin2 || '').length === i - 1,
                                            'border-emerald-600 bg-white text-emerald-700 shadow-xs': !isPinError && (pin2 || '').length >= i,
                                            'border-gray-300 bg-gray-50/60 text-gray-400': !isPinError && (pin2 || '').length < i - 1
                                        }"
                                    >
                                        <template x-if="(pin2 || '').length >= i">
                                            <span x-show="!showPin2" class="w-3 h-3 rounded-full inline-block" :class="isPinError ? 'bg-red-500 animate-pulse' : 'bg-emerald-600'"></span>
                                        </template>
                                        <template x-if="(pin2 || '').length >= i">
                                            <span x-show="showPin2" class="font-bold text-lg" :class="isPinError ? 'text-red-600' : 'text-emerald-700'" x-text="(pin2 || '')[i - 1]"></span>
                                        </template>
                                        <template x-if="(pin2 || '').length < i">
                                            <span class="w-2 h-2 rounded-full inline-block" :class="isPinError ? 'bg-red-300' : 'bg-gray-300'"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('posPinConfirm') <p class="text-red-600 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <button 
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-lg shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer flex items-center justify-center gap-2 mt-2"
                    >
                        <svg wire:loading wire:target="saveInitialPosPin" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveInitialPosPin">Simpan & Aktifkan PIN POS</span>
                        <span wire:loading wire:target="saveInitialPosPin">Memproses...</span>
                    </button>
                </form>
            </div>
        </div>

    @elseif(!$activeSession)
        <!-- Overlay Buka Shift (Clean Filament Native Style) -->
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4 font-sans"
             x-data="{
                 displayCash: '',
                 isSubmitting: false,
                 formatRupiah(val) {
                     if (!val || val === 0 || val === '0') return '';
                     let num = val.toString().replace(/\D/g, '');
                     return num ? parseInt(num, 10).toLocaleString('id-ID') : '';
                 },
                 updateCash(val) {
                     let clean = val.replace(/\D/g, '');
                     this.displayCash = clean ? parseInt(clean, 10).toLocaleString('id-ID') : '';
                     $wire.set('openingCash', clean ? parseInt(clean, 10) : 0);
                 },
                 async submitOpenSession() {
                     if (this.isSubmitting) return;
                     this.isSubmitting = true;
                     try {
                         await $wire.openSession();
                     } finally {
                         this.isSubmitting = false;
                     }
                 }
             }"
             x-init="displayCash = formatRupiah($wire.openingCash)">
            <div class="bg-white border border-gray-200 rounded-xl p-6 sm:p-8 shadow-lg w-full max-w-md space-y-6">
                <div class="text-center space-y-1.5">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Buka Shift Kasir</h2>
                    <p class="text-xs font-medium text-gray-500">Masukkan modal awal (uang kas di laci) untuk memulai transaksi.</p>
                </div>
                
                <div class="space-y-5">
                    <div>
                        <label for="openingCashInput" class="block text-xs font-medium text-gray-700 mb-1.5">
                            Modal Awal (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-gray-400">Rp</span>
                            <input 
                                type="text" 
                                id="openingCashInput"
                                x-model="displayCash"
                                @input="updateCash($event.target.value)"
                                @keydown.enter.prevent="submitOpenSession()"
                                class="w-full pl-10 pr-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-900 text-base font-semibold placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition duration-150" 
                                placeholder="0"
                                autofocus
                            />
                        </div>
                        @error('openingCash') 
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>
                    <button 
                        type="button"
                        @click="submitOpenSession()"
                        :disabled="isSubmitting"
                        wire:loading.attr="disabled"
                        wire:target="openSession"
                        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white font-medium text-sm rounded-lg shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer flex items-center justify-center gap-2"
                    >
                        <svg wire:loading wire:target="openSession" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="openSession">Mulai Sesi Shift</span>
                        <span wire:loading wire:target="openSession">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- MAIN POS AREA -->

        <!-- Backdrop for mobile sidebar drawer -->
        <div x-show="isSidebarOpen" 
             @click="isSidebarOpen = false" 
             x-cloak 
             class="md:hidden fixed inset-0 z-30 bg-gray-950/50 backdrop-blur-xs"></div>

        <!-- Sidebar (Clean Filament Native Style) -->
        <div :class="isSidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full md:translate-x-0 md:w-20'" class="fixed md:relative inset-y-0 left-0 bg-white border-r border-gray-200 flex flex-col justify-between h-full transition-all duration-300 z-40 flex-shrink-0 font-sans shadow-xl md:shadow-none">
            <div class="overflow-y-auto overflow-x-hidden no-scrollbar">
                <!-- Logo -->
                <div class="h-16 flex items-center border-b border-gray-200 px-4 transition-all duration-300" :class="isSidebarOpen ? 'justify-start' : 'justify-center'">
                    <div class="flex items-center gap-3">
                        <img src="{{ $posLogoUrl }}" alt="Raabiha Logo" class="w-8 h-8 object-contain flex-shrink-0">
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-bold text-gray-900 text-lg tracking-tight whitespace-nowrap">Raabiha</span>
                    </div>
                </div>
                
                <!-- Menus -->
                <nav class="p-3 space-y-1 mt-1">
                    <button @click="activePage = 'kasir'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer" :class="[activePage==='kasir' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Kasir">
                        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Kasir</span>
                    </button>
                    <button @click="activePage = 'history'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer" :class="[activePage==='history' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Riwayat Transaksi">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Riwayat Transaksi</span>
                    </button>
                    <button @click="activePage = 'returns'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer" :class="[activePage==='returns' ? 'bg-amber-50 text-amber-800 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Riwayat Retur">
                        <svg class="w-5 h-5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Riwayat Retur</span>
                    </button>
                    <button @click="activePage = 'customers'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer" :class="[activePage==='customers' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Pelanggan">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Pelanggan</span>
                    </button>
                    <button @click="activePage = 'reserved'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer relative" :class="[activePage==='reserved' ? 'bg-blue-50 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Pesanan Dipesan">
                        <div class="relative flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            @if(($totalReservedCount ?? 0) > 0)
                                <span class="absolute -top-1.5 -right-2 min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-black rounded-full shadow-xs border-2 border-white {{ ($overdueCount ?? 0) > 0 ? 'bg-rose-600 text-white animate-pulse' : (($todayCount ?? 0) > 0 ? 'bg-amber-500 text-white' : 'bg-blue-600 text-white') }}">
                                    {{ $totalReservedCount }}
                                </span>
                            @endif
                        </div>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap flex items-center justify-between w-full">
                            <span>Dipesan</span>
                            @if(($totalReservedCount ?? 0) > 0)
                                <span class="ml-1.5 px-2 py-0.5 text-[10px] font-extrabold rounded-full {{ ($overdueCount ?? 0) > 0 ? 'bg-rose-100 text-rose-800' : (($todayCount ?? 0) > 0 ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">{{ $totalReservedCount }}</span>
                            @endif
                        </span>
                    </button>
                    <button @click="activePage = 'cashsummary'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer" :class="[activePage==='cashsummary' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Rekap Kas">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Rekap Kas</span>
                    </button>
                    <button @click="lockScreen()" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer" :class="[isSidebarOpen ? 'justify-start' : 'justify-center', 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']" title="Kunci Layar">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Kunci Layar</span>
                    </button>
                    
                    <div class="pt-3 mt-3 border-t border-gray-200">
                        <!-- Printer -->
                        <div class="px-1" :class="isSidebarOpen ? 'px-1' : 'px-0'">
                            <label x-show="isSidebarOpen" class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5 pl-2 transition-opacity duration-300 block">Perangkat</label>
                            <div class="flex flex-col gap-1.5 p-1.5 rounded-lg transition-all" :class="isSidebarOpen ? 'bg-gray-50 border border-gray-200' : 'bg-transparent border-transparent'">
                                <select x-show="isSidebarOpen" x-model="printerType" class="w-full text-xs border-none bg-transparent focus:ring-0 cursor-pointer text-gray-700 font-medium py-1 transition-opacity duration-300">
                                    <option value="bluetooth">Bluetooth</option>
                                    <option value="serial">USB/Serial</option>
                                </select>
                                <button @click="showPrinterModal = true" :class="[printerConnected ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white hover:border-red-300 hover:text-red-600 text-red-600 border-gray-200', isSidebarOpen ? 'justify-center px-3 py-1.5 border shadow-xs' : 'justify-center p-2.5']" class="w-full rounded-md text-xs font-semibold transition-all flex items-center gap-2 relative cursor-pointer" title="Pengaturan Printer POS">
                                    <span class="relative flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        <span x-show="!isSidebarOpen" class="absolute -top-1 -right-1 h-2 w-2 rounded-full border-2 border-white" :class="printerConnected ? 'bg-emerald-500' : 'bg-red-500 animate-ping'" style="display:none;"></span>
                                    </span>
                                    <span x-show="isSidebarOpen" x-text="printerConnected ? 'Tersambung' : 'Belum Terhubung'" class="whitespace-nowrap"></span>
                                </button>
                                <label x-show="isSidebarOpen" class="flex items-center gap-2 mt-2 px-1 cursor-pointer">
                                    <input type="checkbox" x-model="autoPrintReceipt" @change="saveAutoPrintSettings" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                                    <span class="text-xs text-gray-700">Auto Print Struk</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
            
            <!-- Profile / User Menu -->
            <div class="p-3 border-t border-gray-200 bg-white" x-data="{ showUserMenu: false }" @click.away="showUserMenu = false">
                <!-- Profile Button -->
                <button @click="showUserMenu = !showUserMenu"
                    class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-all cursor-pointer"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
                    title="{{ auth()->user()->name }}">

                    {{-- Avatar Inisial --}}
                    <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    {{-- Nama & Email (muncul saat sidebar terbuka) --}}
                    <div x-show="isSidebarOpen" x-transition.opacity.duration.200ms class="flex-1 text-left overflow-hidden">
                        <div class="text-xs font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</div>
                        @if(auth()->user()->email)
                            <div class="text-[11px] text-gray-500 truncate">{{ auth()->user()->email }}</div>
                        @endif
                    </div>

                    {{-- Chevron --}}
                    <svg x-show="isSidebarOpen" x-transition.opacity.duration.200ms class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="showUserMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Popup -->
                <div x-show="showUserMenu"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute bottom-[68px] left-3 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50"
                     :class="isSidebarOpen ? 'w-[calc(100%-24px)]' : 'w-60'"
                     style="display:none;">

                    {{-- Header Info --}}
                    <div class="px-3.5 py-2.5 bg-gray-50 border-b border-gray-200">
                        <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Masuk sebagai</div>
                        <div class="font-semibold text-xs text-gray-900 mt-0.5">{{ auth()->user()->name }}</div>
                        @if(auth()->user()->email)
                            <div class="text-[11px] text-gray-500 truncate">{{ auth()->user()->email }}</div>
                        @endif
                    </div>

                    <div class="p-1.5 space-y-0.5">
                        {{-- Tutup Shift --}}
                        <button @click="showUserMenu = false; showCloseSession = true;"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-left rounded-lg text-rose-600 hover:bg-rose-50 transition-colors font-medium text-xs cursor-pointer">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Tutup Shift Kasir
                        </button>

                        {{-- Kunci Layar --}}
                        <button @click="showUserMenu = false; lockScreen();"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-left rounded-lg text-amber-700 hover:bg-amber-50 transition-colors font-medium text-xs cursor-pointer">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Kunci Layar POS
                        </button>

                        {{-- Ganti PIN POS --}}
                        <button @click="showUserMenu = false; showChangePinModal = true;"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-left rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium text-xs cursor-pointer">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Ganti PIN POS (6-Digit)
                        </button>

                        {{-- Ke Admin (hanya untuk yang boleh) --}}
                        @if(auth()->user()->hasAnyRole(['super_admin', 'owner', 'panel_user', 'marketing', 'finance', 'logistics', 'cs']))
                        <a href="{{ url('/admin') }}"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-left rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium text-xs cursor-pointer">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Admin Panel
                        </a>
                        @endif

                        <div class="border-t border-gray-100 my-1"></div>

                        {{-- Keluar --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-left rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors font-medium text-xs cursor-pointer">
                                <svg class="w-4 h-4 flex-shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Center: Products Area (Kasir - Clean Filament Native Style) -->
        <div x-show="activePage === 'kasir'" x-cloak wire:key="pos-page-kasir" class="flex-1 flex flex-col h-full bg-gray-50/40 min-w-0 overflow-hidden font-sans">
            <!-- Header/Search (Filament Native Style) -->
            <div class="bg-white border-b border-gray-200 p-3.5 sticky top-0 z-10 flex items-center gap-3 shadow-xs">
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Toggle Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="flex-1 relative max-w-2xl">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition duration-150" placeholder="Cari Produk atau Barcode / SKU...">
                </div>
                <!-- Printer Status Indicator (Filament Native Badge) -->
                <button @click="showPrinterModal = true"
                    :title="printerConnected ? 'Printer: Tersambung (klik untuk pengaturan)' : 'Printer: Belum Terhubung — Klik untuk Pengaturan'"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-md border text-xs font-semibold transition-all cursor-pointer"
                    :class="printerConnected
                        ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100'
                        : 'bg-red-50 border-red-200 text-red-700 hover:bg-red-100'">
                    <span class="relative flex h-2 w-2 flex-shrink-0">
                        <span class="relative inline-flex rounded-full h-2 w-2"
                              :class="printerConnected ? 'bg-emerald-600' : 'bg-red-600'"></span>
                    </span>
                    <span x-text="printerConnected ? 'Printer OK' : 'Printer Offline'" class="hidden sm:inline whitespace-nowrap"></span>
                </button>

                <!-- Tombol Input Produk Kustom Fast Entry -->
                <button type="button" @click="showCustomProductModal = true"
                    title="Tambah Produk Kustom / Nego Impor"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-amber-300 text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span class="hidden md:inline">Produk Kustom</span>
                </button>

                <!-- Tombol Muat Ulang POS (Refresh) -->
                <button type="button" @click="reloadPos()"
                    title="Muat Ulang POS / Refresh Halaman"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 text-xs font-semibold bg-white hover:bg-gray-50 text-gray-700 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span class="hidden md:inline">Muat Ulang</span>
                </button>

                <!-- Tombol Install Aplikasi Desktop PC (PWA) -->
                <button x-show="canInstallApp" type="button" @click="installApp()"
                    title="Install Raabiha POS sebagai Aplikasi Desktop PC"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-emerald-300 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="hidden sm:inline">Install App Desktop</span>
                </button>

                <!-- Button Antrean (Khusus Layar Mobile & Tablet < lg) -->
                <button type="button" @click="showHoldModal = true"
                        class="lg:hidden flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-md shadow-xs transition duration-150 cursor-pointer relative"
                        title="Lihat Pesanan Tertahan (Antrean)">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="hidden sm:inline">Antrean</span>
                    <template x-if="heldCarts.length > 0">
                        <span class="w-4 h-4 bg-amber-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center" x-text="heldCarts.length"></span>
                    </template>
                </button>
            </div>

            <!-- Product Grid (Fluid Auto-Fill Grid - Auto Responsive when Sidebar Opens) -->
            <div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6">
                <div class="grid gap-3 sm:gap-4 [grid-template-columns:repeat(auto-fill,minmax(170px,1fr))] pb-24 lg:pb-4">
                    @forelse($products as $product)
                        @php
                            // Cek jika produk punya varian
                            $hasVariants = $product->variants->count() > 0;
                            $computedStock = $product->computed_stock ?? ($hasVariants ? $product->variants->sum('stock') : $product->stock);
                            $isOutOfStock = $computedStock <= 0;
                            
                            // Harga display & Cek Promo Event POS Aktif
                            $hasPromo = false;
                            $promoPrice = null;
                            $originalPrice = null;
                            $priceDisplay = '';
                            $promoBadge = null;

                            $activeEventPromo = $this->activeEventPromotions->first(fn($ep) => $ep->isProductEligible($product->id, $product->category_id));
                            $basePrice = $product->pos_price ?: $product->price;

                            if ($activeEventPromo) {
                                $hasPromo = true;
                                $originalPrice = $basePrice;
                                if ($activeEventPromo->discount_type === 'percent') {
                                    $discVal = (float) $activeEventPromo->discount_amount;
                                    $promoPrice = max(0, $basePrice * (1 - ($discVal / 100)));
                                    $promoBadge = $activeEventPromo->name . ' -' . round($discVal) . '%';
                                } else {
                                    $discVal = (float) $activeEventPromo->discount_amount;
                                    $promoPrice = max(0, $basePrice - $discVal);
                                    $promoBadge = $activeEventPromo->name . ' -Rp ' . number_format($discVal, 0, ',', '.');
                                }
                                $priceDisplay = 'Rp ' . number_format($promoPrice, 0, ',', '.');
                            } elseif ($product->pos_discount_price) {
                                $hasPromo = true;
                                $promoPrice = $product->pos_discount_price;
                                $originalPrice = $basePrice;
                                $priceDisplay = 'Rp ' . number_format($promoPrice, 0, ',', '.');
                            } elseif ($product->pos_price) {
                                $originalPrice = $product->pos_price;
                                $priceDisplay = 'Rp ' . number_format($originalPrice, 0, ',', '.');
                            } elseif ($hasVariants) {
                                $minPrice = $product->variants->min('price');
                                $maxPrice = $product->variants->max('price');
                                if ($minPrice != $maxPrice) {
                                    $priceDisplay = 'Rp ' . number_format($minPrice, 0, ',', '.') . ' - Rp ' . number_format($maxPrice, 0, ',', '.');
                                } else {
                                    $priceDisplay = 'Rp ' . number_format($minPrice, 0, ',', '.');
                                }
                                $originalPrice = $minPrice;
                            } else {
                                if ($product->discount_price) {
                                    $hasPromo = true;
                                    $promoPrice = $product->discount_price;
                                    $originalPrice = $product->price;
                                    $priceDisplay = 'Rp ' . number_format($promoPrice, 0, ',', '.');
                                } else {
                                    $originalPrice = $product->price;
                                    $priceDisplay = 'Rp ' . number_format($originalPrice, 0, ',', '.');
                                }
                            }
                            $priceForJs = $hasPromo ? $promoPrice : $originalPrice;
                            
                            $imageUrl = asset('assets/images/placeholder.webp');
                            if (!empty($product->images)) {
                                if (is_numeric($product->images[0])) {
                                    $media = \Awcodes\Curator\Models\Media::find($product->images[0]);
                                    if ($media) $imageUrl = Storage::url($media->path);
                                } else {
                                    if (Storage::disk('public')->exists($product->images[0])) {
                                        $imageUrl = asset('storage/' . $product->images[0]);
                                    } else {
                                        $imageUrl = asset($product->images[0]);
                                    }
                                }
                            }
                            $image = $imageUrl;
                        @endphp
                        
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden {{ $isOutOfStock ? 'opacity-60 cursor-not-allowed' : 'hover:border-emerald-500 hover:shadow-md cursor-pointer group' }} transition duration-150 relative flex flex-col justify-between"
                             @if(!$isOutOfStock)
                             x-data="{ variantsData: {{ $hasVariants ? \Illuminate\Support\Js::from($product->variants->map(function($v) use ($product, $activeEventPromo, $image) {
                                 $vBasePrice = $v->pos_price ?: ($v->price ?: $product->price);
                                 $vPrice = $vBasePrice;
                                 if ($activeEventPromo) {
                                     if ($activeEventPromo->discount_type === 'percent') {
                                         $vPrice = max(0, $vBasePrice * (1 - (((float)$activeEventPromo->discount_amount) / 100)));
                                     } else {
                                         $vPrice = max(0, $vBasePrice - ((float)$activeEventPromo->discount_amount));
                                     }
                                 }
                                 return [
                                     'id' => $v->id, 
                                     'name' => $v->name, 
                                     'price' => $vPrice, 
                                     'original_price' => (float)$vBasePrice,
                                     'stock' => $v->stock,
                                     'image' => $v->media ? Storage::url($v->media->path) : $image,
                                     'attributes' => $v->attributeOptions->map(fn($opt) => [
                                         'attr_id' => $opt->attribute_id,
                                         'attr_name' => $opt->attribute->name ?? '',
                                         'attr_slug' => $opt->attribute->slug ?? '',
                                         'value' => $opt->value,
                                     ])->values()->all()
                                 ];
                             })) : 'null' }} }"
                             @click="addProduct({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $priceForJs }}, {{ $hasVariants ? 'true' : 'false' }}, variantsData, '{{ $image }}', {{ $product->is_custom ? 'true' : 'false' }}, {{ (float)($product->purchase_price ?? 0) }}, {{ (float)($originalPrice ?? $priceForJs) }}, {{ $hasPromo ? (float)$promoPrice : 'null' }}, {{ (int)$computedStock }})"
                             @endif
                             >
                             
                             @if($isOutOfStock)
                                <div class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none bg-gray-50/40">
                                    <span class="bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20 text-xs font-bold px-3 py-1 rounded-md shadow-xs transform -rotate-6">HABIS</span>
                                </div>
                             @endif

                            <div class="aspect-square bg-gray-50 relative overflow-hidden">
                                <img src="{{ $image }}" alt="{{ $product->name }}" class="object-cover w-full h-full {{ !$isOutOfStock ? 'group-hover:scale-102' : '' }} transition-transform duration-300">
                                @if(!empty($promoBadge))
                                    <span class="absolute top-2 left-2 bg-gradient-to-r from-rose-600 to-amber-500 text-white text-[10px] px-2 py-0.5 rounded-md font-bold shadow-md flex items-center gap-1 z-10 animate-pulse">
                                        🔥 {{ $promoBadge }}
                                    </span>
                                @elseif(isset($product->is_best_seller) && $product->is_best_seller)
                                    <span class="absolute top-2 left-2 bg-amber-400 text-amber-950 text-[10px] px-2 py-0.5 rounded-md font-bold shadow-xs flex items-center gap-1 z-10">
                                        Terlaris
                                    </span>
                                @endif
                                <span class="absolute bottom-2 left-2 {{ $isOutOfStock ? 'bg-red-600' : 'bg-emerald-600' }} text-white text-[10px] px-2 py-0.5 rounded-md font-semibold shadow-xs z-10">Stok: {{ $computedStock }}</span>
                                @if($hasVariants)
                                    <span class="absolute bottom-2 right-2 bg-gray-900/85 backdrop-blur-xs text-white text-[10px] px-2 py-0.5 rounded-md font-medium z-10 border border-white/20 shadow-xs">{{ $product->variants->count() }} Varian</span>
                                @endif
                            </div>
                            <div class="p-3 space-y-1.5">
                                <h3 class="font-semibold text-gray-900 text-xs line-clamp-2 leading-tight {{ !$isOutOfStock ? 'group-hover:text-emerald-600' : '' }} transition-colors">{{ $product->name }}</h3>
                                <div class="flex flex-col">
                                    @if($hasPromo)
                                        <span class="text-[10px] text-gray-400 line-through">Rp {{ number_format($originalPrice, 0, ',', '.') }}</span>
                                        <span class="text-rose-600 font-bold text-sm leading-tight">{{ $priceDisplay }}</span>
                                    @else
                                        <span class="text-emerald-600 font-bold text-sm leading-tight">{{ $priceDisplay }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <p class="text-sm font-medium text-gray-500">Tidak ada produk ditemukan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Modal Input Produk Kustom Fast Entry - Filament Native Style -->
            <div x-show="showCustomProductModal" x-cloak wire:key="modal-custom-product" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/50 backdrop-blur-xs font-sans" x-transition.opacity>
                <div class="bg-white w-full max-w-md rounded-xl overflow-hidden shadow-2xl border border-gray-200 flex flex-col max-h-[90vh]" @click.away="showCustomProductModal = false">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 bg-amber-50 rounded-lg text-amber-600 border border-amber-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-gray-950 leading-tight">Tambah Produk Kustom / Impor</h3>
                                <p class="text-xs text-gray-500 font-medium">Input cepat barang kustom/impor ke katalog & keranjang</p>
                            </div>
                        </div>
                        <button @click="showCustomProductModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">&times;</button>
                    </div>

                    <!-- Form Content -->
                    <div class="p-6 space-y-4 overflow-y-auto flex-1 text-xs bg-gray-50/50">
                        <div>
                            <label class="block font-bold text-gray-900 mb-1">Nama Produk Kustom</label>
                            <input type="text" x-model="customProductName" placeholder="misal: Baju Korea Impor Type A" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-xs font-medium text-gray-950 shadow-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition duration-150">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-gray-900 mb-1">Harga Modal / HPP</label>
                                <input type="number" x-model.number="customPurchasePrice" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-xs font-semibold text-gray-950 shadow-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition duration-150 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-900 mb-1" title="Harga jual standar produk">Harga Jual / Normal</label>
                                <input type="number" x-model.number="customPrice" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-xs font-bold text-emerald-950 shadow-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition duration-150 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-900 mb-1">Jumlah (Qty)</label>
                            <input type="number" x-model.number="customQty" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-xs font-semibold text-gray-950 shadow-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition duration-150 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>

                        <!-- Opsi Penyimpanan Katalog & Keranjang -->
                        <div class="space-y-2">
                            <div class="p-3 bg-white rounded-lg border border-gray-200 flex items-center justify-between shadow-xs">
                                <div>
                                    <div class="font-bold text-gray-900 text-xs">Simpan ke Katalog POS</div>
                                    <div class="text-[11px] text-gray-500">Buat master produk di katalog POS (Stok tersimpan: <span class="font-bold text-emerald-700" x-text="customQty || 1"></span> pcs).</div>
                                </div>
                                <input type="checkbox" x-model="customSaveToCatalog" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                            </div>

                            <div class="p-3 bg-white rounded-lg border border-gray-200 flex items-center justify-between shadow-xs">
                                <div>
                                    <div class="font-bold text-gray-900 text-xs">Sekalian Tambah 1 ke Keranjang Belanja</div>
                                    <div class="text-[11px] text-gray-500">Opsional jika ada pembeli yang langsung membeli item ini sekarang.</div>
                                </div>
                                <input type="checkbox" x-model="customAddToCart" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl flex-shrink-0">
                        <button type="button" @click="showCustomProductModal = false" class="px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                            Batal
                        </button>
                        <button type="button" @click="saveCustomProductModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                            Simpan ke Katalog POS
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Nego Harga Produk Kustom (Saat Diklik dari Catalog Grid) -->
            <!-- Modal Nego Harga & Detail Produk (Mirip Gambar 3) -->
            <div x-show="showCustomNegoModal" x-cloak wire:key="modal-custom-product-nego" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-gray-950/50 backdrop-blur-xs font-sans" x-transition.opacity>
                <div class="bg-white w-full max-w-3xl rounded-2xl overflow-hidden shadow-2xl border border-gray-200 flex flex-col max-h-[90vh]" @click.away="showCustomNegoModal = false">
                    <!-- Header (Mirip Gambar 3) -->
                    <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-100 flex items-start justify-between bg-white flex-shrink-0">
                        <div>
                            <h3 class="font-black text-base sm:text-lg text-gray-950 leading-tight uppercase tracking-tight" x-text="customNegoProduct.name || 'Detail Produk Nego'"></h3>
                            <p class="text-[11px] sm:text-xs text-gray-500 font-medium mt-0.5">Atur harga nego dan jumlah produk yang akan dimasukkan ke keranjang</p>
                        </div>
                        <button @click="showCustomNegoModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">&times;</button>
                    </div>

                    <!-- Body 2 Kolom (Mirip Gambar 3) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 bg-white overflow-y-auto flex-1">
                        <!-- Kolom Kiri: Form Harga & Qty -->
                        <div class="p-4 sm:p-6 space-y-4">
                            <!-- Harga Normal (Readonly) -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-gray-500 tracking-wider mb-1">Harga Normal (Tidak Dapat Diubah)</label>
                                <div class="p-2.5 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-600">Harga Katalog</span>
                                    <span class="font-extrabold text-sm text-gray-900" 
                                          :class="{'line-through text-gray-400 font-normal': customNegoProduct.promoPrice && customNegoProduct.promoPrice < customNegoProduct.originalPrice}"
                                          x-text="'Rp ' + formatMoney(customNegoProduct.originalPrice)"></span>
                                </div>
                            </div>

                            <!-- Harga Promo (Readonly, jika ada promo) -->
                            <template x-if="customNegoProduct.promoPrice && customNegoProduct.promoPrice < customNegoProduct.originalPrice">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="inline-flex items-center gap-1 bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded-md">🔥 PROMO AKTIF</span>
                                        <label class="text-[11px] font-bold uppercase text-gray-500 tracking-wider">Harga Promo</label>
                                    </div>
                                    <div class="p-2.5 bg-rose-50/60 border border-rose-100 rounded-xl flex items-center justify-between">
                                        <span class="text-xs font-semibold text-rose-800">Harga Promo Event</span>
                                        <span class="font-black text-sm text-rose-600" x-text="'Rp ' + formatMoney(customNegoProduct.promoPrice)"></span>
                                    </div>
                                </div>
                            </template>

                            <!-- Harga Nego (Editable) -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-emerald-800 tracking-wider mb-1">Harga Nego (Sesuaikan Harga)</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-2.5 text-xs font-bold text-gray-400">Rp</span>
                                    <input type="number" 
                                           x-model.number="customNegoProduct.negoPrice" 
                                           @keydown.enter="confirmCustomNegoAddToCart()"
                                           placeholder="0" 
                                           class="w-full pl-10 pr-3 py-2.5 border-2 border-emerald-500 rounded-xl bg-white text-sm font-black text-emerald-950 shadow-xs focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                </div>
                            </div>

                            <!-- Jumlah (Qty) -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[11px] font-bold uppercase text-gray-500 tracking-wider">Jumlah (Qty)</label>
                                    <template x-if="customNegoProduct.stock !== undefined && customNegoProduct.stock !== 999999">
                                        <span class="text-[11px] font-semibold text-emerald-600" x-text="'Stok: ' + customNegoProduct.stock"></span>
                                    </template>
                                </div>
                                <div class="inline-flex items-center border border-gray-300 rounded-xl bg-white overflow-hidden shadow-xs">
                                    <button type="button" @click="customNegoProduct.qty = Math.max(1, customNegoProduct.qty - 1)" class="w-10 h-10 flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-black cursor-pointer text-base transition select-none flex-shrink-0 border-r border-gray-200">-</button>
                                    <input type="number" x-model.number="customNegoProduct.qty" min="1" :max="customNegoProduct.stock" @input="if(customNegoProduct.stock && customNegoProduct.qty > customNegoProduct.stock) { customNegoProduct.qty = customNegoProduct.stock; showToast('Jumlah melebihi stok yang tersedia (' + customNegoProduct.stock + ')', 'warning'); }" class="w-16 text-center border-0 focus:ring-0 text-sm font-black p-0 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    <button type="button" @click="if(!customNegoProduct.stock || customNegoProduct.qty < customNegoProduct.stock) { customNegoProduct.qty++ } else { showToast('Jumlah melebihi stok yang tersedia (' + customNegoProduct.stock + ')', 'warning') }" class="w-10 h-10 flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-black cursor-pointer text-base transition select-none flex-shrink-0 border-l border-gray-200">+</button>
                                </div>
                            </div>

                            <!-- HPP / PIN Supervisor Warning -->
                            <template x-if="customNegoProduct.purchasePrice > 0">
                                <div class="p-2.5 rounded-xl text-xs flex items-center justify-between border"
                                     :class="customNegoProduct.negoPrice < customNegoProduct.purchasePrice ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-gray-50 border-gray-200 text-gray-600'">
                                    <span>HPP / Modal: <strong x-text="'Rp ' + formatMoney(customNegoProduct.purchasePrice)"></strong></span>
                                    <span x-show="customNegoProduct.negoPrice < customNegoProduct.purchasePrice" class="text-rose-600 font-black text-[11px] flex items-center gap-1">
                                        ⚠️ Butuh PIN Supervisor
                                    </span>
                                </div>
                            </template>
                        </div>

                        <!-- Kolom Kanan: Preview Gambar & Total (Mirip Gambar 3) -->
                        <div class="p-4 sm:p-6 bg-gray-50/50 flex flex-col items-center justify-between space-y-3 sm:space-y-4">
                            <div class="w-full flex flex-col items-center text-center space-y-2 sm:space-y-3">
                                <div class="w-28 h-28 sm:w-44 sm:h-44 bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-xs relative flex items-center justify-center flex-shrink-0">
                                    <img :src="customNegoProduct.defaultImage || '{{ asset('assets/images/placeholder.webp') }}'" 
                                         :alt="customNegoProduct.name" 
                                         class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-gray-900" x-text="customNegoProduct.name"></h4>
                                    <p class="text-[11px] sm:text-xs text-gray-500 font-semibold mt-0.5" x-text="customNegoProduct.qty + ' item × Rp ' + formatMoney(customNegoProduct.negoPrice)"></p>
                                </div>
                                <div class="p-2.5 sm:p-3 bg-emerald-50 border border-emerald-200/80 rounded-2xl w-full text-center">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Total Subtotal Nego</span>
                                    <span class="text-xl sm:text-2xl font-black text-emerald-700 font-mono" x-text="'Rp ' + formatMoney(customNegoProduct.negoPrice * customNegoProduct.qty)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer (Mirip Gambar 3) -->
                    <div class="px-4 sm:px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between rounded-b-2xl flex-shrink-0">
                        <button type="button" @click="showCustomNegoModal = false" class="px-4 sm:px-5 py-2.5 bg-white hover:bg-gray-100 border border-gray-300 text-gray-700 font-bold text-xs rounded-xl shadow-xs transition">
                            Batal
                        </button>
                        <button type="button" @click="confirmCustomNegoAddToCart()" class="px-4 sm:px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md transition cursor-pointer flex items-center gap-1.5">
                            + TAMBAH KE KERANJANG
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Cart Sidebar (Clean Filament Native Style - Desktop / Large Screen) -->
        <div x-show="activePage === 'kasir'" x-cloak class="hidden lg:flex w-[380px] xl:w-[420px] bg-white border-l border-gray-200 flex-col relative z-20 font-sans flex-shrink-0">
            <!-- Cart Header -->
            <div class="p-3.5 border-b border-gray-200 flex justify-between items-center bg-white">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Keranjang Belanja
                </h2>
                <div class="flex items-center gap-1.5">
                    <button @click="showHoldModal = true" class="relative text-emerald-600 hover:bg-emerald-50 px-2.5 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 text-xs font-semibold cursor-pointer" title="Lihat Antrean">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Antrean</span>
                        <span x-show="heldCarts.length > 0" class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full" x-text="heldCarts.length"></span>
                    </button>
                    <button @click="clearCart()" x-show="cart.length > 0" class="text-gray-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition-colors cursor-pointer" title="Kosongkan Keranjang">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-gray-50/30">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 p-8 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <p class="font-semibold text-gray-700 text-sm">Keranjang masih kosong</p>
                        <p class="text-xs text-gray-500 mt-1">Pilih produk di samping untuk memulai transaksi.</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="index">
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-xs flex flex-col gap-1.5 relative group hover:border-emerald-500 transition-all">
                        <div class="flex justify-between items-start pr-6">
                            <h4 class="font-semibold text-xs text-gray-900 leading-tight" x-text="item.name"></h4>
                            <div class="font-bold text-emerald-600 text-xs" x-text="'Rp ' + formatMoney(item.price * item.quantity)"></div>
                        </div>
                        
                        <div class="flex justify-between items-center mt-0.5">
                            <div class="text-[11px] text-gray-500 flex items-center gap-1">
                                <template x-if="item.original_price && item.original_price > item.price">
                                    <span class="line-through text-gray-400" x-text="'Rp ' + formatMoney(item.original_price)"></span>
                                </template>
                                <span x-text="'Rp ' + formatMoney(item.price) + ' / item'"></span>
                            </div>
                            <!-- Qty Controls -->
                            <div class="flex items-center border border-gray-200 rounded-md bg-gray-50">
                                <button @click="updateQty(index, -1)" class="w-6 h-6 flex items-center justify-center bg-white rounded-l-md text-gray-700 hover:text-emerald-600 font-bold text-xs cursor-pointer">-</button>
                                <input type="number" x-model.number="item.quantity" @input="validateCartItemQty(index)" @change="validateCartItemQty(index)" class="w-8 text-center bg-transparent border-none focus:ring-0 text-xs font-semibold p-0 mx-0.5" min="1" :max="item.stock">
                                <button @click="updateQty(index, 1)" class="w-6 h-6 flex items-center justify-center bg-white rounded-r-md text-gray-700 hover:text-emerald-600 font-bold text-xs cursor-pointer">+</button>
                            </div>
                        </div>

                        <!-- Hapus Item -->
                        <button @click="removeItem(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-600 bg-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Cart Footer (Totals & Checkout - Filament Native Style) -->
            <div class="bg-white border-t border-gray-200 p-4 space-y-3">
                <div class="flex justify-between items-center text-xs text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-900 text-sm" x-text="'Rp ' + formatMoney(subtotal)"></span>
                </div>

                <!-- Voucher Selector Button -->
                <div class="flex justify-between items-center text-xs cursor-pointer group" @click="showVoucherModal = true">
                    <span class="text-emerald-600 hover:text-emerald-700 font-medium" x-text="activeVoucher ? activeVoucher.name : 'Gunakan Kupon Promo'"></span>
                    <div class="flex items-center gap-2">
                        <span x-show="activeVoucher" class="font-semibold text-red-600 text-xs" x-text="'- Rp ' + formatMoney(voucherDiscountAmount)"></span>
                        <span x-show="!activeVoucher" class="text-xs font-semibold text-emerald-600 group-hover:text-emerald-700">Pilih ></span>
                        <button x-show="activeVoucher" @click.stop="removeVoucher()" class="text-gray-400 hover:text-red-600 transition-colors p-0.5 rounded-full hover:bg-red-50" title="Lepas Promo">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Diskon Manual Row -->
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600 font-medium">Diskon Manual</span>
                    <div class="flex items-center gap-2">
                        <template x-if="manualDiscountValue > 0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-amber-700 text-xs" x-text="'- Rp ' + formatMoney(manualDiscountAmount)"></span>
                                <button @click="openManualDiscountModal()" class="text-gray-400 hover:text-amber-700 transition-colors p-0.5 rounded-full hover:bg-amber-50" title="Edit Diskon Manual">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button @click="removeManualDiscount()" class="text-gray-400 hover:text-red-600 transition-colors p-0.5 rounded-full hover:bg-red-50" title="Hapus Diskon Manual">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="!manualDiscountValue || manualDiscountValue <= 0">
                            <button @click="openManualDiscountModal()" class="text-xs font-semibold text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 px-2 py-0.5 rounded-md transition-colors cursor-pointer flex items-center gap-1">
                                <span>+ Potongan</span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                    <span class="text-base font-bold text-gray-900">Total</span>
                    <span class="text-xl font-bold text-emerald-600" x-text="'Rp ' + formatMoney(grandTotal)"></span>
                </div>
                
                <div class="flex gap-2 pt-1">
                    <button 
                        @click="holdCart()"
                        :disabled="cart.length === 0"
                        class="px-3.5 py-2.5 rounded-lg font-medium text-gray-700 shadow-xs transition duration-150 flex items-center justify-center border border-gray-300 cursor-pointer"
                        :class="cart.length > 0 ? 'bg-white hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed border-transparent'"
                        title="Masukkan ke Antrean">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </button>
                    
                    <button 
                        @click="openCheckoutModal()"
                        :disabled="cart.length === 0"
                        class="flex-1 py-2.5 px-4 rounded-lg font-semibold text-sm shadow-xs transition duration-150 flex justify-center items-center gap-2 cursor-pointer"
                        :class="cart.length > 0 ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span>PROSES PEMBAYARAN</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- FLOATING MOBILE CART BAR (Visible on mobile & tablet portrait < lg) -->
        <div x-show="activePage === 'kasir' && cart.length > 0" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="transform translate-y-full opacity-0"
             x-transition:enter-end="transform translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="transform translate-y-0 opacity-100"
             x-transition:leave-end="transform translate-y-full opacity-0"
             class="lg:hidden fixed bottom-3 left-3 right-3 z-30 font-sans">
            <button type="button" @click="showMobileCartDrawer = true" class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold rounded-2xl flex items-center justify-between shadow-xl transition-all cursor-pointer border border-emerald-500">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs font-extrabold" x-text="totalItems"></span>
                    <span class="text-xs font-bold uppercase tracking-wider">Keranjang Belanja</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-base font-extrabold" x-text="'Rp ' + formatMoney(grandTotal)"></span>
                    <svg class="w-5 h-5 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                </div>
            </button>
        </div>

        <!-- MOBILE CART DRAWER (Bottom Sheet Slide-Over for < lg) -->
        <!-- 1. Dedicated Mobile Backdrop -->
        <div x-show="showMobileCartDrawer"
             x-cloak
             @click="showMobileCartDrawer = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="lg:hidden fixed inset-0 z-50 bg-gray-950/60 backdrop-blur-xs font-sans"></div>

        <!-- 2. Bottom Sheet Content Container -->
        <div x-show="showMobileCartDrawer"
             x-cloak
             class="lg:hidden fixed inset-x-0 bottom-0 z-50 flex flex-col justify-end font-sans pointer-events-none">
            
            <div x-show="showMobileCartDrawer"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="bg-white w-full rounded-t-2xl shadow-2xl flex flex-col max-h-[85vh] overflow-hidden border-t border-gray-200 pointer-events-auto">
                
                <!-- Drawer Header -->
                <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-white flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-gray-900">Keranjang Belanja</h2>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full" x-text="totalItems + ' Item'"></span>
                    </div>
                    <button type="button" @click="showMobileCartDrawer = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Drawer Cart Items List -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3 divide-y divide-gray-100">
                    <template x-for="(item, index) in cart" :key="item.product_id + '-' + (item.product_variant_id || '')">
                        <div class="pt-3 first:pt-0 flex items-center justify-between gap-3 text-xs" :class="item.is_return ? 'bg-rose-50/50 p-2 rounded-lg -mx-2' : ''">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold truncate" :class="item.is_return ? 'text-rose-900' : 'text-gray-900'" x-text="item.name"></div>
                                <div class="text-[11px] flex items-center gap-1" :class="item.is_return ? 'text-rose-600' : 'text-gray-500'">
                                    <template x-if="item.original_price && item.original_price > item.price && !item.is_return">
                                        <span class="line-through text-gray-400" x-text="'Rp ' + formatMoney(item.original_price)"></span>
                                    </template>
                                    <span x-text="(item.price < 0 ? '- ' : '') + 'Rp ' + formatMoney(Math.abs(item.price))"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <template x-if="!item.is_return">
                                    <div class="flex items-center">
                                        <button type="button" @click="updateQty(index, -1)" class="w-7 h-7 rounded-l-lg border border-gray-300 flex items-center justify-center font-bold text-gray-700 hover:bg-gray-100">-</button>
                                        <span class="w-8 text-center font-bold text-gray-900 border-t border-b border-gray-300 h-7 flex items-center justify-center" x-text="item.quantity"></span>
                                        <button type="button" @click="updateQty(index, 1)" class="w-7 h-7 rounded-r-lg border border-gray-300 flex items-center justify-center font-bold text-gray-700 hover:bg-gray-100">+</button>
                                    </div>
                                </template>
                                <template x-if="item.is_return">
                                    <span class="text-xs font-bold text-rose-700 bg-rose-100 px-2 py-1 rounded" x-text="'Qty: ' + item.quantity"></span>
                                </template>
                                <button type="button" @click="removeFromCart(index)" class="text-rose-500 hover:text-rose-700 ml-1 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Drawer Summary & Action Footer -->
                <div class="p-4 bg-gray-50 border-t border-gray-200 space-y-3 flex-shrink-0">
                    <!-- Voucher Selector Button -->
                    <div class="flex justify-between items-center text-xs cursor-pointer group" @click="showVoucherModal = true">
                        <span class="text-emerald-600 font-medium" x-text="activeVoucher ? activeVoucher.name : 'Gunakan Kupon Promo'"></span>
                        <div class="flex items-center gap-2">
                            <span x-show="activeVoucher" class="font-semibold text-red-600 text-xs" x-text="'- Rp ' + formatMoney(voucherDiscountAmount)"></span>
                            <span x-show="!activeVoucher" class="text-xs font-semibold text-emerald-600">Pilih ></span>
                            <button x-show="activeVoucher" @click.stop="removeVoucher()" class="text-gray-400 hover:text-red-600 p-0.5" title="Lepas Promo">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Diskon Manual Row -->
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600 font-medium">Diskon Manual</span>
                        <div class="flex items-center gap-2">
                            <template x-if="manualDiscountValue > 0">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-semibold text-amber-700 text-xs" x-text="'- Rp ' + formatMoney(manualDiscountAmount)"></span>
                                    <button @click="openManualDiscountModal()" class="text-gray-400 hover:text-amber-700 p-0.5" title="Edit Diskon Manual">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button @click="removeManualDiscount()" class="text-gray-400 hover:text-red-600 p-0.5" title="Hapus Diskon Manual">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!manualDiscountValue || manualDiscountValue <= 0">
                                <button @click="openManualDiscountModal()" class="text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 px-2 py-0.5 rounded-md transition-colors cursor-pointer flex items-center gap-1">
                                    <span>+ Potongan</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-sm font-bold text-gray-900 pt-2 border-t border-gray-200">
                        <span>Total Tagihan:</span>
                        <span class="text-lg text-emerald-700" x-text="'Rp ' + formatMoney(grandTotal)"></span>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="button" 
                                @click="holdCart(); showMobileCartDrawer = false" 
                                class="px-3.5 py-3 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-bold shadow-xs cursor-pointer flex items-center gap-1.5"
                                title="Masukkan ke Antrean">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs font-bold text-gray-700">Tahan</span>
                        </button>
                        <button type="button" 
                                @click="showMobileCartDrawer = false; openCheckoutModal()" 
                                class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm shadow-md flex justify-center items-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>PROSES PEMBAYARAN</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: Pilih Varian Produk - Direct Attribute Grid + Live Image Preview -->
        <div x-show="showVariantModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-3 md:p-4" style="display: none;">
            <div @click.away="showVariantModal = false"
                 x-show="showVariantModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-3xl rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans flex flex-col max-h-[90vh]">
                 
                 <!-- Modal Header -->
                 <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white flex-shrink-0">
                     <div>
                         <h3 class="text-base font-bold text-gray-950" x-text="currentProductForVariant ? currentProductForVariant.name : 'Pilih Varian Produk'"></h3>
                         <p class="text-xs text-gray-500 font-medium">Pilih opsi atribut di bawah untuk melihat stok & detail varian</p>
                     </div>
                     <div class="flex items-center gap-3">
                         <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none">
                             <input type="checkbox" x-model="variantShowOutOfStock" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5">
                             <span>Tampilkan Habis</span>
                         </label>
                         <button type="button" @click="showVariantModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                         </button>
                     </div>
                 </div>

                 <!-- Modal Body (Split 2 Columns) -->
                 <div class="grid grid-cols-1 md:grid-cols-12 overflow-y-auto flex-1 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                     
                     <!-- KOLOM KIRI: Attribute Selector Groups (7/12 width) -->
                     <div class="md:col-span-7 p-5 space-y-5 overflow-y-auto max-h-[60vh] md:max-h-none">
                         <template x-for="group in variantAttributeGroups" :key="group.name">
                             <div class="space-y-2">
                                 <!-- Group Header -->
                                 <div class="flex items-center justify-between">
                                     <div class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                                         <span x-text="group.name"></span>
                                         <span x-show="selectedVariantAttributes[group.name]" class="text-[10px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-bold lowercase" x-text="'✓ ' + selectedVariantAttributes[group.name]"></span>
                                     </div>
                                     
                                     <!-- Search input if options > 8 -->
                                     <template x-if="group.optionsArray.length > 8">
                                         <div class="relative w-36">
                                             <input type="text"
                                                    x-model="variantAttributeSearch[group.name]"
                                                    :placeholder="'Cari ' + group.name + '...'"
                                                    class="w-full text-[11px] border border-gray-200 rounded-md px-2 py-1 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                         </div>
                                     </template>
                                 </div>

                                 <!-- Option Chips Grid -->
                                 <div class="flex flex-wrap gap-2 max-h-36 overflow-y-auto p-1 bg-gray-50/50 border border-gray-100 rounded-xl">
                                     <template x-for="optVal in group.optionsArray.filter(o => !variantAttributeSearch[group.name] || o.toLowerCase().includes(variantAttributeSearch[group.name].toLowerCase()))" :key="optVal">
                                         <button type="button"
                                                 @click="toggleVariantAttributeOption(group.name, optVal)"
                                                 :disabled="getOptionStockForAttribute(group.name, optVal) <= 0"
                                                 class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5"
                                                 :class="selectedVariantAttributes[group.name] === optVal 
                                                     ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' 
                                                     : (getOptionStockForAttribute(group.name, optVal) <= 0 
                                                         ? (variantShowOutOfStock ? 'opacity-40 bg-gray-100 border-gray-200 text-gray-400 line-through cursor-not-allowed' : 'hidden') 
                                                         : 'bg-white border-gray-200 text-gray-800 hover:border-emerald-500 hover:bg-emerald-50/50')">
                                             <span x-text="optVal"></span>
                                             <span class="text-[10px] font-normal"
                                                   :class="selectedVariantAttributes[group.name] === optVal ? 'text-emerald-100' : 'text-gray-400'"
                                                   x-text="'(' + getOptionStockForAttribute(group.name, optVal) + ')'"></span>
                                         </button>
                                     </template>
                                 </div>
                             </div>
                         </template>
                     </div>

                     <!-- KOLOM KANAN: Live Preview & Details (5/12 width) -->
                      <div class="md:col-span-5 p-4 sm:p-5 bg-gray-50/60 flex flex-col justify-between items-center text-center">
                          <div class="w-full flex flex-row sm:flex-col items-center justify-center gap-3 sm:gap-3 text-left sm:text-center">
                              <!-- Variant / Product Image -->
                              <div class="w-24 h-24 sm:w-36 sm:h-36 md:w-44 md:h-44 rounded-xl border border-gray-200 bg-white overflow-hidden shadow-xs flex items-center justify-center p-1 relative flex-shrink-0">
                                  <img :src="selectedVariantPreviewImage" 
                                       :alt="currentProductForVariant ? currentProductForVariant.name : ''" 
                                       class="w-full h-full object-cover rounded-lg transition-all duration-200">
                                  
                                  <template x-if="selectedMatchedVariant">
                                      <span class="absolute top-1.5 right-1.5 sm:top-2 sm:right-2 px-1.5 sm:px-2 py-0.5 text-[9px] sm:text-[10px] font-bold rounded-md shadow-xs"
                                            :class="selectedMatchedVariant.stock > 0 ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'"
                                            x-text="selectedMatchedVariant.stock > 0 ? 'Stok: ' + selectedMatchedVariant.stock : 'HABIS'">
                                      </span>
                                  </template>
                              </div>

                              <!-- Selection Detail Summary -->
                              <div class="space-y-1 w-full min-w-0">
                                  <div class="text-xs font-semibold text-gray-500 truncate" x-text="currentProductForVariant ? currentProductForVariant.name : ''"></div>
                                  <div class="text-sm font-bold text-gray-950 leading-tight">
                                      <span x-text="selectedMatchedVariant ? selectedMatchedVariant.name : 'Pilih kombinasi atribut'"></span>
                                      <div class="mt-1 flex items-center justify-center flex-wrap gap-1.5">
                                          <template x-if="selectedMatchedVariant ? (selectedMatchedVariant.original_price && selectedMatchedVariant.original_price > selectedMatchedVariant.price) : (currentProductForVariant && currentProductForVariant.originalPrice > currentProductForVariant.price)">
                                              <span class="line-through text-gray-400 text-xs font-semibold" x-text="selectedMatchedVariant ? ('Rp ' + formatMoney(selectedMatchedVariant.original_price)) : ('Rp ' + formatMoney(currentProductForVariant.originalPrice))"></span>
                                          </template>
                                          <span class="text-base font-extrabold" :class="(selectedMatchedVariant ? (selectedMatchedVariant.original_price && selectedMatchedVariant.original_price > selectedMatchedVariant.price) : (currentProductForVariant && currentProductForVariant.originalPrice > currentProductForVariant.price)) ? 'text-rose-600' : 'text-emerald-700'" x-text="selectedMatchedVariant ? ('Rp ' + formatMoney(selectedMatchedVariant.price)) : (currentProductForVariant ? 'Rp ' + formatMoney(currentProductForVariant.price) : '')"></span>
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <!-- Stock Alert / Instruction -->
                          <div class="w-full mt-3 sm:mt-4">
                              <template x-if="!isAllVariantAttributesSelected">
                                  <p class="text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg text-center">
                                      Lengkapi semua opsi atribut yang tersedia
                                  </p>
                              </template>
                              <template x-if="isAllVariantAttributesSelected && selectedMatchedVariant && selectedMatchedVariant.stock <= 0">
                                  <p class="text-[11px] font-medium text-rose-700 bg-rose-50 border border-rose-200 px-3 py-1.5 rounded-lg text-center">
                                      Stok varian ini habis, tidak dapat dipilih
                                  </p>
                              </template>
                              <template x-if="isAllVariantAttributesSelected && selectedMatchedVariant && selectedMatchedVariant.stock > 0">
                                  <p class="text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg text-center">
                                      Stok tersedia: <span class="font-bold" x-text="selectedMatchedVariant.stock"></span> item
                                  </p>
                              </template>
                          </div>
                      </div>

                 </div>

                 <!-- Modal Footer -->
                 <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-between rounded-b-xl flex-shrink-0">
                     <button type="button" @click="showVariantModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                         Batal
                     </button>

                     <button type="button"
                             @click="confirmAddSelectedVariantToCart()"
                             :disabled="!isAllVariantAttributesSelected || !selectedMatchedVariant || selectedMatchedVariant.stock <= 0"
                             class="px-5 py-2 rounded-lg font-bold text-xs shadow-xs transition duration-150 flex items-center gap-1.5 cursor-pointer"
                             :class="(isAllVariantAttributesSelected && selectedMatchedVariant && selectedMatchedVariant.stock > 0) 
                                 ? 'bg-emerald-600 hover:bg-emerald-700 text-white' 
                                 : 'bg-gray-200 text-gray-400 cursor-not-allowed opacity-60'">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                         <span>+ TAMBAH KE KERANJANG</span>
                     </button>
                 </div>
            </div>
        </div>

        <!-- MODAL: Checkout / Payment - Filament Native Style (Redesigned Fast POS Layout) -->
        <div x-show="showCheckoutModal" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-3 md:p-4" style="display: none;">
            
            <div @click.away="if(!isProcessing) showCheckoutModal = false" 
                 x-show="showCheckoutModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-3xl rounded-xl border border-gray-200 shadow-2xl flex flex-col max-h-[92vh] overflow-hidden font-sans">
                
                <!-- Modal Header -->
                <div class="px-6 py-3.5 border-b border-gray-200 flex items-center justify-between bg-white flex-shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-gray-950">Pembayaran Transaksi Kasir</h3>
                        <p class="text-xs text-gray-500 font-medium">Pilih metode pembayaran dan masukkan nominal bayar</p>
                    </div>
                    <button type="button" @click="showCheckoutModal = false" :disabled="isProcessing" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body 2 Kolom Layout -->
                <div class="grid grid-cols-1 md:grid-cols-12 overflow-y-auto flex-1 divide-y md:divide-y-0 md:divide-x divide-gray-200 bg-white">
                    
                    <!-- Kolom Kiri: Ringkasan Tagihan & Item (Col 5) -->
                    <div class="md:col-span-5 p-5 bg-gray-50/50 flex flex-col justify-between space-y-4">
                        <div class="space-y-4">
                            <!-- Giant Total Tagihan Display -->
                            <div class="p-4 rounded-xl shadow-xs" :class="grandTotal < 0 ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white'">
                                <span class="text-[11px] font-bold uppercase tracking-wider block opacity-90" x-text="grandTotal < 0 ? 'Kembalian ke Pelanggan' : 'Total Tagihan'"></span>
                                <span class="text-2xl sm:text-3xl font-extrabold mt-1 block" x-text="'Rp ' + formatMoney(Math.abs(grandTotal))"></span>
                                <span x-show="grandTotal < 0" class="text-[11px] opacity-80 mt-1 block">Selisih retur — kembalikan uang ke pelanggan</span>
                            </div>

                            <!-- List Items (Scrollable) -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider">Rincian Barang</h4>
                                    <span class="text-[11px] font-semibold text-gray-500" x-text="cart.length + ' item'"></span>
                                </div>
                                <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1 border border-gray-200 rounded-xl p-2.5 bg-white shadow-xs">
                                    <template x-for="(item, idx) in cart" :key="idx">
                                        <div class="flex justify-between items-center text-xs py-1 border-b border-gray-100 last:border-b-0" :class="item.is_return ? 'bg-rose-50/50 p-2 rounded-lg' : ''">
                                            <div class="pr-2 truncate">
                                                <div class="font-bold truncate" :class="item.is_return ? 'text-rose-900' : 'text-gray-900'" x-text="item.name"></div>
                                                <div class="text-[11px] flex items-center gap-1" :class="item.is_return ? 'text-rose-600' : 'text-gray-500'">
                                                    <template x-if="item.original_price && item.original_price > item.price && !item.is_return">
                                                        <span class="line-through text-gray-400" x-text="'Rp ' + formatMoney(item.original_price)"></span>
                                                    </template>
                                                    <span x-text="(item.price < 0 ? '- ' : '') + 'Rp ' + formatMoney(Math.abs(item.price)) + ' × ' + item.quantity + ' pcs'"></span>
                                                </div>
                                            </div>
                                            <div class="font-bold whitespace-nowrap" :class="item.is_return ? 'text-rose-700' : 'text-gray-950'" x-text="(item.price * item.quantity < 0 ? '- ' : '') + 'Rp ' + formatMoney(Math.abs(item.price * item.quantity))"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Summary Breakdown -->
                        <div class="bg-white p-3 rounded-xl border border-gray-200 text-xs space-y-1.5 shadow-xs">
                            <div class="flex justify-between text-gray-600 font-medium">
                                <span>Subtotal</span>
                                <span class="font-bold text-gray-900" x-text="'Rp ' + formatMoney(subtotal)"></span>
                            </div>
                            <template x-if="voucherDiscountAmount > 0">
                                <div class="flex justify-between text-emerald-700 font-medium">
                                    <span>Diskon Voucher</span>
                                    <span class="font-bold" x-text="'- Rp ' + formatMoney(voucherDiscountAmount)"></span>
                                </div>
                            </template>
                            <template x-if="manualDiscountAmount > 0">
                                <div class="flex justify-between text-amber-700 font-medium">
                                    <span>Diskon Manual</span>
                                    <span class="font-bold" x-text="'- Rp ' + formatMoney(manualDiscountAmount)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Metode Bayar & Nominal (Col 7) -->
                    <div class="md:col-span-7 p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-4">
                            <!-- Split Payment Toggle -->
                            <div class="flex items-center justify-between mb-4 bg-purple-50 p-3 rounded-xl border border-purple-200">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    <div>
                                        <h4 class="font-bold text-purple-900 text-xs">Gunakan Split Payment</h4>
                                        <p class="text-[10px] text-purple-700">Bayar dengan lebih dari 1 metode pembayaran</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="isSplitPayment" class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>

                            <!-- Metode Pembayaran Grid -->
                            <div x-show="!isSplitPayment">
                                <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider mb-2.5">Metode Pembayaran</h4>
                                
                                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto pr-1">
                                    <template x-for="method in paymentMethods" :key="method.code">
                                        <button type="button"
                                                @click="selectPaymentMethod(method)"
                                                class="h-10 px-3 rounded-xl text-xs font-semibold flex items-center justify-between transition duration-150 shadow-xs cursor-pointer"
                                                :class="paymentMethod === method.code ? 'border-2 border-emerald-600 bg-emerald-50/90 text-emerald-950 font-bold' : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50'">
                                            <div class="flex items-center gap-2 truncate">
                                                <template x-if="method.logo">
                                                    <img :src="method.logo" :alt="method.name" class="w-4 h-4 object-contain flex-shrink-0">
                                                </template>
                                                <span class="truncate" x-text="method.name"></span>
                                            </div>
                                            <span x-show="paymentMethod === method.code" class="w-2.5 h-2.5 rounded-full bg-emerald-600 flex-shrink-0"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Nominal Uang (Jika Tunai) -->
                            <div x-show="!isSplitPayment && isCashSelected()" x-transition.opacity class="space-y-3 bg-emerald-50/40 p-4 rounded-xl border border-emerald-200/80">
                                <div>
                                    <label class="block text-xs font-bold text-gray-900 mb-1.5">Uang Diterima (Rp)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sm font-extrabold text-gray-400">Rp</div>
                                        <input type="text"
                                               x-model="displayCashPaid"
                                               @input="updateCashPaidInput($event.target.value)"
                                               @focus="$event.target.select()"
                                               class="w-full pl-10 pr-3.5 py-2.5 bg-white border border-emerald-300 rounded-lg text-xl font-extrabold text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-xs"
                                               placeholder="0">
                                    </div>
                                </div>

                                <!-- Smart Unique Preset Nominal Buttons -->
                                <div>
                                    <span class="text-[11px] font-semibold text-emerald-900 block mb-1">Nominal Cepat:</span>
                                    <div class="grid grid-cols-3 gap-2">
                                        <template x-for="(preset, pIdx) in getNominalPresets()" :key="pIdx">
                                            <button type="button" 
                                                    @click="setCashPaid(preset)" 
                                                    class="py-2 px-1 rounded-lg font-bold text-xs shadow-xs transition cursor-pointer text-center truncate border"
                                                    :class="Number(cashPaid) === Number(preset) 
                                                        ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700' 
                                                        : 'bg-white text-emerald-900 border-emerald-300 hover:bg-emerald-100'">
                                                <span x-text="pIdx === 0 ? 'Uang Pas' : ('Rp ' + formatMoney(preset))"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Prominent Kembalian Box -->
                                <template x-if="grandTotal < 0">
                                    <div class="p-3.5 rounded-xl flex items-center justify-between text-xs font-bold border shadow-xs bg-rose-50 border-rose-300 text-rose-700">
                                        <span class="uppercase tracking-wider font-extrabold">Kembalian ke Pelanggan</span>
                                        <span class="text-xl font-extrabold" x-text="'Rp ' + formatMoney(Math.abs(grandTotal))"></span>
                                    </div>
                                </template>
                                <template x-if="grandTotal >= 0">
                                    <div class="p-3.5 rounded-xl flex items-center justify-between text-xs font-bold border shadow-xs"
                                         :class="cashPaid < grandTotal ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-emerald-100/80 border-emerald-300 text-emerald-950'">
                                        <span class="uppercase tracking-wider font-extrabold" x-text="cashPaid < grandTotal ? 'Uang Masih Kurang' : 'Kembalian'"></span>
                                        <span class="text-xl font-extrabold" x-text="'Rp ' + formatMoney(Math.abs(cashChange))"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Catatan Pembayaran Non-Tunai -->
                            <div x-show="!isSplitPayment && !isCashSelected()" x-transition.opacity class="p-3.5 bg-white rounded-xl border border-gray-200 text-xs text-gray-600 leading-relaxed shadow-xs">
                                Metode pembayaran non-tunai (<strong x-text="paymentMethods.find(m => m.code === paymentMethod)?.name || paymentMethod"></strong>) dipilih. Transaksi akan dicatat di laporan kasir.
                            </div>

                            <!-- Split Payment UI -->
                            <div x-show="isSplitPayment" x-transition.opacity class="space-y-3 bg-purple-50/40 p-4 rounded-xl border border-purple-200/80">
                                <h4 class="font-bold text-purple-900 text-xs mb-2">Rincian Split Payment</h4>
                                <template x-for="(sp, index) in splitPayments" :key="index">
                                    <div class="flex items-center gap-2 mb-2">
                                        <select x-model="sp.method" class="w-1/3 px-3 py-2 border border-purple-300 rounded-lg text-xs font-semibold focus:ring-purple-500 focus:border-purple-500">
                                            <template x-for="m in paymentMethods" :key="m.code">
                                                <option :value="m.code" x-text="m.name"></option>
                                            </template>
                                        </select>
                                        <div class="relative w-2/3">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-extrabold text-gray-400">Rp</div>
                                            <input type="number"
                                                x-model="sp.amount"
                                                @input="calculateChange()"
                                                class="w-full pl-8 pr-3 py-2 bg-white border border-purple-300 rounded-lg text-sm font-bold text-gray-950 focus:outline-none focus:ring-2 focus:ring-purple-500 shadow-xs"
                                                placeholder="0">
                                        </div>
                                        <button type="button" @click="removeSplitPayment(index)" x-show="splitPayments.length > 1" class="p-2 text-rose-500 hover:text-rose-700 bg-white border border-rose-200 rounded-lg shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </template>
                                
                                <button type="button" @click="addSplitPayment()" class="w-full py-2 bg-white border border-dashed border-purple-300 rounded-lg text-purple-700 text-xs font-bold hover:bg-purple-50 transition flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Metode Bayar
                                </button>

                                <div class="p-3.5 rounded-xl flex items-center justify-between text-xs font-bold border shadow-xs mt-3"
                                     :class="grandTotal < 0 ? 'bg-rose-50 border-rose-300 text-rose-700' : (totalSplitPaid < grandTotal ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-emerald-100/80 border-emerald-300 text-emerald-950')">
                                    <span class="uppercase tracking-wider font-extrabold" x-text="grandTotal < 0 ? 'Kembalian ke Pelanggan' : (totalSplitPaid < grandTotal ? 'Uang Masih Kurang' : 'Kembalian')"></span>
                                    <span class="text-xl font-extrabold" x-text="'Rp ' + formatMoney(Math.abs(grandTotal < 0 ? grandTotal : cashChange))"></span>
                                </div>
                            </div>

                            <!-- Identitas Pembeli (Live Search Autocomplete) -->
                            <div class="pt-2 border-t border-gray-200" id="customerSection">
                                <div class="flex items-center justify-between mb-1.5">
                                        <span>Pelanggan (Live Search / Autocomplete)</span>
                                    <button type="button" x-show="customerName || customerPhone || customerSearchInput" @click="clearCustomer()" class="text-[11px] font-bold text-rose-600 hover:text-rose-700 cursor-pointer flex items-center gap-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Hapus Pelanggan
                                    </button>
                                </div>

                                <div class="relative" @click.away="showCustomerDropdown = false">
                                    <div class="relative">
                                        <input type="text" 
                                               x-model="customerSearchInput"
                                               @focus="showCustomerDropdown = true"
                                               @input="onCustomerInput()"
                                               class="w-full pl-8 pr-8 py-2 rounded-lg text-xs font-medium placeholder-gray-400 focus:outline-none transition-all shadow-xs border border-gray-300 bg-white text-gray-900 focus:ring-2 focus:ring-emerald-500"
                                               placeholder="Cari nama atau No. WhatsApp (contoh: Siti / 0812...)">
                                        
                                        <svg class="w-4 h-4 absolute left-2.5 top-2.5 pointer-events-none text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>

                                        <button x-show="customerSearchInput" type="button" @click="clearCustomer()" class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <!-- Dropdown List Autocomplete -->
                                    <div x-show="showCustomerDropdown && filteredCustomers.length > 0" 
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto divide-y divide-gray-100">
                                        <template x-for="c in filteredCustomers" :key="c.id || c.phone || c.name">
                                            <div @click="selectCustomer(c)" class="p-2.5 hover:bg-emerald-50 cursor-pointer flex items-center justify-between transition-colors">
                                                <div>
                                                    <div class="font-bold text-xs text-gray-900" x-text="c.name || 'Tanpa Nama'"></div>
                                                    <div class="text-[10px] text-gray-500 font-mono" x-text="c.phone || '-'"></div>
                                                </div>
                                                <div class="text-right">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                        <span x-text="c.stamp_count || 0"></span>/12 Cap
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Detail Nama & Phone jika kasir mengetik manual -->
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                     <input type="text"
                                            id="customerNameInput"
                                            x-model="customerName"
                                            @input="saveActiveCart()"
                                            class="w-full px-2.5 py-1 rounded text-[11px] font-medium text-gray-800 focus:outline-none transition-all border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500"
                                            placeholder="Nama Pelanggan">
                                     <input type="text"
                                            id="customerPhoneInput"
                                            x-model="customerPhone"
                                            @input="saveActiveCart()"
                                            class="w-full px-2.5 py-1 rounded text-[11px] font-medium text-gray-800 focus:outline-none transition-all border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500"
                                            placeholder="No WhatsApp">
                                 </div>

                                 <!-- Banner Pemberitahuan Voucher & Hadiah Stempel di Modal Checkout -->
                                 <div x-show="activeCustomerLoyalty" x-transition class="mt-2.5 p-3 bg-amber-50 rounded-xl border border-amber-300 text-xs">
                                     <div class="font-bold text-amber-900 flex items-center justify-between">
                                         <span>Informasi Stempel Pelanggan:</span>
                                         <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900 border border-amber-300" x-text="(activeCustomerLoyalty?.stamp_count || 0) + ' / 12 Cap'"></span>
                                     </div>

                                     <!-- Voucher Promo Hadiah -->
                                     <template x-if="availableLoyaltyVouchersForCustomer.length > 0">
                                         <div class="mt-2">
                                             <div class="text-[11px] font-semibold text-amber-800 mb-1.5">
                                                 🎟️ Pelanggan berhak memasang Voucher Hadiah Stempel:
                                             </div>
                                             <div class="space-y-1.5">
                                                 <template x-for="v in availableLoyaltyVouchersForCustomer" :key="v.id">
                                                     <div class="flex items-center justify-between bg-white p-2 rounded-lg border border-amber-200 shadow-xs">
                                                         <div>
                                                             <div class="font-bold text-gray-900 text-xs" x-text="v.name"></div>
                                                             <div class="text-[10px] text-emerald-700 font-semibold" x-text="getVoucherDiscountLabel(v)"></div>
                                                         </div>
                                                         <button type="button" 
                                                                 @click="applyVoucher(v); showToast('Voucher hadiah berhasil dipasang!', 'success');"
                                                                 :disabled="activeVoucher && activeVoucher.id === v.id"
                                                                 class="px-2.5 py-1 text-[11px] font-bold rounded shadow-xs cursor-pointer transition"
                                                                 :class="activeVoucher && activeVoucher.id === v.id ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-600 hover:bg-amber-700 text-white'">
                                                             <span x-text="activeVoucher && activeVoucher.id === v.id ? 'Terpasang' : 'Pasang Voucher'"></span>
                                                         </button>
                                                     </div>
                                                 </template>
                                             </div>
                                         </div>
                                     </template>

                                     <!-- Hadiah Barang Fisik / Manual -->
                                     <template x-if="availableLoyaltyManualRewardsForCustomer.length > 0">
                                         <div class="mt-2 pt-2 border-t border-amber-200">
                                             <div class="text-[11px] font-semibold text-amber-900 mb-1">
                                                 🎁 Hadiah Barang Fisik (Serahkan ke Pelanggan):
                                             </div>
                                             <div class="space-y-1.5">
                                                 <template x-for="(mTier, mIdx) in availableLoyaltyManualRewardsForCustomer" :key="mIdx">
                                                     <div class="bg-white p-2 rounded-lg border border-amber-300 flex items-center justify-between shadow-xs">
                                                         <div>
                                                             <div class="font-bold text-amber-950 text-xs flex items-center gap-1">
                                                                 <span>🎁</span>
                                                                 <span x-text="mTier.description"></span>
                                                             </div>
                                                             <div class="text-[10px] text-amber-800 font-medium" x-text="'Syarat: Minimal ' + mTier.min_stamps + ' Cap Stempel (Terpenuhi)'"></div>
                                                         </div>
                                                         <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-600 text-white rounded">Siap Diserahkan</span>
                                                     </div>
                                                 </template>
                                             </div>
                                         </div>
                                     </template>

                                     <template x-if="availableLoyaltyVouchersForCustomer.length === 0 && availableLoyaltyManualRewardsForCustomer.length === 0">
                                         <div class="text-[11px] text-amber-800 mt-1">
                                             Saldo stempel pelanggan saat ini: <strong x-text="(activeCustomerLoyalty?.stamp_count || 0)"></strong> Cap. Stempel baru akan ditambahkan otomatis setelah pembayaran selesai.
                                         </div>
                                     </template>
                                 </div>

                                <!-- Toggle Status Dipesan / Reserved -->
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <label class="flex items-center justify-between cursor-pointer p-2.5 bg-blue-50/80 rounded-xl border border-blue-200 hover:bg-blue-100/80 transition-colors">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <div>
                                                <span class="text-xs font-bold text-blue-950 block">Dipesan (Barang Ambil Nanti)</span>
                                                <span class="text-[10px] text-blue-700 font-medium block">Bayar lunas sekarang, barang diserahkan kemudian</span>
                                            </div>
                                        </div>
                                        <input type="checkbox" x-model="isReserved" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                    </label>

                                    <div x-show="isReserved" x-transition class="mt-2.5 pl-1">
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Perkiraan Tanggal Ambil (Opsional):</label>
                                        <input type="date" x-model="pickupDate" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-between rounded-b-xl flex-shrink-0">
                    <button type="button" @click="showCheckoutModal = false" :disabled="isProcessing" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="submitOrder()" 
                            :disabled="isProcessing || (grandTotal >= 0 && !isSplitPayment && isCashSelected() && cashPaid < grandTotal) || (grandTotal >= 0 && isSplitPayment && totalSplitPaid < grandTotal)" 
                            class="px-6 py-2.5 text-white font-bold text-xs rounded-lg shadow-md transition duration-150 cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="isReserved ? 'bg-blue-600 hover:bg-blue-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                        <span x-show="!isProcessing" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="isReserved ? 'SIMPAN PESANAN (DIPESAN)' : 'SELESAIKAN PEMBAYARAN'"></span>
                        </span>
                        <span x-show="isProcessing" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memproses Transaksi...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Konfirmasi Penyerahan Barang Reserved Order -->
        <div x-show="showPickupConfirmModal" 
             x-cloak 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-150" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-[110] flex items-center justify-center bg-gray-950/60 backdrop-blur-xs p-4 font-sans" style="display: none;">
            <div @click.away="showPickupConfirmModal = false"
                 x-show="showPickupConfirmModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white border border-gray-200 rounded-2xl shadow-2xl w-full max-w-md p-6 text-center space-y-5">
                
                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto ring-8 ring-emerald-50">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-gray-900">Konfirmasi Penyerahan Barang</h3>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Apakah Anda yakin barang untuk pesanan <span class="font-mono font-bold text-emerald-700" x-text="confirmPickupOrder ? '#' + confirmPickupOrder.number : ''"></span> a.n. <strong class="text-gray-900" x-text="confirmPickupOrder ? confirmPickupOrder.name : ''"></strong> sudah diserahkan ke pelanggan?
                    </p>
                    <p class="text-[11px] text-gray-500 bg-amber-50 text-amber-800 p-2.5 rounded-xl border border-amber-200/80 mt-2 font-medium">
                        Status pesanan akan diubah menjadi <strong class="font-bold">Selesai (Completed)</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" 
                            @click="showPickupConfirmModal = false" 
                            class="w-1/2 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" 
                            @click="$wire.completeReservedOrder(confirmPickupOrder.id); showPickupConfirmModal = false" 
                            class="w-1/2 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Ya, Serahkan</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Tutup Shift (Clean Filament Native Style) -->
        <div x-show="showCloseSession"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4 font-sans" style="display: none;">
            <div @click.away="showCloseSession = false"
                 x-show="showCloseSession"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white border border-gray-200 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                <div class="px-4 sm:px-6 py-3.5 border-b border-gray-200 flex items-center justify-between bg-white flex-shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-950">Tutup Shift Kasir</h3>
                            <p class="text-xs text-gray-500 font-medium">Hitung uang fisik aktual di laci kasir</p>
                        </div>
                    </div>
                    <button type="button" @click="showCloseSession = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="closeSession" class="flex flex-col overflow-hidden flex-1" x-data="{
                    displayEndingCash: '',
                    formatRupiah(val) {
                        if (!val || val === 0 || val === '0') return '';
                        let num = val.toString().replace(/\D/g, '');
                        return num ? parseInt(num, 10).toLocaleString('id-ID') : '';
                    },
                    updateEndingCash(val) {
                        let clean = val.replace(/\D/g, '');
                        this.displayEndingCash = clean ? parseInt(clean, 10).toLocaleString('id-ID') : '';
                        $wire.set('actualEndingCash', clean ? parseInt(clean, 10) : 0);
                    }
                }" x-init="displayEndingCash = formatRupiah($wire.actualEndingCash)">
                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        @if($activeSession)
                        @php
                            // Hanya hitung order tunai (cash/tunai), exclude QRIS, transfer, dll, dan cancelled
                            $cashOrders = $activeSession->orders()
                                ->whereNotIn('status', ['cancelled'])
                                ->where(function ($q) {
                                    $q->where('payment_method', 'cash')
                                      ->orWhere('payment_method', 'tunai');
                                });
                            $totalCashSales = $cashOrders->sum('grand_total');

                            $pettyCashIn = \App\Models\Cashflow::where('source', 'pos')
                                ->where('category', 'pos_petty_cash')
                                ->where('type', 'in')
                                ->where('created_at', '>=', $activeSession->opened_at)
                                ->sum('amount');

                            $pettyCashOut = \App\Models\Cashflow::where('source', 'pos')
                                ->where('category', 'pos_petty_cash')
                                ->where('type', 'out')
                                ->where('created_at', '>=', $activeSession->opened_at)
                                ->sum('amount');

                            $exchangeExtraPayIn = \App\Models\Cashflow::where('source', 'pos')
                                ->where('category', 'pos_exchange_pay')
                                ->where('type', 'in')
                                ->where('created_at', '>=', $activeSession->opened_at)
                                ->sum('amount');

                            $voidAndRefundCashOut = \App\Models\Cashflow::where('source', 'pos')
                                ->where('type', 'out')
                                ->where('created_at', '>=', $activeSession->opened_at)
                                ->where(function ($q) {
                                    $q->where('category', 'pos_return_refund')
                                      ->orWhere(function ($q2) {
                                          $q2->where('category', 'pos_void')
                                             ->whereHas('order', function ($q3) {
                                                 $q3->whereIn('payment_method', ['cash', 'tunai']);
                                             });
                                      });
                                })
                                ->sum('amount');

                            $modalEstimasi = $activeSession->opening_cash
                                + $totalCashSales
                                + $pettyCashIn
                                + $exchangeExtraPayIn
                                - $pettyCashOut
                                - $voidAndRefundCashOut;
                        @endphp
                        <div class="bg-gray-50 p-3.5 rounded-lg border border-gray-200 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Modal Awal Shift:</span>
                                <span class="font-semibold text-gray-900">Rp {{ number_format($activeSession->opening_cash, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Penjualan Tunai:</span>
                                <span class="font-semibold text-emerald-600">+ Rp {{ number_format($totalCashSales, 0, ',', '.') }}</span>
                            </div>
                            @if($pettyCashIn > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Kas Masuk (Petty Cash):</span>
                                <span class="font-semibold text-emerald-600">+ Rp {{ number_format($pettyCashIn, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            @if($pettyCashOut > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Kas Keluar (Petty Cash):</span>
                                <span class="font-semibold text-rose-600">- Rp {{ number_format($pettyCashOut, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            @if($voidAndRefundCashOut > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Void / Refund Tunai:</span>
                                <span class="font-semibold text-rose-600">- Rp {{ number_format($voidAndRefundCashOut, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            <div class="border-t border-gray-200 pt-2 flex justify-between items-center">
                                <span class="font-semibold text-gray-700">Estimasi Uang Fisik Laci:</span>
                                <span class="font-bold text-emerald-700 text-xs">Rp {{ number_format($modalEstimasi, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @endif

                        <div>
                            <label for="actualEndingCash" class="block text-xs font-semibold text-gray-700 mb-1">
                                Total Uang Fisik Aktual (Rp) <span class="text-rose-600">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-gray-500">Rp</span>
                                <input 
                                    type="text" 
                                    id="actualEndingCash"
                                    x-model="displayEndingCash"
                                    @input="updateEndingCash($event.target.value)"
                                    class="w-full pl-9 pr-3.5 py-2 bg-white border border-gray-300 rounded-lg text-base font-bold text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-xs" 
                                    placeholder="0" 
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label for="sessionNotes" class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <textarea 
                                id="sessionNotes"
                                wire:model="sessionNotes" 
                                class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 text-xs placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-xs" 
                                rows="2" 
                                placeholder="Misal: Ada pengeluaran operasional Rp 10.000"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-4 sm:px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl flex-shrink-0">
                        <button type="button" @click="showCloseSession = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Batal</button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled" wire:target="closeSession"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer flex items-center gap-1.5"
                        >
                            <svg wire:loading wire:target="closeSession" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="closeSession">Akhiri Shift</span>
                            <span wire:loading wire:target="closeSession">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Hold Carts - Filament Native Style -->
        <div x-show="showHoldModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showHoldModal = false"
                 x-show="showHoldModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-xl rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans flex flex-col max-h-[85vh]">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white flex-shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-gray-950">Daftar Antrean Pesanan</h3>
                        <p class="text-xs text-gray-500 font-medium">Keranjang belanja yang ditahan sementara</p>
                    </div>
                    <button type="button" @click="showHoldModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto flex-1 space-y-3 bg-gray-50/50">
                    <template x-if="heldCarts.length === 0">
                        <div class="text-center py-10 text-gray-400">
                            <p class="text-xs font-semibold">Tidak ada antrean pesanan aktif.</p>
                        </div>
                    </template>
                    
                    <template x-for="hold in heldCarts" :key="hold.id">
                        <div class="bg-white border border-gray-200 shadow-xs p-4 rounded-xl flex items-center justify-between gap-4">
                            <div>
                                <div class="font-bold text-xs text-gray-950" x-text="hold.name"></div>
                                <div class="text-[11px] text-gray-500 font-medium" x-text="hold.cart.length + ' item · Antre sejak ' + hold.time"></div>
                                <div class="font-bold text-xs text-emerald-600 mt-0.5" x-text="'Rp ' + formatMoney(hold.total)"></div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="deleteHeldCart(hold.id)" class="px-3 py-1.5 bg-white border border-gray-300 text-rose-600 font-semibold rounded-lg hover:bg-rose-50 text-xs shadow-xs transition">Hapus</button>
                                <button @click="resumeCart(hold.id)" class="px-3 py-1.5 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 text-xs shadow-xs transition">Lanjutkan</button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end rounded-b-xl flex-shrink-0">
                    <button type="button" @click="showHoldModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Konfirmasi - Filament Native Style -->
        <div x-show="showConfirmModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showConfirmModal = false"
                 x-show="showConfirmModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-sm rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans p-6 text-center">
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-3 border border-rose-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-base font-bold text-gray-950 mb-1" x-text="confirmTitle"></h3>
                <p class="text-xs text-gray-500 font-medium mb-5" x-text="confirmMessage"></p>
                
                <div class="flex gap-2.5">
                    <button @click="showConfirmModal = false" class="flex-1 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Batal</button>
                    <button @click="executeConfirm()" class="flex-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>

        <!-- MODAL: Input Custom - Filament Native Style -->
        <div x-show="showInputModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showInputModal = false"
                 x-show="showInputModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-sm rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                    <h3 class="text-base font-bold text-gray-950" x-text="inputTitle"></h3>
                    <button type="button" @click="showInputModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="p-6 space-y-3">
                    <p class="text-xs text-gray-500 font-medium" x-text="inputMessage"></p>
                    <input type="text" id="alpineInputModalField" x-model="inputValue" @keydown.enter="executeInput()" :placeholder="inputPlaceholder" class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-xs">
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl">
                    <button type="button" @click="showInputModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Batal</button>
                    <button type="button" @click="executeInput()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Simpan</button>
                </div>
            </div>
        </div>

        <!-- MODAL: Diskon Manual - Filament Native Style -->
        <div x-show="showManualDiscountModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showManualDiscountModal = false"
                 x-show="showManualDiscountModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-md rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-950">Diskon Manual Pesanan</h3>
                            <p class="text-xs text-gray-500 font-medium">Potongan harga dalam Rupiah (Rp) atau Persen (%)</p>
                        </div>
                    </div>
                    <button type="button" @click="showManualDiscountModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Segmented Switch: Rp vs % -->
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-gray-100 rounded-lg border border-gray-200/80">
                        <button type="button" @click="tempManualDiscountType = 'rp'"
                                class="py-2 rounded-md text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                                :class="tempManualDiscountType === 'rp' ? 'bg-white text-amber-800 shadow-xs border border-gray-200' : 'text-gray-600 hover:text-gray-900'">
                            <span>Nominal (Rp)</span>
                        </button>
                        <button type="button" @click="tempManualDiscountType = 'percent'"
                                class="py-2 rounded-md text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                                :class="tempManualDiscountType === 'percent' ? 'bg-white text-amber-800 shadow-xs border border-gray-200' : 'text-gray-600 hover:text-gray-900'">
                            <span>Persentase (%)</span>
                        </button>
                    </div>

                    <!-- Input Nominal/Persen -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nilai Diskon</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-xs text-gray-500" x-text="tempManualDiscountType === 'rp' ? 'Rp' : '%'"></span>
                            <input type="number" id="alpineManualDiscountField" x-model="tempManualDiscountValue" @keydown.enter="applyManualDiscount()"
                                   :placeholder="tempManualDiscountType === 'rp' ? 'Contoh: 10000' : 'Contoh: 10'"
                                   class="w-full pl-9 pr-3.5 py-2 bg-white border border-gray-300 rounded-lg text-base font-bold text-gray-950 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-xs">
                        </div>
                    </div>

                    <!-- Quick Presets -->
                    <div>
                        <div class="text-[11px] font-semibold text-gray-500 mb-1.5">Preset Cepat:</div>
                        <div class="grid grid-cols-5 gap-1.5" x-show="tempManualDiscountType === 'rp'">
                            <button type="button" @click="tempManualDiscountValue = 5000" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">5k</button>
                            <button type="button" @click="tempManualDiscountValue = 10000" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">10k</button>
                            <button type="button" @click="tempManualDiscountValue = 20000" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">20k</button>
                            <button type="button" @click="tempManualDiscountValue = 50000" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">50k</button>
                            <button type="button" @click="tempManualDiscountValue = 100000" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">100k</button>
                        </div>
                        <div class="grid grid-cols-5 gap-1.5" x-show="tempManualDiscountType === 'percent'">
                            <button type="button" @click="tempManualDiscountValue = 5" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">5%</button>
                            <button type="button" @click="tempManualDiscountValue = 10" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">10%</button>
                            <button type="button" @click="tempManualDiscountValue = 15" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">15%</button>
                            <button type="button" @click="tempManualDiscountValue = 20" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">20%</button>
                            <button type="button" @click="tempManualDiscountValue = 25" class="py-1.5 bg-white hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer shadow-xs">25%</button>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Bar -->
                <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl">
                    <button type="button" @click="showManualDiscountModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Batal</button>
                    <button type="button" @click="applyManualDiscount()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Terapkan Diskon</button>
                </div>
            </div>
        </div>

        <!-- OVERLAY: Lock Screen Kasir (Clean Filament Native Style without calculator numpad) -->
        <div x-show="isLocked"
             @screen-unlock-failed.window="handleUnlockFailed($event.detail[0]?.message || $event.detail?.message)"
             @screen-unlocked.window="isLocked = false; lockPasswordInput = ''; isLockError = false; lockErrorMessage = '';"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4 font-sans" style="display: none;">
            <div class="bg-white border border-gray-200 rounded-xl p-6 sm:p-8 text-center shadow-xl w-full max-w-md space-y-6">
                <!-- Icon Padlock Lock/Unlock Animation -->
                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-full flex items-center justify-center mx-auto shadow-xs transition-all duration-300 transform"
                     :class="{
                         'bg-red-50 text-red-600 border-red-200 animate-pin-shake': isLockError,
                         'bg-emerald-100 text-emerald-700 border-emerald-300 scale-105': !isLockError && (lockPasswordInput || '').length === 6
                     }">
                    <!-- Locked Icon (when length < 6 or error) -->
                    <svg x-show="(lockPasswordInput || '').length < 6 || isLockError" class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <!-- Unlocked Icon (when length === 6 and no error) -->
                    <svg x-show="(lockPasswordInput || '').length === 6 && !isLockError" x-cloak class="w-8 h-8 text-emerald-600 transition-transform duration-300 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                </div>

                <div class="space-y-1">
                    <h2 class="text-xl font-bold tracking-tight text-gray-900">{{ auth()->user()->name }}</h2>
                    <p class="text-xs font-medium text-gray-500">Masukkan 6-digit PIN POS Anda untuk membuka kunci layar.</p>
                </div>

                <form @submit.prevent="submitUnlock()" class="space-y-5 text-left">
                    <!-- 6-Dots Indicator UI -->
                    <div class="relative cursor-pointer" @click="document.getElementById('posLockPasswordField').focus()">
                        <input 
                            type="password" 
                            id="posLockPasswordField" 
                            x-model="lockPasswordInput" 
                            @input="isLockError = false; lockErrorMessage = '';"
                            maxlength="6" 
                            pattern="[0-9]*" 
                            inputmode="numeric" 
                            class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10"
                            autofocus
                        />
                        <div class="grid grid-cols-6 gap-2.5" :class="isLockError ? 'animate-pin-shake' : ''">
                            <template x-for="i in 6" :key="i">
                                <div 
                                    class="h-12 rounded-lg border text-center flex items-center justify-center text-lg font-bold transition-all duration-150"
                                    :class="{
                                        'border-red-500 ring-2 ring-red-500/20 bg-red-50/50 text-red-600': isLockError,
                                        'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/40': !isLockError && (lockPasswordInput || '').length === i - 1,
                                        'border-emerald-600 bg-white text-emerald-700 shadow-xs': !isLockError && (lockPasswordInput || '').length >= i,
                                        'border-gray-300 bg-gray-50/60 text-gray-400': !isLockError && (lockPasswordInput || '').length < i - 1
                                    }"
                                >
                                    <span x-show="String(lockPasswordInput || '').length >= i" class="w-3 h-3 rounded-full inline-block" :class="isLockError ? 'bg-red-500 animate-pulse' : 'bg-emerald-600'"></span>
                                    <span x-show="String(lockPasswordInput || '').length < i" class="w-2 h-2 rounded-full inline-block" :class="isLockError ? 'bg-red-300' : 'bg-gray-300'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="lockErrorMessage" class="text-xs text-red-600 font-medium text-center" x-text="lockErrorMessage"></div>

                    <button 
                        type="submit" 
                        :disabled="!lockPasswordInput || String(lockPasswordInput).trim().length !== 6" 
                        wire:loading.attr="disabled"
                        wire:target="unlockScreenWithPin"
                        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-medium text-sm rounded-lg shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer flex items-center justify-center gap-2 mt-2"
                    >
                        <span wire:loading.remove wire:target="unlockScreenWithPin">Buka Kunci Layar</span>
                        <span wire:loading.flex wire:target="unlockScreenWithPin" class="items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memeriksa PIN...
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- MODAL: Void Transaksi POS - Filament Native Style -->
        <div x-show="showVoidModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[110] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showVoidModal = false"
                 x-show="showVoidModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-md rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-950">Batalkan Transaksi (Void)</h3>
                            <p class="text-xs text-gray-500 font-medium">Otorisasi supervisor untuk membatalkan nota</p>
                        </div>
                    </div>
                    <button type="button" @click="showVoidModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitVoidOrder()" class="space-y-0">
                    <div class="p-6 space-y-4">
                        <div class="bg-rose-50 rounded-lg p-3.5 border border-rose-200">
                            <div class="text-[10px] font-bold text-rose-700 uppercase tracking-wider mb-1">Nota yang akan dibatalkan</div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900 text-sm" x-text="'#' + voidOrderNumber"></span>
                                <span class="font-extrabold text-rose-700 text-base" x-text="'Rp ' + formatMoney(voidOrderTotal)"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">1. Supervisor Pengizin Void</label>
                            <select x-model="voidSupervisorIdInput" class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-xs">
                                <option value="">-- Pilih Supervisor / Manager --</option>
                                <template x-for="sup in supervisors" :key="sup.id">
                                    <option :value="sup.id" x-text="sup.name + ' (' + sup.role + ')'"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">2. Alasan Pembatalan / Void</label>
                            <input type="text" x-model="voidReasonInput" placeholder="Contoh: Salah input barang / Batal beli"
                                   class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-xs">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-gray-700">3. PIN Supervisor (6 Digit) <span class="text-red-500">*</span></label>
                                <button type="button" @click="showVoidPinText = !showVoidPinText" class="text-xs font-medium text-rose-600 hover:text-rose-700 focus:outline-none cursor-pointer flex items-center gap-1" tabindex="-1">
                                    <span x-text="showVoidPinText ? 'Sembunyikan' : 'Lihat PIN'"></span>
                                </button>
                            </div>
                            <div class="relative cursor-pointer" @click="$refs.voidPinField.focus()">
                                <input 
                                    x-ref="voidPinField"
                                    type="text" 
                                    maxlength="6" 
                                    pattern="[0-9]*" 
                                    inputmode="numeric" 
                                    :value="voidSupervisorPinInput"
                                    @input="voidSupervisorPinInput = $event.target.value.replace(/\D/g, '').slice(0, 6)"
                                    class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10"
                                />
                                <div class="grid grid-cols-6 gap-2">
                                    <template x-for="i in 6" :key="i">
                                        <div class="h-11 rounded-lg border text-center flex items-center justify-center text-lg font-bold transition-all duration-150"
                                            :class="{
                                                'border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/40': (voidSupervisorPinInput || '').length === i - 1,
                                                'border-rose-600 bg-white text-rose-700 shadow-xs': (voidSupervisorPinInput || '').length >= i,
                                                'border-gray-300 bg-gray-50/60 text-gray-400': (voidSupervisorPinInput || '').length < i - 1
                                            }">
                                            <template x-if="(voidSupervisorPinInput || '').length >= i">
                                                <span x-show="!showVoidPinText" class="w-2.5 h-2.5 rounded-full inline-block bg-rose-600"></span>
                                            </template>
                                            <template x-if="(voidSupervisorPinInput || '').length >= i">
                                                <span x-show="showVoidPinText" class="font-bold text-base text-rose-700" x-text="(voidSupervisorPinInput || '')[i - 1]"></span>
                                            </template>
                                            <template x-if="(voidSupervisorPinInput || '').length < i">
                                                <span class="w-2 h-2 rounded-full inline-block bg-gray-300"></span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer Bar -->
                    <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl">
                        <button type="button" @click="showVoidModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Batal</button>
                        <button type="submit" :disabled="!voidSupervisorIdInput || !voidSupervisorPinInput || String(voidSupervisorPinInput).trim().length !== 6"
                                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-40 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Batalkan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Retur & Penukaran Ukuran Barang POS - Filament Native Style -->
        <div x-show="showReturnModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[115] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showReturnModal = false"
                 x-show="showReturnModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-2xl max-h-[90vh] flex flex-col rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white flex-shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-950">Retur & Penukaran Ukuran Barang</h3>
                            <p class="text-xs text-gray-500 font-medium" x-text="'Nota Asli: #' + returnOrderNumber"></p>
                        </div>
                    </div>
                    <button type="button" @click="showReturnModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Wizard Step Indicator -->
                <div class="px-6 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between text-xs font-semibold text-gray-500">
                    <div class="flex items-center gap-2" :class="returnStep === 1 ? 'text-amber-700 font-bold' : ''">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold" :class="returnStep === 1 ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-600'">1</span>
                        <span>1. Pilih Barang Retur</span>
                    </div>
                    <div class="h-0.5 w-12 bg-gray-200"></div>
                    <div class="flex items-center gap-2" :class="returnStep === 2 ? 'text-amber-700 font-bold' : ''">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold" :class="returnStep === 2 ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-600'">2</span>
                        <span>2. Opsi Retur & Otorisasi</span>
                    </div>
                </div>

                <form @submit.prevent="handleReturnSubmitClick()" class="flex flex-col flex-1 overflow-hidden space-y-0">
                    <div class="p-6 space-y-5 overflow-y-auto flex-1 bg-gray-50/50">
                        
                        <!-- ==================== LANGKAH 1: PILIH BARANG NOTA ==================== -->
                        <div x-show="returnStep === 1" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider">Pilih Barang & Kuantitas yang Dikembalikan</label>
                                <span class="text-xs font-medium text-gray-500" x-text="returnOrderItems.filter(i => (i.return_qty || 0) > 0).length + ' barang dipilih'"></span>
                            </div>

                            <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                                <template x-for="(item, idx) in returnOrderItems" :key="item.id || idx">
                                    <div class="flex items-center justify-between bg-white p-3.5 rounded-xl border transition-all duration-150 shadow-xs"
                                         :class="(item.return_qty || 0) > 0 ? 'border-amber-400 ring-2 ring-amber-500/10 bg-amber-50/30' : 'border-gray-200'">
                                        <div class="flex-1 pr-4">
                                            <div class="font-bold text-xs text-gray-950" x-text="item.name"></div>
                                            <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-2">
                                                <span>Rp <strong x-text="formatMoney(item.price)"></strong> / pcs</span>
                                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                                <span>Dibeli: <strong class="text-gray-700" x-text="(item.quantity || item.qty || 1) + ' pcs'"></strong></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-[11px] font-semibold text-gray-500">Kuantitas Retur:</span>
                                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white shadow-xs">
                                                <button type="button" @click="item.return_qty = Math.max(0, (item.return_qty || 0) - 1)" class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 text-sm transition cursor-pointer select-none">-</button>
                                                <input type="text"
                                                       inputmode="numeric"
                                                       pattern="[0-9]*"
                                                       :value="item.return_qty || 0"
                                                       @input="const val = parseInt($event.target.value.replace(/\D/g, '') || '0', 10); item.return_qty = Math.max(0, Math.min((item.quantity || item.qty || 1), val));"
                                                       class="w-12 text-center text-xs font-bold border-none bg-transparent p-1 focus:ring-0">
                                                <button type="button" @click="item.return_qty = Math.min((item.quantity || item.qty || 1), (item.return_qty || 0) + 1)" class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 text-sm transition cursor-pointer select-none">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 flex justify-between items-center text-xs font-semibold text-amber-900">
                                <span>Subtotal Nilai Barang Retur:</span>
                                <span class="text-sm font-bold text-amber-700" x-text="'Rp ' + formatMoney(returnSubtotal)"></span>
                            </div>
                        </div>

                        <!-- ==================== LANGKAH 2: OPSI RETUR & OTORISASI ==================== -->
                        <div x-show="returnStep === 2" class="space-y-5">
                            <!-- Jenis Retur -->
                            <div>
                                <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">Pilih Jenis Transaksi Retur</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition shadow-xs"
                                           :class="returnType === 'exchange' ? 'border-2 border-amber-600 bg-amber-50/80 text-amber-950 font-bold shadow-sm' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'">
                                        <input type="radio" x-model="returnType" value="exchange" class="text-amber-600 focus:ring-amber-500">
                                        <div>
                                            <div class="text-xs font-bold">Tukar Barang / Ukuran</div>
                                            <div class="text-[11px] font-normal text-gray-500 mt-0.5">Tukar ke varian/produk pengganti</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition shadow-xs"
                                           :class="returnType === 'refund' ? 'border-2 border-rose-600 bg-rose-50/80 text-rose-950 font-bold shadow-sm' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'">
                                        <input type="radio" x-model="returnType" value="refund" class="text-rose-600 focus:ring-rose-500">
                                        <div>
                                            <div class="text-xs font-bold">Pengembalian Uang (Refund)</div>
                                            <div class="text-[11px] font-normal text-gray-500 mt-0.5">Kembalikan kas uang ke pelanggan</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Opsi Metode Pengembalian Uang (HANYA MUNCUL JIKA REFUND ATAU TUKAR LEBIH MURAH) -->
                            <template x-if="returnType === 'refund' || (returnType === 'exchange' && returnExchangedItems.length > 0 && returnNetAmount < 0)">
                                <div>
                                    <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">Metode Pembayaran Refund / Kembalian Kas</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition shadow-xs"
                                               :class="refundPaymentMethod === 'cash' ? 'border-2 border-emerald-600 bg-emerald-50/80 text-emerald-950 font-bold' : 'border-gray-200 bg-white text-gray-700'">
                                            <input type="radio" x-model="refundPaymentMethod" value="cash" class="text-emerald-600 focus:ring-emerald-500">
                                            <div>
                                                <div class="text-xs font-bold">Tunai di Laci Kasir</div>
                                                <div class="text-[11px] font-normal text-gray-500">Memotong fisik kas laci shift ini</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition shadow-xs"
                                               :class="refundPaymentMethod === 'bank' ? 'border-2 border-blue-600 bg-blue-50/80 text-blue-950 font-bold' : 'border-gray-200 bg-white text-gray-700'">
                                            <input type="radio" x-model="refundPaymentMethod" value="bank" class="text-blue-600 focus:ring-blue-500">
                                            <div>
                                                <div class="text-xs font-bold">Transfer Bank Utama</div>
                                                <div class="text-[11px] font-normal text-gray-500">Laci kasir tidak berkurang</div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Detail Rekening Tujuan Transfer Bank Refund -->
                                    <template x-if="refundPaymentMethod === 'bank'">
                                        <div class="mt-3 p-3 bg-blue-50/80 rounded-xl border border-blue-200 space-y-2 shadow-xs">
                                            <div class="text-[11px] font-bold text-blue-900 uppercase tracking-wider">Detail Rekening Tujuan Refund</div>
                                            <div class="grid grid-cols-2 gap-2.5">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">Bank / E-Wallet</label>
                                                    <select x-model="refundBankName" class="w-full px-2.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-blue-500 shadow-xs">
                                                        <option value="BCA">BCA</option>
                                                        <option value="Mandiri">Mandiri</option>
                                                        <option value="BRI">BRI</option>
                                                        <option value="BNI">BNI</option>
                                                        <option value="BSI">BSI</option>
                                                        <option value="GoPay">GoPay</option>
                                                        <option value="OVO">OVO</option>
                                                        <option value="ShopeePay">ShopeePay</option>
                                                        <option value="DANA">DANA</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">No. Rek / HP & Nama</label>
                                                    <input type="text" x-model="refundBankAccount" placeholder="Contoh: 1234567890 a.n. Budi"
                                                           class="w-full px-2.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 shadow-xs">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Barang Pengganti (Hanya jika Tukar Barang) -->
                            <template x-if="returnType === 'exchange'">
                                <div>
                                    <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">Pilih Barang Pengganti (Tukar Ukuran/Varian)</label>

                                    <template x-if="returnExchangedItems.length > 0">
                                        <div class="mb-3 space-y-2">
                                            <template x-for="(eItem, eIdx) in returnExchangedItems" :key="eIdx">
                                                <div class="flex items-center justify-between bg-amber-50/80 border border-amber-200 rounded-xl px-3.5 py-2.5 text-xs shadow-xs">
                                                    <div>
                                                        <span class="font-bold text-gray-950" x-text="eItem.name"></span>
                                                        <span class="text-amber-800 font-semibold ml-2" x-text="'Rp ' + formatMoney(eItem.price)"></span>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex items-center border border-amber-300 rounded-lg bg-white overflow-hidden shadow-xs">
                                                            <button type="button" @click="if(eItem.quantity > 1) eItem.quantity--; else removeExchangeItem(eIdx)" class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 font-bold text-gray-600 text-xs cursor-pointer">-</button>
                                                            <span class="px-2.5 font-bold text-xs" x-text="eItem.quantity"></span>
                                                            <button type="button" @click="eItem.quantity++" class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 font-bold text-gray-600 text-xs cursor-pointer">+</button>
                                                        </div>
                                                        <button type="button" @click="removeExchangeItem(eIdx)" class="text-rose-600 font-semibold text-xs hover:underline cursor-pointer">Hapus</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <div class="border border-gray-300 rounded-xl overflow-hidden bg-white shadow-xs">
                                        <input type="text" x-model="exchangeSearchQuery"
                                               placeholder="Cari produk pengganti di sini..."
                                               class="w-full text-xs border-0 border-b border-gray-200 px-3.5 py-2.5 focus:ring-0 bg-white"
                                               autocomplete="off">
                                        <div class="max-h-40 overflow-y-auto divide-y divide-gray-100">
                                            <template x-for="p in allProducts.filter(p => !exchangeSearchQuery || p.name.toLowerCase().includes(exchangeSearchQuery.toLowerCase())).slice(0,15)" :key="p.id">
                                                <div>
                                                    <template x-if="!p.has_variants">
                                                        <button type="button"
                                                                :disabled="p.stock <= 0"
                                                                @click="addExchangeItem(p); exchangeSearchQuery = '';"
                                                                class="w-full text-left px-3.5 py-2 text-xs hover:bg-amber-50 disabled:opacity-40 disabled:cursor-not-allowed flex justify-between items-center cursor-pointer">
                                                            <div>
                                                                <span class="font-semibold text-gray-900" x-text="p.name"></span>
                                                                <span class="text-gray-500 ml-2" x-text="'Rp ' + formatMoney(p.price)"></span>
                                                            </div>
                                                            <span class="text-gray-400 text-[11px]" x-text="'Stok: ' + p.stock"></span>
                                                        </button>
                                                    </template>
                                                    <template x-if="p.has_variants">
                                                        <div class="px-3.5 py-2 bg-gray-50/50">
                                                            <div class="text-[11px] font-bold text-gray-700 mb-1" x-text="p.name"></div>
                                                            <div class="flex flex-wrap gap-1.5">
                                                                <template x-for="v in p.variants" :key="v.id">
                                                                    <button type="button"
                                                                            :disabled="v.stock <= 0"
                                                                            @click="addExchangeItem({id: p.id, name: p.name, price: p.price, stock: p.stock, has_variants: true}, {id: v.id, name: v.name, price: v.price, stock: v.stock}); exchangeSearchQuery = '';"
                                                                            class="px-2.5 py-1 bg-white border border-gray-300 rounded-lg text-xs font-medium hover:bg-amber-50 hover:border-amber-400 cursor-pointer disabled:opacity-40 shadow-xs">
                                                                        <span x-text="v.name"></span>
                                                                        <span class="text-gray-400 ml-1" x-text="'(' + v.stock + ')'"></span>
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Ringkasan Nilai Selisih -->
                            <div class="bg-amber-50/80 rounded-xl p-4 border border-amber-200 space-y-2 shadow-xs">
                                <div class="flex justify-between text-xs font-medium text-gray-600">
                                    <span>Total Barang Retur:</span>
                                    <span class="font-bold text-gray-900" x-text="'- Rp ' + formatMoney(returnSubtotal)"></span>
                                </div>
                                <template x-if="returnType === 'exchange'">
                                    <div class="flex justify-between text-xs font-medium text-gray-600">
                                        <span>Total Barang Tukar:</span>
                                        <span class="font-bold text-gray-900" x-text="'+ Rp ' + formatMoney(exchangeSubtotal)"></span>
                                    </div>
                                </template>
                                <div class="pt-2 border-t border-amber-200 flex justify-between items-center">
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Hasil Akhir Selisih:</span>
                                    <template x-if="returnNetAmount > 0">
                                        <span class="text-xs font-bold text-amber-800" x-text="'Pelanggan Lebih Mahut: +Rp ' + formatMoney(returnNetAmount)"></span>
                                    </template>
                                    <template x-if="returnNetAmount < 0">
                                        <span class="text-xs font-bold text-rose-700" x-text="'Pengembalian Uang (' + (refundPaymentMethod === 'bank' ? 'Bank Transfer' : 'Kas Laci') + '): Rp ' + formatMoney(Math.abs(returnNetAmount))"></span>
                                    </template>
                                    <template x-if="returnNetAmount === 0">
                                        <span class="text-xs font-bold text-gray-700">Pas (Selisih Rp 0)</span>
                                    </template>
                                </div>
                            </div>

                            <!-- Petunjuk untuk Selisih Positif (Tambah Bayar / Lebih Mahal) -->
                            <template x-if="returnNetAmount > 0">
                                <div class="p-3.5 bg-amber-100/90 rounded-xl border border-amber-300 text-amber-950 text-xs space-y-1 shadow-xs">
                                    <div class="font-bold flex items-center gap-1.5 text-amber-900">
                                        <svg class="w-4 h-4 text-amber-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Penukaran Lebih Mahal (+Rp <strong x-text="formatMoney(returnNetAmount)"></strong>)</span>
                                    </div>
                                    <p class="text-[11px] leading-relaxed text-amber-900">
                                        Untuk transaksi dengan penambahan bayar atau pembelian item baru, silakan lakukan <strong>Refund Nota</strong> di sini lebih dahulu, lalu masukkan barang baru ke <strong>Keranjang POS Utama</strong> agar alur pembayaran (Cash/QRIS/EDC) & Struk Pembelian Baru tercetak resmi.
                                    </p>
                                </div>
                            </template>

                            <!-- Alasan Retur -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Retur / Penukaran</label>
                                <input type="text" x-model="returnReasonInput" placeholder="Contoh: Ukuran kekecilan / Tukar warna / Cacat barang"
                                       class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-xs">
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer & Wizard Navigation Buttons -->
                    <div class="px-6 py-3.5 bg-gray-50/90 border-t border-gray-200 flex items-center justify-between rounded-b-xl flex-shrink-0">
                        <div>
                            <button type="button" @click="showReturnModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Batal</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="returnStep === 2">
                                <button type="button" @click="returnStep = 1" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition cursor-pointer">← Langkah 1</button>
                            </template>
                            <template x-if="returnStep === 1">
                                <button type="button" :disabled="returnSubtotal <= 0" @click="returnStep = 2" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-40 text-white font-semibold text-xs rounded-lg shadow-xs transition cursor-pointer">Lanjut ke Langkah 2 →</button>
                            </template>
                            <template x-if="returnStep === 2">
                                <button type="button" @click="handleReturnSubmitClick()" :disabled="returnSubtotal <= 0 || returnNetAmount > 0" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-40 text-white font-semibold text-xs rounded-lg shadow-xs transition cursor-pointer">Proses Retur Sekarang</button>
                            </template>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Otorisasi Supervisor Retur & Refund (6-Box Dot PIN) -->
        <div x-show="showReturnSupervisorModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[120] flex items-center justify-center bg-gray-950/60 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showReturnSupervisorModal = false"
                 x-show="showReturnSupervisorModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-sm rounded-2xl border border-gray-200 shadow-2xl p-6 font-sans space-y-4">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2 text-rose-700 font-bold text-sm">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Otorisasi PIN Supervisor</span>
                    </div>
                    <button type="button" @click="showReturnSupervisorModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Supervisor Pengizin</label>
                        <select x-model="returnSupervisorIdInput" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-xs">
                            <option value="">-- Pilih Supervisor --</option>
                            <template x-for="sup in supervisors" :key="sup.id">
                                <option :value="sup.id" x-text="sup.name + ' (' + sup.role + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 text-center">PIN 6-Digit Supervisor</label>
                        <div class="relative cursor-pointer" @click="$refs.returnPinModalInput.focus()">
                            <input x-ref="returnPinModalInput" type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric"
                                   x-model="returnSupervisorPinInput"
                                   class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10" />
                            <div class="grid grid-cols-6 gap-2">
                                <template x-for="i in 6" :key="i">
                                    <div class="h-10 rounded-xl border-2 flex items-center justify-center font-bold text-lg transition-all duration-150 shadow-xs"
                                         :class="(returnSupervisorPinInput || '').length >= i ? 'border-rose-500 bg-rose-50 text-rose-950 scale-105' : 'border-gray-300 bg-white text-gray-400'">
                                        <span x-text="(returnSupervisorPinInput || '').length >= i ? '●' : ''"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showReturnSupervisorModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-xl shadow-xs transition">Batal</button>
                    <button type="button" @click="confirmReturnWithSupervisor()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">Konfirmasi Otorisasi</button>
                </div>
            </div>
        </div>

        <!-- MODAL: Ganti PIN POS 6-Digit -->
        <div x-show="showChangePinModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" style="display: none;">
            <div @click.away="showChangePinModal = false"
                 x-show="showChangePinModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-md rounded-2xl p-6 relative shadow-2xl">
                
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Ganti PIN POS 6-Digit
                    </h3>
                    <button type="button" @click="showChangePinModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="changePosPin" class="space-y-4" x-data="{ showOld: false, showNew: false, showConfirm: false }">
                    <!-- PIN Lama -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-gray-700">PIN Lama (6 Digit) <span class="text-red-500">*</span></label>
                            <button type="button" @click="showOld = !showOld" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 cursor-pointer" tabindex="-1">
                                <span x-text="showOld ? 'Sembunyikan' : 'Lihat PIN'"></span>
                            </button>
                        </div>
                        <div class="relative cursor-pointer" @click="$refs.oldPinField.focus()">
                            <input x-ref="oldPinField" type="text" maxlength="6" pattern="[0-9]*" inputmode="numeric"
                                   :value="$wire.oldPosPin"
                                   @input="$wire.set('oldPosPin', $event.target.value.replace(/\D/g, '').slice(0, 6))"
                                   class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10" />
                            <div class="grid grid-cols-6 gap-2">
                                <template x-for="i in 6" :key="i">
                                    <div class="h-11 rounded-lg border text-center flex items-center justify-center text-lg font-bold transition-all duration-150"
                                        :class="{
                                            'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/40': ($wire.oldPosPin || '').length === i - 1,
                                            'border-emerald-600 bg-white text-emerald-700 shadow-xs': ($wire.oldPosPin || '').length >= i,
                                            'border-gray-300 bg-gray-50/60 text-gray-400': ($wire.oldPosPin || '').length < i - 1
                                        }">
                                        <template x-if="($wire.oldPosPin || '').length >= i">
                                            <span x-show="!showOld" class="w-2.5 h-2.5 rounded-full inline-block bg-emerald-600"></span>
                                        </template>
                                        <template x-if="($wire.oldPosPin || '').length >= i">
                                            <span x-show="showOld" class="font-bold text-base text-emerald-700" x-text="($wire.oldPosPin || '')[i - 1]"></span>
                                        </template>
                                        <template x-if="($wire.oldPosPin || '').length < i">
                                            <span class="w-2 h-2 rounded-full inline-block bg-gray-300"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('oldPosPin') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- PIN Baru -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-gray-700">PIN Baru (6 Digit) <span class="text-red-500">*</span></label>
                            <button type="button" @click="showNew = !showNew" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 cursor-pointer" tabindex="-1">
                                <span x-text="showNew ? 'Sembunyikan' : 'Lihat PIN'"></span>
                            </button>
                        </div>
                        <div class="relative cursor-pointer" @click="$refs.newPinField.focus()">
                            <input x-ref="newPinField" type="text" maxlength="6" pattern="[0-9]*" inputmode="numeric"
                                   :value="$wire.newPosPin"
                                   @input="$wire.set('newPosPin', $event.target.value.replace(/\D/g, '').slice(0, 6))"
                                   class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10" />
                            <div class="grid grid-cols-6 gap-2">
                                <template x-for="i in 6" :key="i">
                                    <div class="h-11 rounded-lg border text-center flex items-center justify-center text-lg font-bold transition-all duration-150"
                                        :class="{
                                            'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/40': ($wire.newPosPin || '').length === i - 1,
                                            'border-emerald-600 bg-white text-emerald-700 shadow-xs': ($wire.newPosPin || '').length >= i,
                                            'border-gray-300 bg-gray-50/60 text-gray-400': ($wire.newPosPin || '').length < i - 1
                                        }">
                                        <template x-if="($wire.newPosPin || '').length >= i">
                                            <span x-show="!showNew" class="w-2.5 h-2.5 rounded-full inline-block bg-emerald-600"></span>
                                        </template>
                                        <template x-if="($wire.newPosPin || '').length >= i">
                                            <span x-show="showNew" class="font-bold text-base text-emerald-700" x-text="($wire.newPosPin || '')[i - 1]"></span>
                                        </template>
                                        <template x-if="($wire.newPosPin || '').length < i">
                                            <span class="w-2 h-2 rounded-full inline-block bg-gray-300"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('newPosPin') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Konfirmasi PIN Baru -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-gray-700">Konfirmasi PIN Baru <span class="text-red-500">*</span></label>
                            <button type="button" @click="showConfirm = !showConfirm" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 cursor-pointer" tabindex="-1">
                                <span x-text="showConfirm ? 'Sembunyikan' : 'Lihat PIN'"></span>
                            </button>
                        </div>
                        <div class="relative cursor-pointer" @click="$refs.confirmPinField.focus()">
                            <input x-ref="confirmPinField" type="text" maxlength="6" pattern="[0-9]*" inputmode="numeric"
                                   :value="$wire.newPosPinConfirm"
                                   @input="$wire.set('newPosPinConfirm', $event.target.value.replace(/\D/g, '').slice(0, 6))"
                                   class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10" />
                            <div class="grid grid-cols-6 gap-2">
                                <template x-for="i in 6" :key="i">
                                    <div class="h-11 rounded-lg border text-center flex items-center justify-center text-lg font-bold transition-all duration-150"
                                        :class="{
                                            'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/40': ($wire.newPosPinConfirm || '').length === i - 1,
                                            'border-emerald-600 bg-white text-emerald-700 shadow-xs': ($wire.newPosPinConfirm || '').length >= i,
                                            'border-gray-300 bg-gray-50/60 text-gray-400': ($wire.newPosPinConfirm || '').length < i - 1
                                        }">
                                        <template x-if="($wire.newPosPinConfirm || '').length >= i">
                                            <span x-show="!showConfirm" class="w-2.5 h-2.5 rounded-full inline-block bg-emerald-600"></span>
                                        </template>
                                        <template x-if="($wire.newPosPinConfirm || '').length >= i">
                                            <span x-show="showConfirm" class="font-bold text-base text-emerald-700" x-text="($wire.newPosPinConfirm || '')[i - 1]"></span>
                                        </template>
                                        <template x-if="($wire.newPosPinConfirm || '').length < i">
                                            <span class="w-2 h-2 rounded-full inline-block bg-gray-300"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('newPosPinConfirm') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showChangePinModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg shadow-brand-500/25 transition-all active:scale-95">Perbarui PIN</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Petty Cash (Kas Masuk/Keluar) - Filament Native Style -->
        <div x-show="showPettyCashModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showPettyCashModal = false"
                 x-show="showPettyCashModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-md rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                    <div>
                        <h3 class="text-base font-bold text-gray-950">Catat Kas Masuk / Keluar</h3>
                        <p class="text-xs text-gray-500 font-medium">Pencatatan kas petty kasir shift ini</p>
                    </div>
                    <button type="button" @click="showPettyCashModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="addPettyCash" class="space-y-0" x-data="{ activeType: @entangle('pettyCashType') }">
                    <div class="p-6 space-y-4">
                        <!-- Hint Limit Mandiri Kasir -->
                        <div x-show="activeType === 'out'"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-98"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="p-3 bg-amber-50/80 border border-amber-200/80 rounded-lg flex items-start gap-2 text-xs text-amber-900">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <span class="font-semibold">
                                    @if($this->pettyCashLimitMode === 'cumulative')
                                        Limit Akumulasi Shift:
                                    @elseif($this->pettyCashLimitMode === 'per_transaction')
                                        Limit Per Transaksi:
                                    @else
                                        Limit Kas (Kombinasi):
                                    @endif
                                </span> Rp {{ number_format($this->pettyCashMaxLimit, 0, ',', '.') }}.
                                <span class="text-amber-800 font-normal">
                                    @if($this->pettyCashLimitMode === 'cumulative')
                                        Pengeluaran yang menyebabkan akumulasi kas keluar shift ini melebihi limit butuh PIN Supervisor.
                                    @else
                                        Pengeluaran di atas nominal ini membutuhkan otorisasi PIN Supervisor.
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Segmented Switch: Kas Keluar vs Kas Masuk -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tipe Pencatatan Kas</label>
                            <div class="grid grid-cols-2 gap-1.5 p-1 bg-gray-100 rounded-lg border border-gray-200/80">
                                <button type="button"
                                        @click="activeType = 'out'; $wire.set('pettyCashType', 'out')"
                                        class="py-2 rounded-md text-xs font-bold transition duration-150 flex items-center justify-center gap-1.5 cursor-pointer"
                                        :class="activeType === 'out' ? 'bg-white text-rose-700 shadow-xs border border-gray-200' : 'text-gray-600 hover:text-gray-900'">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                    <span>Kas Keluar (Pengeluaran)</span>
                                </button>
                                <button type="button"
                                        @click="activeType = 'in'; $wire.set('pettyCashType', 'in')"
                                        class="py-2 rounded-md text-xs font-bold transition duration-150 flex items-center justify-center gap-1.5 cursor-pointer"
                                        :class="activeType === 'in' ? 'bg-white text-emerald-700 shadow-xs border border-gray-200' : 'text-gray-600 hover:text-gray-900'">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                    <span>Kas Masuk (Pemasukan)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Input Nominal -->
                        <div x-data="{
                            rawVal: @entangle('pettyCashAmount'),
                            displayVal: '',
                            formatInput(val) {
                                let clean = String(val || '').replace(/\D/g, '');
                                clean = clean.replace(/^0+/, '');
                                let num = clean ? parseInt(clean, 10) : 0;
                                this.rawVal = num;
                                this.displayVal = num > 0 ? num.toLocaleString('id-ID') : '';
                            },
                            init() {
                                this.formatInput(this.rawVal);
                                this.$watch('rawVal', (v) => {
                                    let num = v ? parseInt(v, 10) : 0;
                                    this.displayVal = num > 0 ? num.toLocaleString('id-ID') : '';
                                });
                            }
                        }">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Uang (Rp)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-gray-500">Rp</div>
                                <input type="text"
                                       inputmode="numeric"
                                       :value="displayVal"
                                       @input="formatInput($event.target.value)"
                                       class="w-full pl-9 pr-3.5 py-2 bg-white border border-gray-300 rounded-lg text-base font-bold text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs"
                                       placeholder="0">
                            </div>
                            @error('pettyCashAmount') <span class="text-rose-600 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Input Keterangan -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan / Alasan</label>
                            <input type="text" wire:model="pettyCashNotes" class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs" placeholder="Contoh: Beli air galon toko / Pembelian lakban">
                            @error('pettyCashNotes') <span class="text-rose-600 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Modal Footer Action Bar -->
                    <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl">
                        <button type="button" @click="showPettyCashModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="addPettyCash"
                                class="px-4 py-2 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="activeType === 'out' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                            <span wire:loading.remove wire:target="addPettyCash" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Kas</span>
                            </span>
                            <span wire:loading wire:target="addPettyCash" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Menyimpan...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Peringatan Limit Kas Kecil -> Minta Izin Supervisor -->
        <div x-show="showPettyCashLimitConfirmModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[110] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showPettyCashLimitConfirmModal = false"
                 x-show="showPettyCashLimitConfirmModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-md rounded-xl border border-gray-200 shadow-2xl p-6 space-y-5 text-center font-sans">
                
                <div class="mx-auto w-12 h-12 rounded-full bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-lg font-bold text-gray-950">Melebihi Limit Kas Kecil</h3>
                    <p class="text-xs text-gray-600 leading-relaxed" x-text="pettyCashLimitMessage || 'Pengeluaran ini melebihi limit mandiri kasir dan memerlukan otorisasi Supervisor.'"></p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="showPettyCashLimitConfirmModal = false"
                            class="flex-1 py-2 px-4 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="proceedPettyCashSupervisorAuth()"
                            class="flex-1 py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Minta Izin Supervisor</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Voucher / Promo - Filament Native Style -->
        <div x-show="showVoucherModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showVoucherModal = false"
                 x-show="showVoucherModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-lg rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans flex flex-col max-h-[85vh]">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white flex-shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-gray-950">Pilih Kupon Promo</h3>
                        <p class="text-xs text-gray-500 font-medium">Voucher diskon aktif untuk transaksi ini</p>
                    </div>
                    <button type="button" @click="showVoucherModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto flex-1 space-y-3 bg-gray-50/50">
                    <template x-if="vouchers.length === 0">
                        <div class="text-center py-10 text-gray-400">
                            <p class="text-xs font-semibold">Tidak ada promo yang sedang aktif saat ini.</p>
                        </div>
                    </template>
                    
                    <template x-for="v in vouchers" :key="v.id">
                        <div 
                            class="border rounded-lg p-3.5 flex flex-col gap-2 transition-all shadow-xs"
                            :class="isVoucherEligible(v) ? (activeVoucher && activeVoucher.id === v.id ? 'bg-emerald-50/80 border-emerald-500' : 'bg-white border-gray-200 hover:border-emerald-400 cursor-pointer') : 'bg-gray-50 border-gray-200 opacity-60 cursor-not-allowed'"
                            @click="isVoucherEligible(v) ? applyVoucher(v) : null">
                            
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h4 class="font-bold text-xs text-gray-950" x-text="v.name"></h4>
                                        <template x-if="getVoucherLoyaltyTier(v.id)">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                                🎁 Loyalti Tier <span x-text="getVoucherLoyaltyTier(v.id).min_stamps"></span> Cap
                                            </span>
                                        </template>
                                    </div>
                                    <div class="text-[10px] text-gray-500 font-mono mt-0.5 bg-gray-100 border border-gray-200 px-1.5 py-0.5 rounded inline-block uppercase" x-text="v.code"></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-extrabold text-xs text-emerald-700" x-text="v.discount_type === 'percent' ? v.discount_amount + '%' : 'Rp ' + formatMoney(v.discount_amount)"></div>
                                </div>
                            </div>
                            
                            <div class="text-[11px] text-gray-500 flex items-center justify-between">
                                <div class="flex flex-col gap-0.5">
                                    <span x-show="v.min_purchase > 0" x-text="'Min. Belanja: Rp ' + formatMoney(v.min_purchase)"></span>
                                    <span x-show="v.min_items > 0" x-text="'Min. Item: ' + v.min_items + ' pcs'"></span>
                                    <span x-show="v.min_purchase <= 0 && v.min_items <= 0">Tanpa min. belanja</span>
                                </div>
                                
                                <span x-show="isVoucherUsedByActiveCustomer(v)" class="text-[11px] font-bold text-gray-500 bg-gray-200 px-2 py-0.5 rounded">
                                    Sudah Pernah Digunakan
                                </span>
                                <span x-show="isVoucherLoyaltyLocked(v) && !isVoucherUsedByActiveCustomer(v)" class="text-[11px] font-bold text-rose-600 flex items-center gap-1">
                                    Wajib <span x-text="getVoucherLoyaltyTier(v.id)?.min_stamps"></span> Cap (Anda: <span x-text="activeCustomerLoyalty ? activeCustomerLoyalty.stamp_count : 0"></span> Cap)
                                </span>
                                <span x-show="!isVoucherLoyaltyLocked(v) && !isVoucherUsedByActiveCustomer(v) && !isVoucherEligible(v)" class="text-[11px] font-semibold text-rose-600">Syarat belum terpenuhi</span>
                                <span x-show="activeVoucher && activeVoucher.id === v.id" class="text-[11px] font-bold text-emerald-700 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Dipakai
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-between rounded-b-xl flex-shrink-0">
                    <div>
                        <button type="button" x-show="activeVoucher" @click="removeVoucher()" class="text-rose-600 text-xs font-semibold hover:underline cursor-pointer">Lepas Promo</button>
                    </div>
                    <button type="button" @click="showVoucherModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- PAGE: Riwayat Transaksi (Filament Native Style) -->
        <!-- ============================================ -->
        <div x-show="activePage === 'history'" x-cloak wire:key="pos-page-history" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50/50 overflow-y-auto font-sans">
            <div class="p-4 md:p-6 max-w-7xl w-full mx-auto space-y-5">
                
                <!-- Header Title Bar -->
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button @click="activePage = 'kasir'" class="p-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Kembali ke Kasir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight text-gray-950">
                                Riwayat Transaksi POS
                            </h1>
                            <p class="text-xs text-gray-500 font-medium">Manajemen nota penjualan, cetak ulang struk, retur, dan pembatalan nota.</p>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan KPI -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Omzet Filtered</p>
                        <p class="text-2xl font-bold text-gray-950 mt-1">Rp {{ number_format($sessionOrders->where('status', '!=', 'cancelled')->sum('grand_total'), 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Nota Selesai</p>
                        <p class="text-2xl font-bold text-gray-950 mt-1">{{ $sessionOrders->where('status', '!=', 'cancelled')->count() }} <span class="text-sm font-normal text-gray-400">nota</span></p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Nota VOID / Batal</p>
                        <p class="text-2xl font-bold text-gray-950 mt-1">{{ $sessionOrders->where('status', 'cancelled')->count() }} <span class="text-sm font-normal text-gray-400">nota</span></p>
                    </div>
                </div>

                <!-- UNIFIED TABLE CARD (Filament Native Card with Toolbar Header) -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs relative">
                    
                    <!-- Filament Table Header Toolbar (Search FAR LEFT + Filter Button FAR RIGHT) -->
                    <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between gap-4 bg-white rounded-t-xl">
                        <!-- SISI KIRI: Search Input -->
                        <div class="relative min-w-[220px] max-w-xs flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="historySearch" wire:key="history-search-input" placeholder="Cari nota, pelanggan, HP..."
                                   class="w-full pl-9 pr-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs">
                        </div>

                        <!-- SISI KANAN: Filter Action Button & Popover Modal -->
                        <!-- SISI KANAN: Filter Action Button & Popover Modal -->
                        <div class="relative flex-shrink-0" x-data="{ showFilterPopover: false }" wire:ignore.self wire:key="history-filter-container">
                            @php
                                $activeFilterCount = ($historyDateFilter !== 'shift' ? 1 : 0) +
                                                     ($historyPaymentFilter !== 'all' ? 1 : 0) +
                                                     ($historyStatusFilter !== 'all' ? 1 : 0);
                            @endphp

                            <!-- Filter Action Trigger Button -->
                            <button type="button" @click="showFilterPopover = !showFilterPopover"
                                    class="px-3.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-xs transition duration-150 flex items-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Filter Tabel">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                <span>Filter</span>
                                @if($activeFilterCount > 0)
                                <span class="w-4 h-4 bg-emerald-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $activeFilterCount }}</span>
                                @endif
                            </button>

                            <!-- Filament Filter Popover Card -->
                            <div x-show="showFilterPopover"
                                 x-cloak
                                 @click.away="showFilterPopover = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-4 space-y-4">

                                <!-- Popover Header -->
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                                    <span class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        Filter Tabel
                                    </span>
                                    @if($activeFilterCount > 0)
                                    <button type="button"
                                            wire:click="resetHistoryFilters"
                                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline cursor-pointer">
                                        Reset filter
                                    </button>
                                    @endif
                                </div>

                                <!-- Filter Options List -->
                                <div class="space-y-3">
                                    <!-- Periode Filter -->
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Periode Transaksi</label>
                                        <select wire:key="filter-date-select" wire:model.live="historyDateFilter"
                                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                            <option value="shift">Shift Hari Ini</option>
                                            <option value="today">Hari Ini</option>
                                            <option value="yesterday">Kemarin</option>
                                            <option value="7days">7 Hari Terakhir</option>
                                            <option value="30days">30 Hari Terakhir</option>
                                            <option value="all">Semua Riwayat</option>
                                        </select>
                                    </div>

                                    <!-- Metode Pembayaran Filter -->
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Metode Pembayaran</label>
                                        <select wire:key="filter-payment-select" wire:model.live="historyPaymentFilter"
                                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                            <option value="all">Semua Metode</option>
                                            @foreach($this->availableHistoryPaymentMethods as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Status Filter -->
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Status Transaksi</label>
                                        <select wire:key="filter-status-select" wire:model.live="historyStatusFilter"
                                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                            <option value="all">Semua Status</option>
                                            @foreach($this->availableHistoryStatuses as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Popover Footer -->
                                <div class="pt-2 border-t border-gray-100 flex justify-end">
                                    <button type="button" @click="showFilterPopover = false" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg transition cursor-pointer">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Content Area -->
                    @if(count($sessionOrders) === 0)
                    <div class="flex flex-col items-center justify-center text-gray-400 py-16">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-900">Tidak ada transaksi ditemukan</p>
                        <p class="text-xs text-gray-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter di atas.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto rounded-b-xl">
                        <table class="w-full text-left border-collapse">
                            <thead class="select-none">
                                <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3 px-4 cursor-pointer hover:bg-gray-100 transition" @click="sortHistoryClient('created_at')">
                                        <div class="flex items-center gap-1.5">
                                            <span>No. Nota & Waktu</span>
                                            <span class="text-xs font-bold" :class="historySortCol === 'created_at' ? 'text-emerald-600' : 'text-gray-400'" x-text="historySortCol === 'created_at' ? (historySortDir === 'desc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 cursor-pointer hover:bg-gray-100 transition" @click="sortHistoryClient('customer')">
                                        <div class="flex items-center gap-1.5">
                                            <span>Pelanggan</span>
                                            <span class="text-xs font-bold" :class="historySortCol === 'customer' ? 'text-emerald-600' : 'text-gray-400'" x-text="historySortCol === 'customer' ? (historySortDir === 'asc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4">Item Barang</th>
                                    <th class="py-3 px-4 text-right cursor-pointer hover:bg-gray-100 transition" @click="sortHistoryClient('grand_total')">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <span>Total Belanja</span>
                                            <span class="text-xs font-bold" :class="historySortCol === 'grand_total' ? 'text-emerald-600' : 'text-gray-400'" x-text="historySortCol === 'grand_total' ? (historySortDir === 'desc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 text-center cursor-pointer hover:bg-gray-100 transition" @click="sortHistoryClient('method')">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span>Metode</span>
                                            <span class="text-xs font-bold" :class="historySortCol === 'method' ? 'text-emerald-600' : 'text-gray-400'" x-text="historySortCol === 'method' ? (historySortDir === 'asc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 text-center cursor-pointer hover:bg-gray-100 transition" @click="sortHistoryClient('status')">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span>Status</span>
                                            <span class="text-xs font-bold" :class="historySortCol === 'status' ? 'text-emerald-600' : 'text-gray-400'" x-text="historySortCol === 'status' ? (historySortDir === 'asc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="posHistoryTableBody" class="divide-y divide-gray-100 text-xs">
                                @foreach($sessionOrders as $order)
                                <tr wire:key="order-row-{{ $order->id }}"
                                    data-history-row
                                    data-sort-created_at="{{ $order->created_at->timestamp }}"
                                    data-sort-customer="{{ strtolower($order->customer_name ?? '') }}"
                                    data-sort-grand_total="{{ $order->grand_total }}"
                                    data-sort-method="{{ strtolower($order->formatted_payment_method ?? '') }}"
                                    data-sort-status="{{ strtolower($order->status) }}"
                                    class="hover:bg-gray-50/80 transition-colors {{ $order->status === 'cancelled' ? 'bg-rose-50/20' : '' }}">
                                    <!-- No. Nota & Waktu -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold font-mono text-gray-900 text-xs">#{{ $order->order_number }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }} · Kasir: {{ $order->cashier->name ?? 'Kasir' }}</div>
                                    </td>

                                    <!-- Pelanggan -->
                                    <td class="py-3 px-4">
                                        @if($order->customer_name)
                                            <div class="font-semibold text-gray-900">{{ $order->customer_name }}</div>
                                            <div class="text-[11px] text-gray-500">{{ $order->customer_phone ?: 'Tanpa HP' }}</div>
                                        @else
                                            <span class="text-gray-400 italic">Pelanggan Umum</span>
                                        @endif
                                    </td>

                                    <!-- Item Barang -->
                                    <td class="py-3 px-4 max-w-xs">
                                        @php
                                            $firstItem = $order->items->first();
                                            $otherCount = $order->items->count() - 1;
                                            $totalPcs = $order->items->sum('quantity');
                                        @endphp
                                        @if($firstItem)
                                            <div class="font-medium text-gray-900 truncate">
                                                {{ $firstItem->product_name ?? $firstItem->name }}
                                            </div>
                                            <div class="text-[11px] text-gray-500">
                                                {{ $firstItem->quantity }} pcs @if($otherCount > 0) <span class="text-emerald-600 font-semibold">+{{ $otherCount }} item lain (Total {{ $totalPcs }} pcs)</span> @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Total Belanja -->
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="font-bold text-xs text-gray-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</div>
                                        @if($order->discount_total > 0)
                                            <div class="text-[10px] font-medium text-emerald-600">Potongan Rp {{ number_format($order->discount_total, 0, ',', '.') }}</div>
                                        @endif
                                    </td>

                                    <!-- Metode -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20">
                                            {{ $order->formatted_payment_method }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if($order->status === 'cancelled')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                                Dibatalkan
                                            </span>
                                        @elseif($order->posReturns && $order->posReturns->count() > 0)
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                    Selesai
                                                </span>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-600/30" title="Nota ini memiliki {{ $order->posReturns->count() }} riwayat retur/penukaran barang">
                                                    <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                    Ada Retur ({{ $order->posReturns->count() }})
                                                </span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                Selesai
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Detail Button -->
                                            <button type="button" @click="openDetailOrderModal(@js([
                                                'id' => $order->id,
                                                'order_number' => $order->order_number,
                                                'created_at' => $order->created_at->format('d M Y, H:i'),
                                                'cashier_name' => $order->cashier->name ?? 'Kasir',
                                                'customer_name' => $order->customer_name ?: 'Pelanggan Umum',
                                                'customer_phone' => $order->customer_phone ?: '-',
                                                'payment_method' => $order->formatted_payment_method,
                                                'subtotal' => (float)$order->subtotal,
                                                'discount_total' => (float)$order->discount_total,
                                                'grand_total' => (float)$order->grand_total,
                                                'cash_paid' => (float)$order->cash_paid,
                                                'cash_change' => (float)$order->cash_change,
                                                'status' => $order->status,
                                                'items' => $order->items->map(fn($i) => [
                                                    'name' => $i->product_name ?? $i->name,
                                                    'variant' => $i->variant_name,
                                                    'qty' => (int)$i->quantity,
                                                    'price' => (float)$i->price,
                                                    'subtotal' => (float)($i->total ?? $i->subtotal)
                                                ])->values()->all()
                                            ]))"
                                                    class="p-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg transition-colors cursor-pointer" title="Detail Rincian Nota">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>

                                            @if($order->status !== 'cancelled')
                                            <!-- Reprint Thermal Receipt Button -->
                                            <button type="button" @click="showReceiptPreviewModal = true; isProcessing = true; previewReceiptData = null; $wire.reprintReceipt({{ $order->id }})" :disabled="isProcessing"
                                                    class="p-1.5 bg-white border border-gray-300 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-gray-700 rounded-lg transition-colors cursor-pointer" title="{{ $order->posReturns && $order->posReturns->count() > 0 ? 'Pratinjau / Cetak Struk Penjualan & Retur' : 'Cetak Ulang Struk Thermal' }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h10z"/></svg>
                                            </button>

                                            @if($order->posReturns && $order->posReturns->count() > 0)
                                            <!-- Dedicated Return Receipt Print Button -->
                                            <button type="button" @click="showReceiptPreviewModal = true; isProcessing = true; previewReceiptData = null; $wire.reprintReturnReceipt({{ $order->posReturns->last()->id }})" :disabled="isProcessing"
                                                    class="p-1.5 bg-amber-50 border border-amber-300 hover:bg-amber-100 hover:border-amber-400 text-amber-800 rounded-lg transition-colors cursor-pointer" title="Pratinjau / Cetak Struk Retur (#{{ $order->posReturns->last()->return_number }})">
                                                <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            </button>
                                            @endif

                                            <!-- Void Order Button (Supervisor Authorization required) -->
                                            <button type="button" @click="openVoidModal({{ $order->id }}, '{{ $order->order_number }}', {{ (float)$order->grand_total }})"
                                                    class="p-1.5 bg-white border border-gray-300 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 text-gray-700 rounded-lg transition-colors cursor-pointer" title="Batalkan Nota (Butuh PIN Supervisor)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>

                                            <!-- Retur/Tukar Button -->
                                            <button type="button" @click="openReturnModal(@js([
                                                'id' => $order->id,
                                                'order_number' => $order->order_number,
                                                'grand_total' => (float)$order->grand_total,
                                                'items' => $order->items->map(fn($i) => [
                                                    'id' => $i->id,
                                                    'product_id' => $i->product_id,
                                                    'product_variant_id' => $i->product_variant_id,
                                                    'name' => $i->product_name ?? $i->name,
                                                    'variant' => $i->variant_name,
                                                    'qty' => (int)$i->quantity,
                                                    'price' => (float)$i->price
                                                ])->values()->all()
                                            ]))"
                                                    class="p-1.5 bg-white border border-gray-300 hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 text-gray-700 rounded-lg transition-colors cursor-pointer" title="Proses Retur / Tukar Barang">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

        <!-- ============================================ -->
        <!-- PAGE: Pesanan Dipesan (Barang Ambil Nanti)   -->
        <!-- ============================================ -->
        <div x-show="activePage === 'reserved'" x-cloak wire:key="pos-page-reserved" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50/50 overflow-hidden font-sans">
            <!-- Header & Filter Bar -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <button @click="activePage = 'kasir'" class="p-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Kembali ke Kasir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-950 flex items-center gap-2">
                            <span>Pesanan Dipesan (Barang Ambil Nanti)</span>
                            <span class="px-2.5 py-0.5 text-xs font-bold bg-blue-100 text-blue-800 rounded-full">{{ $totalReservedCount ?? 0 }} Pesanan</span>
                        </h1>
                        <p class="text-xs text-gray-500 font-medium">Pembayaran sudah tercatat & stok terpotong, menunggu pengambilan oleh pelanggan.</p>
                    </div>
                </div>

                <!-- Search & Status Filter Chips -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Search Bar -->
                    <div class="relative min-w-[220px]">
                        <input type="text" 
                               wire:model.live.debounce.300ms="reservedSearch" 
                               placeholder="Cari nama, WA, #order..." 
                               class="w-full pl-8 pr-7 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        @if(!empty($reservedSearch))
                            <button wire:click="$set('reservedSearch', '')" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        @endif
                    </div>

                    <!-- Filter Status -->
                    <div class="inline-flex bg-gray-100 p-1 rounded-lg border border-gray-200 text-xs font-semibold">
                        <button type="button" 
                                wire:click="$set('reservedFilterStatus', 'all')" 
                                wire:loading.attr="disabled"
                                class="px-3 py-1 rounded-md transition-all cursor-pointer {{ $reservedFilterStatus === 'all' ? 'bg-white text-blue-900 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900' }}">
                            Semua ({{ $totalReservedCount ?? 0 }})
                        </button>
                        <button type="button" 
                                wire:click="$set('reservedFilterStatus', 'today')" 
                                wire:loading.attr="disabled"
                                class="px-3 py-1 rounded-md transition-all cursor-pointer flex items-center gap-1.5 {{ $reservedFilterStatus === 'today' ? 'bg-amber-500 text-white shadow-xs font-bold' : (($todayCount ?? 0) > 0 ? 'text-amber-700 hover:bg-amber-50 font-bold' : 'text-gray-600 hover:text-gray-900') }}">
                            @if(($todayCount ?? 0) > 0)
                                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                            @endif
                            Hari Ini ({{ $todayCount ?? 0 }})
                        </button>
                        <button type="button" 
                                wire:click="$set('reservedFilterStatus', 'upcoming')" 
                                wire:loading.attr="disabled"
                                class="px-3 py-1 rounded-md transition-all cursor-pointer {{ $reservedFilterStatus === 'upcoming' ? 'bg-white text-blue-900 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900' }}">
                            Mendatang ({{ $upcomingCount ?? 0 }})
                        </button>
                        <button type="button" 
                                wire:click="$set('reservedFilterStatus', 'overdue')" 
                                wire:loading.attr="disabled"
                                class="px-3 py-1 rounded-md transition-all cursor-pointer flex items-center gap-1.5 {{ $reservedFilterStatus === 'overdue' ? 'bg-rose-600 text-white shadow-xs font-bold' : (($overdueCount ?? 0) > 0 ? 'text-rose-600 hover:bg-rose-50 font-bold' : 'text-gray-600 hover:text-gray-900') }}">
                            @if(($overdueCount ?? 0) > 0)
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                            @endif
                            Jatuh Tempo ({{ $overdueCount ?? 0 }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6">
                <!-- Loading State Indicator -->
                <div wire:loading wire:target="reservedFilterStatus, reservedSearch, resetReservedFilters" class="w-full py-16 flex flex-col items-center justify-center text-gray-500">
                    <div class="inline-block w-8 h-8 border-3 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                    <p class="text-xs font-semibold text-gray-600">Memuat pesanan dipesan...</p>
                </div>

                <div wire:loading.remove wire:target="reservedFilterStatus, reservedSearch, resetReservedFilters">
                @if(empty($reservedOrders) || count($reservedOrders) === 0)
                    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-xs">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-1">Tidak Ada Pesanan Dipesan</h3>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto">
                            @if(!empty($reservedSearch) || $reservedFilterStatus !== 'all')
                                Tidak ditemukan pesanan dipesan dengan kata kunci / filter tersebut. 
                                <button wire:click="resetReservedFilters()" class="text-blue-600 underline font-semibold ml-1 cursor-pointer">Reset Filter</button>
                            @else
                                Saat ini belum ada pesanan yang tersimpan dengan status Dipesan.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($reservedOrders as $rOrder)
                            @php
                                $isToday   = $rOrder->pickup_date && $rOrder->pickup_date->isToday();
                                $isOverdue = $rOrder->pickup_date && $rOrder->pickup_date->isPast() && !$isToday;
                            @endphp
                            <div wire:key="reserved-order-{{ $rOrder->id }}" class="bg-white rounded-2xl border {{ $isOverdue ? 'border-rose-300 ring-2 ring-rose-200/80 shadow-rose-100/50 bg-rose-50/10' : ($isToday ? 'border-amber-300 ring-2 ring-amber-200/80 bg-amber-50/10' : 'border-blue-200') }} p-5 shadow-xs flex flex-col justify-between hover:shadow-md transition-all">
                                <div>
                                    @if($isOverdue)
                                        <div class="mb-3 p-2 bg-rose-50 border border-rose-200 text-rose-900 rounded-xl text-[11px] font-bold flex items-center justify-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                                            <span>⚠️ Pesanan Melewati Tanggal Janji Ambil!</span>
                                        </div>
                                    @elseif($isToday)
                                        <div class="mb-3 p-2 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-[11px] font-bold flex items-center justify-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                            <span>🔔 Jadwal Pengambilan Barang Hari Ini!</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                        <div>
                                            <span class="font-mono font-bold text-sm text-gray-900">#{{ $rOrder->order_number }}</span>
                                            <div class="text-[11px] text-gray-500 font-medium mt-0.5">{{ $rOrder->created_at ? $rOrder->created_at->format('d M Y, H:i') : '-' }}</div>
                                        </div>
                                        @if($isOverdue)
                                            <span class="px-2.5 py-1 text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 rounded-full flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Jatuh Tempo
                                            </span>
                                        @elseif($isToday)
                                            <span class="px-2.5 py-1 text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 rounded-full flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                Hari Ini
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-bold bg-blue-100 text-blue-800 rounded-full">
                                                Dipesan
                                            </span>
                                        @endif
                                    </div>

                                    <div class="py-3 space-y-2 text-xs">
                                        <div class="flex justify-between items-start text-gray-700">
                                            <span class="text-gray-500">Pelanggan:</span>
                                            <div class="text-right">
                                                <div class="font-bold text-gray-900">{{ $rOrder->customer_name ?: 'Pelanggan POS' }}</div>
                                                @if($rOrder->customer_phone)
                                                    <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">📞 {{ $rOrder->customer_phone }}</div>
                                                @else
                                                    <div class="text-[11px] text-gray-400 font-normal italic mt-0.5">(Tanpa No. HP)</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center text-gray-700">
                                            <span class="text-gray-500">Tgl. Transaksi:</span>
                                            <span class="font-semibold text-gray-900">{{ $rOrder->created_at ? $rOrder->created_at->format('d M Y, H:i') : '-' }}</span>
                                        </div>

                                        @if($rOrder->pickup_date)
                                            <div class="flex justify-between items-center p-2 rounded-lg border text-xs {{ $isOverdue ? 'bg-rose-50 border-rose-200 text-rose-900 font-bold' : ($isToday ? 'bg-amber-50 border-amber-200 text-amber-900 font-bold' : 'bg-blue-50 border-blue-100 text-blue-900 font-semibold') }}">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4 {{ $isOverdue ? 'text-rose-600' : ($isToday ? 'text-amber-600' : 'text-blue-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    Perkiraan Ambil:
                                                </span>
                                                <span>
                                                    {{ $rOrder->pickup_date->format('d M Y') }}
                                                    @if($isOverdue)
                                                        <span class="text-[10px] bg-rose-200 text-rose-900 px-1.5 py-0.5 rounded font-bold ml-1">Terlewat</span>
                                                    @elseif($isToday)
                                                        <span class="text-[10px] bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded font-bold ml-1">Hari Ini</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endif

                                        <div class="flex justify-between items-start text-gray-700">
                                            <span class="text-gray-500">Metode Bayar:</span>
                                            <span class="font-semibold text-right text-gray-900 max-w-[200px] break-words">{{ $rOrder->formatted_payment_method }}</span>
                                        </div>

                                        <div class="flex justify-between text-gray-900 font-bold pt-2 border-t border-gray-100 text-sm">
                                            <span>Total Pembayaran:</span>
                                            <span class="text-emerald-700">Rp {{ number_format($rOrder->grand_total, 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    <!-- Detail Barang -->
                                    <div class="mt-2 p-2.5 bg-gray-50 rounded-xl text-[11px] text-gray-600 space-y-1">
                                        <div class="font-semibold text-gray-700 mb-1">Rincian Barang:</div>
                                        @foreach($rOrder->items as $rItem)
                                            <div class="flex justify-between">
                                                <span>{{ $rItem->name ?: ($rItem->product->name ?? 'Produk') }} ({{ $rItem->quantity }}x)</span>
                                                <span class="font-medium">Rp {{ number_format($rItem->total ?? ($rItem->price * $rItem->quantity), 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center gap-2">
                                    <button type="button" 
                                            @click="confirmPickupOrder = { id: {{ $rOrder->id }}, number: '{{ $rOrder->order_number }}', name: '{{ addslashes($rOrder->customer_name ?: 'Pelanggan POS') }}' }; showPickupConfirmModal = true" 
                                            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Tandai Sudah Diambil</span>
                                    </button>
                                    <button type="button" 
                                            @click="showReceiptPreviewModal = true; isProcessing = true; previewReceiptData = null; $wire.reprintReceipt({{ $rOrder->id }})" 
                                            class="py-2.5 px-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium text-xs rounded-xl shadow-xs transition-colors cursor-pointer" 
                                            title="Cetak Struk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h10z"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- PAGE: Riwayat Retur (Filament Native Table)  -->
        <!-- ============================================ -->
        <div x-show="activePage === 'returns'" x-cloak wire:key="pos-page-returns" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50/50 overflow-y-auto font-sans">
            <div class="p-4 md:p-6 max-w-7xl w-full mx-auto space-y-5">
                
                <!-- Header Title Bar -->
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button @click="activePage = 'kasir'" class="p-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Kembali ke Kasir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight text-gray-950">
                                Riwayat Retur & Penukaran Barang
                            </h1>
                            <p class="text-xs text-gray-500 font-medium">Manajemen retur, penukaran barang, dan pengembalian uang kasir.</p>
                        </div>
                    </div>
                </div>
                <!-- Ringkasan KPI Retur -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Transaksi Retur</p>
                        <p class="text-2xl font-bold text-gray-950 mt-1">{{ count($sessionReturns ?? []) }} <span class="text-sm font-normal text-gray-400">transaksi</span></p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Refund Kas</p>
                        <p class="text-2xl font-bold text-rose-600 mt-1">Rp {{ number_format(abs(collect($sessionReturns ?? [])->where('net_amount', '<', 0)->sum('net_amount')), 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Tambah Bayar (Tukar)</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format(collect($sessionReturns ?? [])->where('net_amount', '>', 0)->sum('net_amount'), 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- UNIFIED TABLE CARD (Filament Native Card with Table) -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs relative">
                    <!-- Table Header Toolbar (Search + Filter Popover) -->
                    <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between gap-4 bg-white rounded-t-xl">
                        <!-- SISI KIRI: Search Input -->
                        <div class="relative min-w-[220px] max-w-xs flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="returnSearch" wire:key="return-search-input" placeholder="Cari no. retur, nota, kasir..."
                                   class="w-full pl-9 pr-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs">
                        </div>

                        <!-- SISI KANAN: Filter Action Button & Popover Modal -->
                        <div class="relative flex-shrink-0" x-data="{ showFilterPopover: false }" wire:ignore.self wire:key="return-filter-container">
                            @php
                                $activeReturnFilterCount = ($returnDateFilter !== 'shift' ? 1 : 0) +
                                                           ($returnTypeFilter !== 'all' ? 1 : 0);
                            @endphp

                            <!-- Filter Action Trigger Button -->
                            <button type="button" @click="showFilterPopover = !showFilterPopover"
                                    class="px-3.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-xs transition duration-150 flex items-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Filter Tabel Retur">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                <span>Filter</span>
                                @if($activeReturnFilterCount > 0)
                                <span class="w-4 h-4 bg-emerald-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $activeReturnFilterCount }}</span>
                                @endif
                            </button>

                            <!-- Filter Popover Card -->
                            <div x-show="showFilterPopover"
                                 x-cloak
                                 @click.away="showFilterPopover = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-4 space-y-4">

                                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                                    <span class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        Filter Retur
                                    </span>
                                    @if($activeReturnFilterCount > 0)
                                    <button type="button"
                                            wire:click="resetReturnFilters"
                                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline cursor-pointer">
                                        Reset filter
                                    </button>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Periode Retur</label>
                                        <select wire:key="filter-return-date-select" wire:model.live="returnDateFilter"
                                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                            <option value="shift">Shift Hari Ini</option>
                                            <option value="today">Hari Ini</option>
                                            <option value="yesterday">Kemarin</option>
                                            <option value="7days">7 Hari Terakhir</option>
                                            <option value="30days">30 Hari Terakhir</option>
                                            <option value="all">Semua Riwayat</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Tipe Retur</label>
                                        <select wire:key="filter-return-type-select" wire:model.live="returnTypeFilter"
                                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                            <option value="all">Semua Tipe</option>
                                            @foreach($this->availableReturnTypes as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-gray-100 flex justify-end">
                                    <button type="button" @click="showFilterPopover = false" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg transition cursor-pointer">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(count($sessionReturns ?? []) === 0)
                    <div class="flex flex-col items-center justify-center text-gray-400 py-16">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-900">Belum ada retur pada shift ini</p>
                        <p class="text-xs text-gray-500 mt-1">Retur dan penukaran barang yang diproses akan muncul di sini.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto rounded-b-xl">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider select-none">
                                <tr>
                                    <th class="py-3 px-4 cursor-pointer hover:bg-gray-100 transition" @click="sortReturnClient('created_at')">
                                        <div class="flex items-center gap-1.5">
                                            <span>No. Retur & Waktu</span>
                                            <span class="text-xs font-bold" :class="returnSortCol === 'created_at' ? 'text-emerald-600' : 'text-gray-400'" x-text="returnSortCol === 'created_at' ? (returnSortDir === 'desc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4">Nota Asli</th>
                                    <th class="py-3 px-4 text-center cursor-pointer hover:bg-gray-100 transition" @click="sortReturnClient('type')">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span>Tipe Transaksi</span>
                                            <span class="text-xs font-bold" :class="returnSortCol === 'type' ? 'text-emerald-600' : 'text-gray-400'" x-text="returnSortCol === 'type' ? (returnSortDir === 'asc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4">Rincian Barang</th>
                                    <th class="py-3 px-4 text-right cursor-pointer hover:bg-gray-100 transition" @click="sortReturnClient('net_amount')">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <span>Selisih Nominal</span>
                                            <span class="text-xs font-bold" :class="returnSortCol === 'net_amount' ? 'text-emerald-600' : 'text-gray-400'" x-text="returnSortCol === 'net_amount' ? (returnSortDir === 'desc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 cursor-pointer hover:bg-gray-100 transition" @click="sortReturnClient('cashier')">
                                        <div class="flex items-center gap-1.5">
                                            <span>Petugas</span>
                                            <span class="text-xs font-bold" :class="returnSortCol === 'cashier' ? 'text-emerald-600' : 'text-gray-400'" x-text="returnSortCol === 'cashier' ? (returnSortDir === 'asc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="posReturnTableBody" class="divide-y divide-gray-100 text-xs">
                                @foreach($sessionReturns as $ret)
                                <tr wire:key="return-row-{{ $ret->id }}"
                                    data-return-row
                                    data-sort-created_at="{{ $ret->created_at->timestamp }}"
                                    data-sort-type="{{ $ret->type }}"
                                    data-sort-net_amount="{{ $ret->net_amount }}"
                                    data-sort-cashier="{{ strtolower($ret->cashier->name ?? '') }}"
                                    class="hover:bg-gray-50/80 transition-colors">
                                    <!-- No. Retur & Waktu -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold font-mono text-gray-900 text-xs">#{{ $ret->return_number }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $ret->created_at->format('H:i') }} WIB</div>
                                    </td>

                                    <!-- Nota Asli -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="font-bold font-mono text-gray-800">#{{ $ret->order->order_number ?? '-' }}</span>
                                    </td>

                                    <!-- Tipe Transaksi -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-medium {{ $ret->type === 'exchange' ? 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20' : 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20' }}">
                                            {{ $ret->type === 'exchange' ? 'Tukar Barang' : 'Refund Kas' }}
                                        </span>
                                    </td>

                                    <!-- Rincian Barang -->
                                    <td class="py-3 px-4 max-w-md">
                                        <div class="space-y-1">
                                            <div class="text-[11px]">
                                                <span class="font-semibold text-rose-600">Dikembalikan:</span> 
                                                @foreach($ret->returnedItems as $rItem)
                                                    <span class="text-gray-800 font-medium">{{ $rItem->product->name ?? 'Produk' }}{{ $rItem->variant ? ' ('.$rItem->variant->name.')' : '' }} x{{ $rItem->quantity }}</span>@if(!$loop->last), @endif
                                                @endforeach
                                            </div>
                                            @if($ret->exchangedItems->count() > 0)
                                            <div class="text-[11px]">
                                                <span class="font-semibold text-amber-700">Pengganti:</span> 
                                                @foreach($ret->exchangedItems as $eItem)
                                                    <span class="text-gray-800 font-medium">{{ $eItem->product->name ?? 'Produk' }}{{ $eItem->variant ? ' ('.$eItem->variant->name.')' : '' }} x{{ $eItem->quantity }}</span>@if(!$loop->last), @endif
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Selisih Nominal -->
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        @if($ret->net_amount > 0)
                                            <div class="font-bold text-xs text-emerald-600">+ Rp {{ number_format($ret->net_amount, 0, ',', '.') }}</div>
                                            <div class="text-[10px] text-gray-400">Tambah Bayar</div>
                                        @elseif($ret->net_amount < 0)
                                            <div class="font-bold text-xs text-rose-600">- Rp {{ number_format(abs($ret->net_amount), 0, ',', '.') }}</div>
                                            <div class="text-[10px] text-gray-400">Refund Kas</div>
                                        @else
                                            <div class="font-bold text-xs text-gray-600">Rp 0</div>
                                            <div class="text-[10px] text-gray-400">Pas</div>
                                        @endif
                                    </td>

                                    <!-- Petugas -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="text-xs font-semibold text-gray-900">{{ $ret->cashier->name ?? 'Kasir' }}</div>
                                        @if($ret->supervisor)
                                            <div class="text-[10px] text-gray-500">Spv: {{ $ret->supervisor->name }}</div>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <button type="button" @click="showReceiptPreviewModal = true; isProcessing = true; previewReceiptData = null; $wire.reprintReturnReceipt({{ $ret->id }})" :disabled="isProcessing"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h10z"/>
                                            </svg>
                                            <span>Cetak Struk</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- PAGE: Pelanggan (Filament Native Table)      -->
        <!-- ============================================ -->
        <div x-show="activePage === 'customers'"
             x-cloak
             wire:key="pos-page-customers"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="flex-1 flex flex-col h-full bg-gray-50/50 overflow-y-auto font-sans">
            <div class="p-4 md:p-6 max-w-7xl w-full mx-auto space-y-5">
                
                <!-- Header Title Bar -->
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button @click="activePage = 'kasir'" class="p-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Kembali ke Kasir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight text-gray-950">Pelanggan POS</h1>
                            <p class="text-xs text-gray-500 font-medium">Manajemen riwayat transaksi dan kartu stempel loyalty pelanggan.</p>
                        </div>
                    </div>
                </div>
                <!-- Ringkasan KPI Pelanggan -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Pelanggan POS</p>
                        <p class="text-2xl font-bold text-gray-950 mt-1">{{ count($sessionCustomers) }} <span class="text-sm font-normal text-gray-400">orang</span></p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Belanja Pelanggan</p>
                        <p class="text-2xl font-bold text-gray-950 mt-1">Rp {{ number_format($sessionCustomers->sum('total_spent'), 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Kunjungan Berulang</p>
                        <p class="text-2xl font-bold text-gray-950 mt-1">{{ $sessionCustomers->filter(fn($c) => ($c->total_orders ?? 1) > 1)->count() }} <span class="text-sm font-normal text-gray-400">pelanggan</span></p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Kartu Stempel Ready</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $sessionCustomers->filter(fn($c) => ($c->stamp_count ?? 0) >= 3 || ($c->completed_cards_count ?? 0) > 0)->count() }} <span class="text-sm font-normal text-gray-400">voucher</span></p>
                    </div>
                </div>

                <!-- UNIFIED TABLE CARD (Filament Native Card with Search Toolbar) -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs relative">
                    <!-- Table Header Toolbar -->
                    <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between gap-4 bg-white rounded-t-xl">
                        <!-- SISI KIRI: Search Input -->
                        <div class="relative min-w-[220px] max-w-xs flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text"
                                   wire:model.live.debounce.300ms="customerSearch"
                                   wire:key="customer-search-input"
                                   placeholder="Cari nama atau No. HP..."
                                   class="w-full pl-9 pr-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs">
                        </div>

                        <!-- SISI KANAN: Filter Action Button & Popover Modal -->
                        <div class="relative flex-shrink-0" x-data="{ showFilterPopover: false }" wire:ignore.self wire:key="customer-filter-container">
                            @php
                                $activeCustomerFilterCount = ($customerDateFilter !== 'all' ? 1 : 0) + ($customerStampFilter !== 'all' ? 1 : 0);
                            @endphp

                            <!-- Filter Action Trigger Button -->
                            <button type="button" @click="showFilterPopover = !showFilterPopover"
                                    class="px-3.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-xs transition duration-150 flex items-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Filter Tabel Pelanggan">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                <span>Filter</span>
                                @if($activeCustomerFilterCount > 0)
                                <span class="w-4 h-4 bg-emerald-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $activeCustomerFilterCount }}</span>
                                @endif
                            </button>

                            <!-- Filter Popover Card -->
                            <div x-show="showFilterPopover"
                                 x-cloak
                                 @click.away="showFilterPopover = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-4 space-y-4">

                                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                                    <span class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        Filter Pelanggan
                                    </span>
                                    @if($activeCustomerFilterCount > 0)
                                    <button type="button"
                                            wire:click="resetCustomerFilters"
                                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline cursor-pointer">
                                        Reset filter
                                    </button>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Status Stempel & Hadiah</label>
                                        <select wire:key="filter-customer-stamp-select" wire:model.live="customerStampFilter"
                                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                            <option value="all">Semua Pelanggan</option>
                                            <option value="ready_gift">Berhak Hadiah (Stempel ≥ 3 Cap)</option>
                                            <option value="completed_card">Memiliki Kartu Selesai (≥ 1 Kartu)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Urutkan Berdasarkan</label>
                                        <select wire:key="filter-customer-sort-select"
                                                @change="$wire.set('customerSortColumn', $event.target.value.split('_')[0]); $wire.set('customerSortDirection', $event.target.value.endsWith('asc') ? 'asc' : 'desc'); customerSortCol = $event.target.value.split('_')[0]; customerSortDir = $event.target.value.endsWith('asc') ? 'asc' : 'desc'; sortCustomersClient($event.target.value.split('_')[0]);"
                                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                            <option value="total_spent_desc" {{ $customerSortColumn === 'total_spent' && $customerSortDirection === 'desc' ? 'selected' : '' }}>Total Belanja: Terbanyak → Terkecil (↑)</option>
                                            <option value="total_spent_asc" {{ $customerSortColumn === 'total_spent' && $customerSortDirection === 'asc' ? 'selected' : '' }}>Total Belanja: Terkecil → Terbanyak (↓)</option>
                                            <option value="stamp_count_desc" {{ $customerSortColumn === 'stamp_count' && $customerSortDirection === 'desc' ? 'selected' : '' }}>Stempel Aktif: Terbanyak → Terkecil (↑)</option>
                                            <option value="points_desc" {{ $customerSortColumn === 'points' && $customerSortDirection === 'desc' ? 'selected' : '' }}>Saldo Poin: Terbanyak → Terkecil (↑)</option>
                                            <option value="completed_cards_desc" {{ $customerSortColumn === 'completed_cards' && $customerSortDirection === 'desc' ? 'selected' : '' }}>Kartu Selesai: Terbanyak → Terkecil (↑)</option>
                                            <option value="name_asc" {{ $customerSortColumn === 'name' && $customerSortDirection === 'asc' ? 'selected' : '' }}>Nama Pelanggan: A → Z (↑)</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Min. Belanja (Rp)</label>
                                            <input type="number" wire:model.live.debounce.300ms="customerMinSpend" placeholder="0" class="w-full px-2.5 py-1 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Max. Belanja (Rp)</label>
                                            <input type="number" wire:model.live.debounce.300ms="customerMaxSpend" placeholder="Tanpa batas" class="w-full px-2.5 py-1 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Min. Cap Stempel</label>
                                            <input type="number" wire:model.live.debounce.300ms="customerMinStamps" placeholder="0" class="w-full px-2.5 py-1 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Min. Saldo Poin</label>
                                            <input type="number" wire:model.live.debounce.300ms="customerMinPoints" placeholder="0" class="w-full px-2.5 py-1 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Periode Kunjungan</label>
                                        <select wire:key="filter-customer-date-select" wire:model.live="customerDateFilter"
                                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                            <option value="all">Semua Pelanggan (Default)</option>
                                            <option value="shift">Shift Hari Ini</option>
                                            <option value="today">Hari Ini</option>
                                            <option value="yesterday">Kemarin</option>
                                            <option value="7days">7 Hari Terakhir</option>
                                            <option value="30days">30 Hari Terakhir</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-gray-100 flex justify-end">
                                    <button type="button" @click="showFilterPopover = false" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg transition cursor-pointer">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(count($sessionCustomers) === 0)
                    <div class="flex flex-col items-center justify-center text-gray-400 py-16">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-900">Belum ada pelanggan tercatat</p>
                        <p class="text-xs text-gray-500 mt-1">Pilih atau daftarkan pelanggan saat checkout kasir.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto rounded-b-xl">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider select-none">
                                <tr>
                                    <th class="py-3 px-4 cursor-pointer hover:bg-gray-100 transition" @click="sortCustomersClient('name')">
                                        <div class="flex items-center gap-1.5">
                                            <span>Nama Pelanggan</span>
                                            <span class="text-xs font-bold" :class="customerSortCol === 'name' ? 'text-emerald-600' : 'text-gray-400'" x-text="customerSortCol === 'name' ? (customerSortDir === 'asc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4">No. Telepon / HP</th>
                                    <th class="py-3 px-4 text-center cursor-pointer hover:bg-gray-100 transition" @click="sortCustomersClient('stamp_count')" title="Klik untuk mengurutkan stempel">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span>Stempel Aktif</span>
                                            <span class="text-xs font-bold" :class="customerSortCol === 'stamp_count' ? 'text-emerald-600' : 'text-gray-400'" x-text="customerSortCol === 'stamp_count' ? (customerSortDir === 'desc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 text-center cursor-pointer hover:bg-gray-100 transition" @click="sortCustomersClient('points')" title="Klik untuk mengurutkan poin">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span>Saldo Poin</span>
                                            <span class="text-xs font-bold" :class="customerSortCol === 'points' ? 'text-emerald-600' : 'text-gray-400'" x-text="customerSortCol === 'points' ? (customerSortDir === 'desc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 text-center cursor-pointer hover:bg-gray-100 transition" @click="sortCustomersClient('completed_cards')" title="Klik untuk mengurutkan kartu selesai">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span>Kartu Selesai (12 Cap)</span>
                                            <span class="text-xs font-bold" :class="customerSortCol === 'completed_cards' ? 'text-emerald-600' : 'text-gray-400'" x-text="customerSortCol === 'completed_cards' ? (customerSortDir === 'desc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 text-right cursor-pointer hover:bg-gray-100 transition" @click="sortCustomersClient('total_spent')" title="Klik untuk mengurutkan total belanja">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <span>Total Belanja</span>
                                            <span class="text-xs font-bold" :class="customerSortCol === 'total_spent' ? 'text-emerald-600' : 'text-gray-400'" x-text="customerSortCol === 'total_spent' ? (customerSortDir === 'desc' ? '↑' : '↓') : '↕'"></span>
                                        </div>
                                    </th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="posCustomerTableBody" class="divide-y divide-gray-100 text-xs">
                                @foreach($sessionCustomers as $idx => $customer)
                                <tr wire:key="cust-row-{{ $idx }}"
                                    data-cust-row
                                    data-sort-name="{{ strtolower($customer->customer_name) }}"
                                    data-sort-stamp_count="{{ $customer->stamp_count ?? 0 }}"
                                    data-sort-points="{{ $customer->loyalty_points ?? 0 }}"
                                    data-sort-completed_cards="{{ $customer->completed_cards_count ?? 0 }}"
                                    data-sort-total_spent="{{ $customer->total_spent ?? 0 }}"
                                    x-show="!$wire.customerSearch || '{{ strtolower($customer->customer_name) }}'.includes(($wire.customerSearch || '').toLowerCase()) || '{{ $customer->customer_phone }}'.includes($wire.customerSearch || '')"
                                    class="hover:bg-gray-50/80 transition-colors">

                                    <!-- Nama Pelanggan -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center flex-shrink-0 border border-emerald-200">
                                                {{ strtoupper(substr($customer->customer_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-xs hover:text-emerald-600 cursor-pointer" @click="openCustomerDetailModal({{ \Illuminate\Support\Js::from($customer) }})">{{ $customer->customer_name }}</div>
                                                <div class="text-[10px] text-gray-400 font-medium">{{ $customer->total_orders ?? 1 }}x Transaksi</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- No. Telepon -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if($customer->customer_phone)
                                            <span class="font-mono text-gray-700 text-xs">{{ $customer->customer_phone }}</span>
                                        @else
                                            <span class="text-gray-400 italic text-[11px]">Tanpa No. HP</span>
                                        @endif
                                    </td>

                                    <!-- Stempel Aktif (12 Cap) -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @php
                                            $stamps = $customer->active_stamps ?? (($customer->stamp_count ?? 0) % 12);
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>{{ $stamps }} / 12 Cap</span>
                                        </span>
                                    </td>

                                    <!-- Saldo Poin -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap font-bold text-gray-800">
                                        {{ number_format($customer->loyalty_points ?? 0, 0, ',', '.') }}
                                    </td>

                                    <!-- Kartu Selesai (12 Cap) -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @php
                                            $completed = $customer->completed_cards_count ?? floor(($customer->stamp_count ?? 0) / 12);
                                        @endphp
                                        @if($completed > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                <span>{{ $completed }} Kartu</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-normal text-gray-400 bg-gray-50">
                                                0 Kartu
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Total Belanja -->
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="font-bold text-xs text-gray-900">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</div>
                                    </td>

                                    <!-- Aksi (Detail Pelanggan) -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button"
                                                    @click="openCustomerDetailModal({{ \Illuminate\Support\Js::from($customer) }})"
                                                    class="px-2.5 py-1 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-semibold text-[11px] rounded-lg shadow-xs transition duration-150 cursor-pointer flex items-center gap-1" title="Lihat Rincian Biodata & Log Transaksi">
                                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Detail</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- PAGE: Rekap Kas (Filament Native Style)      -->
        <!-- ============================================ -->
        <div x-show="activePage === 'cashsummary'" x-cloak wire:key="pos-page-cashsummary" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50/50 overflow-y-auto font-sans">
            <div class="p-4 md:p-6 max-w-7xl w-full mx-auto space-y-5">
                
                <!-- Header Title Bar -->
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button @click="activePage = 'kasir'" class="p-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Kembali ke Kasir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight text-gray-950">Rekap Kas Shift</h1>
                            <p class="text-xs text-gray-500 font-medium">Shift dibuka sejak {{ $sessionStats['opened_at'] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <!-- Ringkasan KPI - 3 Stat Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Stat 1: Estimasi Kas di Laci (Featured) -->
                    <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Estimasi Kas di Laci</p>
                        <p class="text-2xl font-bold text-emerald-950 mt-1">Rp {{ number_format($sessionStats['expected_cash'] ?? 0, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-emerald-600 mt-0.5">Modal + Tunai + Kas Masuk - Keluar</p>
                    </div>

                    <!-- Stat 2: Total Omzet -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Omzet Penjualan</p>
                        <p class="text-2xl font-bold text-gray-950 mt-1">Rp {{ number_format($sessionStats['total_sales'] ?? 0, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Tunai + Non-Tunai shift ini</p>
                    </div>

                    <!-- Stat 3: Total Nota -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Nota Selesai</p>
                        <p class="text-2xl font-bold text-gray-950 mt-1">{{ $sessionStats['total_trx'] ?? 0 }} <span class="text-sm font-normal text-gray-400">nota</span></p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Berhasil diproses</p>
                    </div>
                </div>

                <!-- 2-COLUMN GRID -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- KOLOM KIRI: Rincian Arus Kas -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-gray-200">
                                <div class="text-sm font-bold text-gray-900">Rincian Arus Kas Shift</div>
                                <div class="text-xs text-gray-500">Komposisi penerimaan & pengeluaran kasir</div>
                            </div>

                            <div class="divide-y divide-gray-100">
                                <div class="flex justify-between items-center px-5 py-3 text-xs">
                                    <span class="text-gray-600">Modal Awal Shift</span>
                                    <span class="font-semibold text-gray-900">Rp {{ number_format($sessionStats['opening_cash'] ?? 0, 0, ',', '.') }}</span>
                                </div>

                                <div class="flex justify-between items-center px-5 py-3 text-xs">
                                    <span class="text-gray-600">Penjualan Tunai</span>
                                    <span class="font-semibold text-gray-900">+ Rp {{ number_format($sessionStats['cash_sales'] ?? 0, 0, ',', '.') }}</span>
                                </div>

                                @if(!empty($sessionStats['non_cash_breakdown']))
                                    @foreach($sessionStats['non_cash_breakdown'] as $ncItem)
                                    <div class="flex justify-between items-center px-5 py-3 text-xs pl-8 bg-gray-50/50">
                                        <span class="text-gray-500">&bull; Non-Tunai: {{ $ncItem['short_label'] }}</span>
                                        <span class="font-medium text-gray-700">+ Rp {{ number_format($ncItem['amount'], 0, ',', '.') }}</span>
                                    </div>
                                    @endforeach
                                @else
                                <div class="flex justify-between items-center px-5 py-3 text-xs pl-8 bg-gray-50/50">
                                    <span class="text-gray-500">&bull; Penjualan Non-Tunai</span>
                                    <span class="font-medium text-gray-700">+ Rp {{ number_format($sessionStats['non_cash_sales'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                                @endif

                                <div class="flex justify-between items-center px-5 py-3 text-xs">
                                    <span class="text-gray-600">Kas Masuk Operasional (In)</span>
                                    <span class="font-semibold text-emerald-600">+ Rp {{ number_format($sessionStats['petty_cash_in'] ?? 0, 0, ',', '.') }}</span>
                                </div>

                                @if(($sessionStats['exchange_in'] ?? 0) > 0)
                                <div class="flex justify-between items-center px-5 py-3 text-xs">
                                    <span class="text-gray-600">Tambah Bayar Tukar Barang</span>
                                    <span class="font-semibold text-emerald-600">+ Rp {{ number_format($sessionStats['exchange_in'], 0, ',', '.') }}</span>
                                </div>
                                @endif

                                <div class="flex justify-between items-center px-5 py-3 text-xs">
                                    <span class="text-gray-600">Kas Keluar Operasional (Out)</span>
                                    <span class="font-semibold text-rose-600">- Rp {{ number_format($sessionStats['petty_cash_out'] ?? 0, 0, ',', '.') }}</span>
                                </div>

                                @if(($sessionStats['void_refund_out'] ?? 0) > 0)
                                <div class="flex justify-between items-center px-5 py-3 text-xs">
                                    <span class="text-gray-600">Pengembalian Uang Void/Retur Tunai</span>
                                    <span class="font-semibold text-rose-600">- Rp {{ number_format($sessionStats['void_refund_out'], 0, ',', '.') }}</span>
                                </div>
                                @endif

                                <div class="flex justify-between items-center px-5 py-3.5 text-xs bg-emerald-50/60 font-bold">
                                    <span class="text-emerald-900">Total Estimasi Kas Laci</span>
                                    <span class="text-emerald-900 text-sm">Rp {{ number_format($sessionStats['expected_cash'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Tutup Shift -->
                        <button @click="activePage = 'kasir'; $nextTick(() => showCloseSession = true)"
                                class="w-full py-3 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition duration-150 flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Tutup Shift Kasir Sekarang</span>
                        </button>
                    </div>

                    <!-- KOLOM KANAN: Tabel Log Kas / Petty Cash -->
                    <div class="lg:col-span-7">
                        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden flex flex-col h-full">
                            <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between bg-white">
                                <div>
                                    <div class="text-sm font-bold text-gray-900">Riwayat Arus Kas Masuk / Keluar</div>
                                    <div class="text-xs text-gray-500">Catatan transaksi kasir & petty cash operasional</div>
                                </div>
                                <div>
                                    <button type="button" @click="showPettyCashModal = true" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition duration-150 flex items-center gap-1.5 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Catat Kas</span>
                                    </button>
                                </div>
                            </div>

                            @if(count($sessionPettyCash ?? []) === 0)
                            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 py-12">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">Belum ada kas masuk/keluar</p>
                                <p class="text-xs text-gray-500 mt-1">Gunakan tombol "Catat Kas" untuk mencatat uang operasional.</p>
                            </div>
                            @else
                            <div class="overflow-x-auto flex-1">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                        <tr>
                                            <th class="py-3 px-4">Waktu</th>
                                            <th class="py-3 px-4 text-center">Tipe</th>
                                            <th class="py-3 px-4">Keterangan</th>
                                            <th class="py-3 px-4 text-right">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-xs">
                                        @foreach($sessionPettyCash as $cashLog)
                                        <tr wire:key="cashlog-row-{{ $cashLog->id }}" class="hover:bg-gray-50/80 transition-colors">
                                            <td class="py-3 px-4 whitespace-nowrap font-mono text-gray-500">
                                                {{ $cashLog->created_at->format('H:i') }} WIB
                                            </td>
                                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                                @if($cashLog->type === 'out')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20">KAS KELUAR</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">KAS MASUK</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="font-semibold text-gray-900">{{ $cashLog->description }}</div>
                                            </td>
                                            <td class="py-3 px-4 text-right whitespace-nowrap font-bold">
                                                <span class="{{ $cashLog->type === 'out' ? 'text-rose-600' : 'text-emerald-600' }}">
                                                    {{ $cashLog->type === 'out' ? '-' : '+' }} Rp {{ number_format($cashLog->amount, 0, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Modal Preview Struk & Sukses Pembayaran - Filament Native Style -->
    <div x-show="showReceiptPreviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/50 backdrop-blur-xs font-sans" x-transition.opacity>
        <div class="bg-white w-full max-w-md rounded-xl overflow-hidden shadow-2xl border border-gray-200 flex flex-col max-h-[90vh]" @click.away="!isProcessing && (showReceiptPreviewModal = false)">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                <div>
                    <template x-if="isProcessing">
                        <div class="animate-pulse flex flex-col gap-2">
                            <div class="h-5 bg-gray-200 rounded w-32"></div>
                            <div class="h-3 bg-gray-200 rounded w-24"></div>
                        </div>
                    </template>
                    <template x-if="!isProcessing">
                        <div>
                            <h3 class="font-bold text-base text-gray-950" x-text="previewReceiptData ? (previewReceiptData.title || 'Pratinjau Struk Cetak') : 'Pratinjau Struk Cetak'"></h3>
                            <p class="text-xs text-gray-500 font-medium" x-text="previewReceiptData ? previewReceiptData.orderNumber : ''"></p>
                        </div>
                    </template>
                </div>
                <button @click="showReceiptPreviewModal = false" :disabled="isProcessing" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition" :class="isProcessing ? 'opacity-50 cursor-not-allowed' : ''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Content Area -->
            <div class="p-6 space-y-4 overflow-y-auto flex-1 bg-gray-50/50">
                <template x-if="isProcessing">
                    <div class="space-y-4">
                        <div class="flex flex-col items-center justify-center py-6">
                            <svg class="animate-spin h-10 w-10 text-emerald-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <h3 class="text-gray-900 font-bold text-lg mb-1">Memproses Transaksi...</h3>
                            <p class="text-gray-500 text-sm text-center max-w-xs">Mohon tunggu, sistem sedang menyelesaikan pembayaran dan menyiapkan struk Anda.</p>
                        </div>
                        <div class="bg-gray-200 p-4 rounded-xl border border-gray-300 shadow-inner flex justify-center">
                            <div class="bg-white p-4 shadow-sm w-full max-w-[300px] animate-pulse space-y-3">
                                <div class="h-10 w-10 bg-gray-200 rounded-full mx-auto"></div>
                                <div class="h-3 bg-gray-200 rounded mx-auto w-3/4"></div>
                                <div class="h-3 bg-gray-200 rounded mx-auto w-1/2"></div>
                                <div class="border-t border-dashed border-gray-300 my-4"></div>
                                <div class="h-3 bg-gray-200 rounded w-full"></div>
                                <div class="h-3 bg-gray-200 rounded w-full"></div>
                                <div class="h-3 bg-gray-200 rounded w-5/6"></div>
                                <div class="border-t border-dashed border-gray-300 my-4"></div>
                                <div class="flex justify-between">
                                    <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                                    <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="!isProcessing">
                    <div class="space-y-4">
                        <!-- High Contrast Change Display (If Cash Change > 0) -->
                        <template x-if="previewReceiptData && previewReceiptData.cashChange > 0">
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Uang Kembalian Pelanggan</div>
                                <div class="text-2xl font-black text-emerald-800 mt-0.5">
                                    Rp <span x-text="formatMoney(previewReceiptData.cashChange)"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Tab Switcher for Orders with Returns -->
                        <template x-if="previewReceiptData && previewReceiptData.has_returns">
                            <div class="flex items-center gap-1.5 p-1 bg-gray-200/80 rounded-lg border border-gray-300">
                                <button type="button" @click="previewReceiptData.activeTab = 'sales'" :class="previewReceiptData.activeTab === 'sales' ? 'bg-white text-gray-950 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'" class="flex-1 py-1.5 px-3 text-xs rounded-md transition text-center cursor-pointer">
                                    Struk Penjualan Asli
                                </button>
                                <button type="button" @click="previewReceiptData.activeTab = 'return'" :class="previewReceiptData.activeTab === 'return' ? 'bg-white text-amber-900 shadow-xs font-bold ring-1 ring-amber-500/30' : 'text-amber-800 hover:text-amber-950 font-medium'" class="flex-1 py-1.5 px-3 text-xs rounded-md transition text-center cursor-pointer">
                                    Struk Retur (<span x-text="previewReceiptData.return_number"></span>)
                                </button>
                            </div>
                        </template>

                        <!-- Receipt Thermal Text Paper Preview -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-semibold uppercase tracking-wider text-gray-700" x-text="previewReceiptData && previewReceiptData.activeTab === 'return' ? 'Pratinjau Struk Retur' : 'Pratinjau Struk Penjualan'"></span>
                                <span class="text-[11px] text-gray-400">Kertas Thermal 80mm/58mm</span>
                            </div>
                            <div class="bg-gray-200 p-4 rounded-xl border border-gray-300 shadow-inner flex justify-center max-h-[380px] overflow-y-auto">
                                <div class="bg-white p-4 shadow-sm w-full max-w-[300px]" style="font-family: 'Courier New', Courier, monospace;">
                                    @if($posReceiptLogoEnabled)
                                    <div class="flex justify-center mb-3">
                                        <img src="{{ $posLogoUrl }}" alt="Logo Toko" class="w-12 h-12 object-contain rounded-full border border-gray-200 p-0.5 bg-white shadow-xs">
                                    </div>
                                    @endif
                                    <pre class="text-[11px] leading-tight text-black whitespace-pre-wrap word-break-all" x-text="previewReceiptData && previewReceiptData.activeTab === 'return' ? previewReceiptData.return_text : (previewReceiptData ? previewReceiptData.text : '')"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl flex-shrink-0">
                <template x-if="isProcessing">
                    <div class="flex items-center gap-3 w-full justify-end animate-pulse">
                        <div class="h-9 bg-gray-200 rounded-lg w-20"></div>
                        <div class="h-9 bg-gray-300 rounded-lg w-32"></div>
                    </div>
                </template>
                <template x-if="!isProcessing">
                    <div class="flex items-center gap-3 w-full justify-end">
                        <template x-if="previewReceiptData && previewReceiptData.isCloseSession">
                            <button @click="$wire.logoutCashier()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                                Selesai & Keluar (Logout)
                            </button>
                        </template>
                        <template x-if="!previewReceiptData || !previewReceiptData.isCloseSession">
                            <button @click="showReceiptPreviewModal = false" class="px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                                Selesai
                            </button>
                        </template>
                        <button @click="printBase64(previewReceiptData.activeTab === 'return' ? previewReceiptData.return_base64 : previewReceiptData.base64, previewReceiptData.order_id); showToast('Mengirim perintah cetak ke printer...', 'info')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h10z"></path></svg>
                            <span x-text="previewReceiptData && previewReceiptData.activeTab === 'return' ? 'Cetak Struk Retur' : 'Cetak Struk'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Modal Pengaturan Printer POS Hybrid - Raabiha POS 2-Column Style -->
    <div x-show="showPrinterModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-gray-950/60 backdrop-blur-xs font-sans" x-transition.opacity>
        <div class="bg-white w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl border border-gray-100 flex flex-col max-h-[90vh]" @click.away="showPrinterModal = false">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-white">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 leading-tight">Pengaturan Printer Thermal POS</h3>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Sambungkan printer cetak struk via Bluetooth atau Kabel USB</p>
                </div>
                <button @click="showPrinterModal = false; if (printerConnected && pendingReceiptData) { previewReceiptData = pendingReceiptData; pendingReceiptData = null; showReceiptPreviewModal = true; }" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-xl text-lg font-bold transition">&times;</button>
            </div>

            <!-- Content Area -->
            <div class="p-6 space-y-5 overflow-y-auto flex-1 bg-white">

                <!-- Status Bar Printer -->
                <div x-show="printerConnected" class="p-3 border rounded-xl flex items-center justify-between"
                    :class="printerConnected ? 'bg-emerald-50/80 border-emerald-200' : 'bg-rose-50/80 border-rose-200'">
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full"
                            :class="printerConnected ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></div>
                        <div>
                            <div class="font-bold text-sm" :class="printerConnected ? 'text-emerald-900' : 'text-rose-900'"
                                x-text="printerConnected ? (printerDeviceName || 'Printer') + ' — Terhubung' : 'Printer Belum Terhubung'"></div>
                            <div class="text-xs mt-0.5" :class="printerConnected ? 'text-emerald-700' : 'text-rose-700'"
                                x-text="printerConnected ? 'Siap mencetak struk.' : 'Pilih cara sambungkan printer di bawah.'"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="printTestReceipt()"
                            class="px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-100 border border-emerald-200 rounded-lg hover:bg-emerald-200 transition-colors">
                            Tes Printer
                        </button>
                        <button type="button" @click="disconnectPrinter()"
                            class="px-3 py-1.5 text-xs font-semibold text-white bg-rose-600 rounded-lg hover:bg-rose-700 shadow-sm transition-colors">
                            Putuskan
                        </button>
                    </div>
                </div>

                <!-- Auto Print Toggle -->
                <div class="p-4 bg-gray-50/80 rounded-xl border border-gray-200/80 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm text-gray-900">Cetak Struk Otomatis</div>
                        <div class="text-xs text-gray-500 mt-0.5">Struk langsung tercetak setiap selesai transaksi</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" x-model="autoPrintReceipt" @change="saveAutoPrintSettings" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <!-- Tombol Koneksi -->
                <div class="space-y-3">
                    <!-- Tombol Bluetooth (auto-try Print Agent → BLE) -->
                    <button type="button" @click="connectBluetooth()"
                        :disabled="isConnectingBT"
                        class="w-full py-4 px-5 rounded-2xl border-2 flex items-center gap-4 transition-all cursor-pointer"
                        :class="isConnectingBT ? 'bg-gray-50 border-gray-200 cursor-not-allowed' : 'bg-white hover:bg-emerald-50 border-emerald-500 hover:border-emerald-600 hover:shadow-md active:scale-[0.99]'">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition"
                            :class="isConnectingBT ? 'bg-gray-100 text-gray-400' : 'bg-emerald-50 text-emerald-600'">
                            <!-- Spinner saat loading -->
                            <template x-if="isConnectingBT">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </template>
                            <!-- Icon Bluetooth normal -->
                            <template x-if="!isConnectingBT">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18l6-6-6-6m-6 12l6-6-6-6"/>
                                </svg>
                            </template>
                        </div>
                        <div class="text-left">
                            <div class="font-bold text-sm" :class="isConnectingBT ? 'text-gray-400' : 'text-gray-900'"
                                x-text="isConnectingBT ? 'Menghubungkan...' : 'Sambungkan via Bluetooth'"></div>
                            <div class="text-xs mt-0.5" :class="isConnectingBT ? 'text-gray-400' : 'text-gray-500'"
                                x-text="isConnectingBT ? 'Sedang mencari printer, harap tunggu...' : 'Untuk printer nirkabel yang sudah dipasangkan'"></div>
                        </div>
                    </button>

                    <!-- Tombol Kabel USB -->
                    <button type="button" @click="scanAndConnectWebSerial()"
                        :disabled="isConnectingBT || isConnectingUSB"
                        class="w-full py-4 px-5 rounded-2xl border-2 flex items-center gap-4 transition-all cursor-pointer"
                        :class="isConnectingUSB ? 'bg-gray-50 border-gray-200 cursor-not-allowed' : 'bg-white hover:bg-blue-50 border-blue-400 hover:border-blue-500 hover:shadow-md active:scale-[0.99]'">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition"
                            :class="isConnectingUSB ? 'bg-gray-100 text-gray-400' : 'bg-blue-50 text-blue-600'">
                            <!-- Spinner saat loading -->
                            <template x-if="isConnectingUSB">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </template>
                            <!-- Icon USB normal -->
                            <template x-if="!isConnectingUSB">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </template>
                        </div>
                        <div class="text-left">
                            <div class="font-bold text-sm" :class="isConnectingUSB ? 'text-gray-400' : 'text-gray-900'"
                                x-text="isConnectingUSB ? 'Menghubungkan USB...' : 'Sambungkan via Kabel USB'"></div>
                            <div class="text-xs mt-0.5" :class="isConnectingUSB ? 'text-gray-400' : 'text-gray-500'"
                                x-text="isConnectingUSB ? 'Sedang mencari printer USB...' : 'Untuk printer yang terhubung dengan kabel ke komputer'"></div>
                        </div>
                    </button>
                </div>

                <!-- Panel: Scan Perangkat Bluetooth Terpasang -->
                <div class="border border-emerald-200 rounded-2xl overflow-hidden">
                    <button type="button"
                        @click="showDeviceList = !showDeviceList; if(showDeviceList && pairedDevices.length === 0) loadPairedDevices()"
                        class="w-full flex items-center justify-between px-4 py-3 bg-emerald-50 hover:bg-emerald-100 transition text-left cursor-pointer">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span class="text-xs font-semibold text-emerald-700">Pilih Printer dari Daftar Perangkat Terpasang</span>
                        </div>
                        <svg class="w-4 h-4 text-emerald-500 transition-transform duration-200" :class="showDeviceList ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="showDeviceList"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="px-4 pb-4 pt-3 bg-white space-y-2">

                        <p class="text-xs text-gray-500 mb-2">Pilih printer Bluetooth Classic yang sudah dipasangkan di pengaturan Bluetooth komputer Anda.</p>

                        <!-- Loading -->
                        <template x-if="loadingDevices">
                            <div class="flex items-center justify-center py-4 gap-2 text-gray-400">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span class="text-xs">Memuat daftar perangkat...</span>
                            </div>
                        </template>

                        <!-- Kosong -->
                        <template x-if="!loadingDevices && pairedDevices.length === 0">
                            <div class="text-center py-4">
                                <p class="text-xs text-gray-400 mb-2">Tidak ada perangkat Bluetooth yang terpasang.</p>
                                <p class="text-xs text-amber-600 font-medium">Langkah: Buka Pengaturan Bluetooth di komputer → Tambahkan printer Kassen → Kembali ke sini dan klik Muat Ulang.</p>
                                <button type="button" @click="loadPairedDevices()"
                                    class="mt-2 text-xs text-emerald-600 hover:underline cursor-pointer">Muat Ulang</button>
                            </div>
                        </template>

                        <!-- Daftar device -->
                        <template x-if="!loadingDevices && pairedDevices.length > 0">
                            <div class="space-y-1.5">
                                <template x-for="dev in pairedDevices" :key="dev.mac">
                                    <button type="button"
                                        @click="connectWithMac(dev.mac, dev.name)"
                                        :disabled="isConnectingBT"
                                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50 hover:bg-emerald-50 hover:border-emerald-300 transition cursor-pointer">
                                        <div class="flex items-center gap-2.5 text-left">
                                            <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18l6-6-6-6m-6 12l6-6-6-6"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-800" x-text="dev.name"></div>
                                                <div class="text-xs text-gray-400 font-mono" x-text="dev.mac"></div>
                                            </div>
                                        </div>
                                        <span class="text-xs text-emerald-600 font-medium">Hubungkan</span>
                                    </button>
                                </template>
                                <button type="button" @click="loadPairedDevices()"
                                    class="w-full text-xs text-gray-400 hover:text-gray-600 py-1 cursor-pointer">↻ Muat Ulang</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Teks bantuan -->
                <p class="text-xs text-gray-400 text-center">
                    Pastikan printer sudah menyala sebelum menekan tombol di atas.
                </p>

                <!-- Atur Koneksi Manual (accordion) -->
                <div class="border border-gray-200 rounded-2xl overflow-hidden">
                    <!-- Header accordion -->
                    <button type="button" @click="showManualForm = !showManualForm"
                        class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition text-left cursor-pointer">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-xs font-semibold text-gray-500">Koneksi Manual (Pengaturan Lanjutan)</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="showManualForm ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Konten accordion -->
                    <div x-show="showManualForm"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="px-4 pb-4 pt-3 space-y-3 bg-white">

                        <!-- Peringatan -->
                        <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-xs text-amber-700 leading-relaxed">
                                Bagian ini untuk pengaturan lanjutan. Jika tidak yakin, gunakan tombol <strong>Sambungkan via Bluetooth</strong> di atas.
                            </p>
                        </div>

                        <!-- Input alamat -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Alamat Bluetooth atau Port USB
                            </label>
                            <input type="text"
                                x-model="manualPrinterAddress"
                                placeholder="Contoh: 05:37:57:E4:5A:23 atau COM3"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 placeholder-gray-400 font-mono"
                                @keydown.enter="connectManual()"
                            />
                            <p class="text-[11px] text-gray-400 mt-1">
                                Bluetooth: isi dengan MAC address printer (lihat di pengaturan Bluetooth komputer).
                                USB: isi dengan nama port seperti COM3 (Windows) atau /dev/ttyUSB0 (Linux).
                            </p>
                        </div>

                        <!-- Tombol sambungkan manual -->
                        <button type="button" @click="connectManual()"
                            :disabled="!manualPrinterAddress.trim() || isConnectingBT"
                            class="w-full py-2.5 px-4 rounded-xl border-2 font-bold text-sm transition flex items-center justify-center gap-2 cursor-pointer"
                            :class="!manualPrinterAddress.trim() || isConnectingBT
                                ? 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed'
                                : 'bg-white hover:bg-emerald-50 border-emerald-500 text-emerald-700 hover:shadow-md active:scale-[0.99]'">
                            <template x-if="isConnectingBT">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </template>
                            <span x-text="isConnectingBT ? 'Menghubungkan...' : 'Sambungkan'"></span>
                        </button>
                    </div>
                </div>

                <!-- Note: Android tidak support -->
                <p class="text-[11px] text-gray-300 text-center">
                    ⚠️ Koneksi printer hanya tersedia di komputer Windows/Linux. Tidak support Android.
                </p>

            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 rounded-b-2xl flex-shrink-0">
                <button type="button" @click="showPrinterModal = false; if (printerConnected && pendingReceiptData) { previewReceiptData = pendingReceiptData; pendingReceiptData = null; showReceiptPreviewModal = true; }" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition duration-150 cursor-pointer">
                    Selesai & Tutup
                </button>
            </div>
        </div>
    </div>


    <!-- Modal Detail Rincian Nota Transaksi - Filament Native Style -->
    <div x-show="showDetailOrderModal" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-xs font-sans" x-transition.opacity>
        <div class="bg-white w-full max-w-lg rounded-xl overflow-hidden shadow-2xl border border-gray-200 flex flex-col max-h-[90vh]" @click.away="showDetailOrderModal = false">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                <div>
                    <h3 class="font-bold text-base text-gray-950 leading-tight" x-text="'Detail Nota #' + (selectedOrderDetail ? selectedOrderDetail.order_number : '')"></h3>
                    <p class="text-xs text-gray-500 font-medium" x-text="selectedOrderDetail ? selectedOrderDetail.created_at + ' · Kasir: ' + selectedOrderDetail.cashier_name : ''"></p>
                </div>
                <button @click="showDetailOrderModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">&times;</button>
            </div>

            <!-- Content Area -->
            <div class="p-6 space-y-4 overflow-y-auto flex-1 text-xs bg-gray-50/50" x-show="selectedOrderDetail">
                <!-- Info Pelanggan & Status -->
                <div class="flex justify-between items-center p-3.5 bg-white rounded-lg border border-gray-200 shadow-xs">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-gray-500 tracking-wider">Pelanggan</div>
                        <div class="font-bold text-gray-900 text-xs mt-0.5" x-text="selectedOrderDetail ? selectedOrderDetail.customer_name : ''"></div>
                        <div class="text-gray-500 text-[11px]" x-text="selectedOrderDetail ? selectedOrderDetail.customer_phone : ''"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold text-gray-500 tracking-wider">Metode Bayar</div>
                        <span class="inline-block mt-0.5 px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded-md ring-1 ring-inset ring-emerald-600/20 text-[11px]" x-text="selectedOrderDetail ? selectedOrderDetail.payment_method : ''"></span>
                    </div>
                </div>

                <!-- Rincian Item Barang -->
                <div>
                    <div class="font-bold text-gray-900 text-xs uppercase tracking-wider mb-2">Rincian Barang</div>
                    <div class="border border-gray-200 rounded-lg overflow-hidden divide-y divide-gray-100 shadow-xs">
                        <template x-for="item in (selectedOrderDetail ? selectedOrderDetail.items : [])">
                            <div class="p-3 flex items-center justify-between bg-white">
                                <div>
                                    <div class="font-bold text-gray-900" x-text="item.name"></div>
                                    <div class="text-gray-400 text-[11px]" x-show="item.variant" x-text="item.variant"></div>
                                    <div class="text-gray-500 text-[11px] mt-0.5" x-text="item.qty + ' x Rp ' + formatMoney(item.price)"></div>
                                </div>
                                <div class="font-bold text-gray-950 text-xs" x-text="'Rp ' + formatMoney(item.subtotal)"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Ringkasan Pembayaran -->
                <div class="p-4 bg-white rounded-lg border border-gray-200 space-y-2 shadow-xs">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-gray-900" x-text="'Rp ' + formatMoney(selectedOrderDetail ? selectedOrderDetail.subtotal : 0)"></span>
                    </div>
                    <div x-show="selectedOrderDetail && selectedOrderDetail.discount_total > 0" class="flex justify-between text-emerald-700 font-medium">
                        <span>Diskon / Voucher</span>
                        <span class="font-semibold" x-text="'- Rp ' + formatMoney(selectedOrderDetail ? selectedOrderDetail.discount_total : 0)"></span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-gray-950 pt-2 border-t border-gray-200 uppercase tracking-wider">
                        <span>TOTAL AKHIR</span>
                        <span class="text-emerald-600 text-sm font-black" x-text="'Rp ' + formatMoney(selectedOrderDetail ? selectedOrderDetail.grand_total : 0)"></span>
                    </div>
                    <div x-show="selectedOrderDetail && selectedOrderDetail.cash_paid > 0" class="flex justify-between text-gray-500 pt-1 text-[11px]">
                        <span>Tunai Diterima / Kembalian</span>
                        <span x-text="'Rp ' + formatMoney(selectedOrderDetail ? selectedOrderDetail.cash_paid : 0) + ' / Rp ' + formatMoney(selectedOrderDetail ? selectedOrderDetail.cash_change : 0)"></span>
                    </div>
                </div>


            </div>

            <!-- Footer Action -->
            <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end rounded-b-xl flex-shrink-0">
                <button @click="showDetailOrderModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Detail Biodata & Log Transaksi Pelanggan -->
    <div x-show="showCustomerDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/50 backdrop-blur-xs font-sans" x-transition.opacity>
        <div class="bg-white w-full max-w-2xl rounded-xl overflow-hidden shadow-2xl border border-gray-200 flex flex-col max-h-[90vh]" @click.away="showCustomerDetailModal = false">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-bold text-sm flex items-center justify-center border border-emerald-200">
                        <span x-text="selectedCustomerDetail ? (selectedCustomerDetail.customer_name || 'P').substring(0,1).toUpperCase() : 'P'"></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-gray-950 leading-tight" x-text="selectedCustomerDetail ? selectedCustomerDetail.customer_name : 'Pelanggan'"></h3>
                        <p class="text-xs text-gray-500 font-medium" x-text="selectedCustomerDetail && selectedCustomerDetail.customer_phone ? selectedCustomerDetail.customer_phone : 'Tanpa No. HP'"></p>
                    </div>
                </div>
                <button @click="showCustomerDetailModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">&times;</button>
            </div>

            <!-- Content Area -->
            <div class="p-6 space-y-5 overflow-y-auto flex-1 text-xs bg-gray-50/50" x-show="selectedCustomerDetail">
                <!-- Ringkasan Profil & Loyalty Status Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-xs">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Stempel Aktif</div>
                        <div class="text-sm font-extrabold text-emerald-600 mt-1" x-text="(selectedCustomerDetail ? (selectedCustomerDetail.active_stamps ?? (selectedCustomerDetail.stamp_count % 12)) : 0) + ' / 12 Cap'"></div>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-xs">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Saldo Poin</div>
                        <div class="text-sm font-extrabold text-gray-900 mt-1" x-text="formatMoney(selectedCustomerDetail ? selectedCustomerDetail.loyalty_points : 0) + ' Poin'"></div>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-xs">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kartu Selesai</div>
                        <div class="text-sm font-extrabold text-amber-600 mt-1" x-text="(selectedCustomerDetail ? selectedCustomerDetail.completed_cards_count : 0) + ' Kartu'"></div>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-xs">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Belanja</div>
                        <div class="text-sm font-extrabold text-emerald-600 mt-1" x-text="'Rp ' + formatMoney(selectedCustomerDetail ? selectedCustomerDetail.total_spent : 0)"></div>
                    </div>
                </div>

                <!-- Informasi Kontak Pelanggan -->
                <div class="bg-white p-3.5 rounded-lg border border-gray-200 space-y-1 shadow-xs">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Biodata Lengkap</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-1">
                        <div><span class="text-gray-500">Nama:</span> <strong class="text-gray-900" x-text="selectedCustomerDetail ? selectedCustomerDetail.customer_name : '-'"></strong></div>
                        <div><span class="text-gray-500">No. HP:</span> <strong class="text-gray-900 font-mono" x-text="selectedCustomerDetail && selectedCustomerDetail.customer_phone ? selectedCustomerDetail.customer_phone : '-'"></strong></div>
                        <div><span class="text-gray-500">Email:</span> <span class="text-gray-900 font-medium" x-text="selectedCustomerDetail && selectedCustomerDetail.customer_email ? selectedCustomerDetail.customer_email : '-'"></span></div>
                        <div><span class="text-gray-500">Alamat:</span> <span class="text-gray-900 font-medium" x-text="selectedCustomerDetail && selectedCustomerDetail.customer_address ? selectedCustomerDetail.customer_address : '-'"></span></div>
                    </div>
                </div>

                <!-- Hadiah Fisik Loyalti yang Berhak Diterima -->
                <template x-if="selectedCustomerDetail && availableLoyaltyManualRewardsForCustomer.length > 0">
                    <div class="bg-amber-50 p-3.5 rounded-xl border border-amber-300 space-y-2 shadow-xs">
                        <div class="font-bold text-amber-950 text-xs flex items-center justify-between">
                            <span>🎁 Hadiah Barang Fisik / Souvenir Pelanggan Ini:</span>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-amber-200 text-amber-900 border border-amber-300 font-bold" x-text="(selectedCustomerDetail.stamp_count || 0) + ' / 12 Cap'"></span>
                        </div>
                        <div class="space-y-2 pt-1">
                            <template x-for="(mTier, mIdx) in availableLoyaltyManualRewardsForCustomer" :key="mIdx">
                                <div class="bg-white p-2.5 rounded-lg border border-amber-200 flex items-center justify-between shadow-xs">
                                    <div>
                                        <div class="font-bold text-gray-900 text-xs" x-text="mTier.description"></div>
                                        <div class="text-[10px] text-amber-800 font-medium" x-text="'Syarat: Minimal ' + mTier.min_stamps + ' Cap Stempel (Terpenuhi)'"></div>
                                    </div>
                                    <button type="button"
                                            @click="$wire.claimPhysicalGiftDirectly(selectedCustomerDetail.customer_phone, mTier.description);"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs transition cursor-pointer flex items-center gap-1">
                                        <span>🎁 Serahkan Hadiah</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Log / Riwayat Transaksi Per Baris -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider">Log Riwayat Transaksi Per Baris</h4>
                        <span class="text-[11px] font-semibold text-gray-500" x-text="(selectedCustomerDetail && selectedCustomerDetail.customer_orders ? selectedCustomerDetail.customer_orders.length : 0) + ' Transaksi Tercatat'"></span>
                    </div>

                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 border-b border-gray-200 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                                    <tr>
                                        <th class="py-2.5 px-3">No. Nota</th>
                                        <th class="py-2.5 px-3">Tanggal & Jam</th>
                                        <th class="py-2.5 px-3">Metode Bayar</th>
                                        <th class="py-2.5 px-3 text-right">Total Belanja</th>
                                        <th class="py-2.5 px-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-xs">
                                    <template x-for="order in (selectedCustomerDetail ? selectedCustomerDetail.customer_orders : [])" :key="order.id">
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="py-2.5 px-3 font-mono font-bold text-gray-900" x-text="'#' + order.order_number"></td>
                                            <td class="py-2.5 px-3 text-gray-600 whitespace-nowrap" x-text="order.created_at"></td>
                                            <td class="py-2.5 px-3">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700" x-text="order.payment_method"></span>
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-bold text-gray-950 whitespace-nowrap" x-text="'Rp ' + formatMoney(order.grand_total)"></td>
                                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Lunas</span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="!selectedCustomerDetail || !selectedCustomerDetail.customer_orders || selectedCustomerDetail.customer_orders.length === 0">
                                        <td colspan="5" class="py-6 text-center text-gray-400 italic">Belum ada log transaksi tercatat untuk pelanggan ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end rounded-b-xl flex-shrink-0">
                <button @click="showCustomerDetailModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Global Livewire Loading Overlay (Spinner) -->
    <div wire:loading.flex wire:target="submitReturn, printZReport" class="fixed inset-0 z-[9999] flex-col items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm font-sans">
        <div class="bg-white p-6 rounded-2xl shadow-xl flex flex-col items-center max-w-sm w-full mx-4 border border-gray-100">
            <svg class="animate-spin h-10 w-10 text-emerald-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <h3 class="text-gray-900 font-bold text-lg mb-1">Memuat Struk...</h3>
            <p class="text-gray-500 text-sm text-center">Mohon tunggu sebentar, sistem sedang memproses dan mengambil data pratinjau struk Anda...</p>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posSystem', () => ({
                isSidebarOpen: false,
                cart: [],
                discount: 0,
                cartBouncing: false,
                bounceTimeout: null,
                showDetailOrderModal: false,
                selectedOrderDetail: null,

                openDetailOrderModal(data) {
                    this.selectedOrderDetail = data;
                    this.showDetailOrderModal = true;
                },
                
                // Voucher & Loyalty State
                posTabId: Math.random().toString(36).substring(2, 10),
                vouchers: {{ \Illuminate\Support\Js::from($vouchers ?? []) }},
                posLoyaltyTiers: {{ \Illuminate\Support\Js::from(json_decode(\App\Models\SiteSetting::where('key', 'pos_loyalty_tiers')->value('value') ?? '[]', true) ?: []) }},
                paymentMethods: {{ \Illuminate\Support\Js::from($paymentMethods ?? []) }},
                isSplitPayment: false,
                splitPayments: [{method: 'cash', amount: ''}],

                get totalSplitPaid() {
                    return this.splitPayments.reduce((acc, curr) => acc + (parseFloat(curr.amount) || 0), 0);
                },

                addSplitPayment() {
                    this.splitPayments.push({method: 'cash', amount: ''});
                },

                removeSplitPayment(index) {
                    if (this.splitPayments.length > 1) {
                        this.splitPayments.splice(index, 1);
                        this.calculateChange();
                    }
                },

                broadcastCrossTabSync(reason = 'sync') {
                    localStorage.setItem('pos_cross_tab_sync', JSON.stringify({
                        timestamp: Date.now(),
                        reason: reason,
                        tabId: this.posTabId
                    }));
                },
                allProducts: {{ \Illuminate\Support\Js::from($allProductsJson ?? []) }},
                exchangeSearchQuery: '',
                showVoucherModal: false,
                activeVoucher: null,
                
                // Modals
                showVariantModal: false,
                showCheckoutModal: false,
                showMobileCartDrawer: false,
                showCloseSession: false,
                showPickupConfirmModal: false,
                confirmPickupOrder: null,
                activePage: 'kasir',
                
                // Checkout State
                cashPaid: 0,
                displayCashPaid: '',
                cashChange: 0,
                paymentMethod: 'cash',
                customerName: '',
                customerPhone: '',
                isReserved: false,
                pickupDate: '',
                activeCustomerLoyalty: null,
                allPosCustomers: @js($allPosCustomers),
                customerSearchInput: '',
                showCustomerDropdown: false,
                isProcessing: false,
                isFullscreen: false,
                deferredInstallPrompt: null,
                canInstallApp: false,
                
                // Toasts
                toasts: [],
                toastId: 0,

                currentProductForVariant: null,
                currentVariants: [],
                variantShowOutOfStock: false,
                selectedVariantAttributes: {},
                variantAttributeSearch: {},
                variantAttributeGroups: [],
                
                // Printer State
                showPrinterModal: false,
                printerConnected: false,
                autoPrintReceipt: localStorage.getItem('pos_auto_print') === 'true',
                printerDevice: null,
                printerDeviceName: localStorage.getItem('pos_printer_name') || '',
                printerType: 'bluetooth',
                printerCharacteristic: null,
                printerPort: null,
                bridgeSocket: null,
                printerConnectionMethod: null, // 'ble', 'serial', 'bridge'
                isConnectingBT: false,
                isConnectingUSB: false,
                pairedDevices: [],
                loadingDevices: false,
                showDeviceList: false,
                showManualForm: false,
                manualPrinterAddress: localStorage.getItem('pos_printer_manual_addr') || '',
                // State Modal Produk Kustom Fast Entry
                showCustomProductModal: false,
                customProductName: '',
                customPrice: '',
                customNormalPrice: '',
                customPurchasePrice: '',
                customQty: 1,
                customSaveToCatalog: true,
                customAddToCart: false,

                // State Modal Detail & Nego Produk Kustom
                showCustomNegoModal: false,
                customNegoProduct: {
                    id: null,
                    name: '',
                    originalPrice: 0,
                    purchasePrice: 0,
                    negoPrice: 0,
                    qty: 1,
                    defaultImage: ''
                },



                // State Modal Detail Pelanggan & Log Transaksi
                showCustomerDetailModal: false,
                selectedCustomerDetail: null,

                openCustomerDetailModal(customerData) {
                    this.selectedCustomerDetail = customerData;
                    this.showCustomerDetailModal = true;
                },

                heldCarts: [],
                showHoldModal: false,
                showReceiptPreviewModal: false,
                previewReceiptData: { orderNumber: '', cashChange: 0, text: '', base64: '' },
                pendingReceiptData: null, // simpan sementara jika printer belum konek saat mau cetak

                // Confirmation Modal State
                showConfirmModal: false,
                confirmTitle: '',
                confirmMessage: '',
                confirmAction: null,
                
                // Buka modal pratinjau struk, atau tampilkan modal printer dulu jika belum konek
                _openReceiptOrPrinterModal() {
                    if (!this.printerConnected) {
                        this.pendingReceiptData = this.previewReceiptData;
                        this.showPrinterModal = true;
                        this.showToast('Hubungkan printer terlebih dahulu untuk mencetak struk.', 'warning');
                    } else {
                        this.showReceiptPreviewModal = true;
                        this.pendingReceiptData = null;
                    }
                },

                // Dipanggil setiap kali printer berhasil konek
                // Jika ada pendingReceiptData → tutup modal printer & buka pratinjau struk
                // Jika tidak ada (buka dari icon biasa) → biarkan modal tetap terbuka
                _afterPrinterConnected() {
                    if (this.pendingReceiptData) {
                        this.showPrinterModal = false;
                        this.previewReceiptData = this.pendingReceiptData;
                        this.pendingReceiptData = null;
                        this.showReceiptPreviewModal = true;
                    }
                    // Tidak ada pendingReceiptData: modal tetap terbuka, admin bisa test printer
                },

                askConfirm(title, message, callback) {
                    this.confirmTitle = title;
                    this.confirmMessage = message;
                    this.confirmAction = callback;
                    this.showConfirmModal = true;
                },
                
                executeConfirm() {
                    if (this.confirmAction) this.confirmAction();
                    this.showConfirmModal = false;
                },

                // Void Order Modal State
                supervisors: {{ \Illuminate\Support\Js::from($supervisorsList ?? []) }},
                selectedSupervisorId: '',
                voidSupervisorIdInput: '',
                showVoidModal: false,
                voidOrderId: null,
                voidOrderNumber: '',
                voidOrderTotal: 0,
                voidReasonInput: '',
                voidSupervisorPinInput: '',

                openVoidModal(id, number, total) {
                    this.voidOrderId = id;
                    this.voidOrderNumber = number;
                    this.voidOrderTotal = total;
                    this.voidReasonInput = '';
                    this.voidSupervisorIdInput = this.supervisors.length === 1 ? this.supervisors[0].id : '';
                    this.voidSupervisorPinInput = '';
                    this.showVoidModal = true;
                },

                submitVoidOrder() {
                    if (!this.voidSupervisorIdInput) {
                        this.showToast('Pilih Supervisor terlebih dahulu', 'error');
                        return;
                    }
                    const pin = (this.voidSupervisorPinInput || '').toString().trim();
                    if (!pin || pin.length !== 6) {
                        this.showToast('Masukkan 6-digit PIN Supervisor', 'error');
                        return;
                    }
                    @this.call('voidOrder', this.voidOrderId, this.voidSupervisorIdInput, pin, this.voidReasonInput);
                },

                // Return & Exchange Modal State
                showReturnModal: false,
                showReturnSupervisorModal: false,
                refundMaxWithoutPin: {{ (int) (\App\Models\SiteSetting::where('key', 'pos_refund_max_without_pin')->value('value') ?? 0) }},
                returnStep: 1,
                refundPaymentMethod: 'cash', // 'cash' or 'bank'
                refundBankName: 'BCA',
                refundBankAccount: '',
                returnOrderId: null,
                returnOrderNumber: '',
                returnType: 'exchange',
                returnReasonInput: '',
                returnSupervisorIdInput: '',
                returnSupervisorPinInput: '',
                returnOrderItems: [],
                returnExchangedItems: [],

                openReturnModal(data) {
                    if (!data) return;
                    // Format data dari object payload tunggal atau argumen terpisah
                    const id = typeof data === 'object' ? data.id : arguments[0];
                    const number = typeof data === 'object' ? data.order_number : arguments[1];
                    const items = typeof data === 'object' ? data.items : arguments[2];

                    this.returnStep = 1;
                    this.refundPaymentMethod = 'cash';
                    this.refundBankName = 'BCA';
                    this.refundBankAccount = '';
                    this.returnOrderId = id;
                    this.returnOrderNumber = number || '';
                    this.returnType = 'exchange';
                    this.returnReasonInput = '';
                    this.returnSupervisorIdInput = this.supervisors.length === 1 ? this.supervisors[0].id : '';
                    this.returnSupervisorPinInput = '';
                    this.returnOrderItems = (items || []).map(i => {
                        const purchasedQty = (i.qty !== undefined && i.qty !== null ? parseInt(i.qty) : (i.quantity !== undefined && i.quantity !== null ? parseInt(i.quantity) : 1));
                        return {
                            ...i,
                            quantity: purchasedQty,
                            qty: purchasedQty,
                            return_qty: 0
                        };
                    });
                    this.returnExchangedItems = [];
                    this.showReturnModal = true;
                    this.showReturnSupervisorModal = false;
                },

                get returnSubtotal() {
                    return (this.returnOrderItems || []).reduce((acc, i) => acc + (i.price * (i.return_qty || 0)), 0);
                },

                get exchangeSubtotal() {
                    return (this.returnExchangedItems || []).reduce((acc, i) => acc + (i.price * (i.quantity || 0)), 0);
                },

                get returnNetAmount() {
                    return this.returnType === 'refund' ? -this.returnSubtotal : (this.exchangeSubtotal - this.returnSubtotal);
                },

                addExchangeItem(product, variant = null) {
                    const price = variant ? parseFloat(variant.price) : parseFloat(product.price);
                    const name = variant ? (product.name + ' - ' + variant.name) : product.name;
                    const vId = variant ? variant.id : null;

                    const existing = this.returnExchangedItems.find(i => i.product_id === product.id && i.product_variant_id === vId);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.returnExchangedItems.push({
                            product_id: product.id,
                            product_variant_id: vId,
                            name: name,
                            price: price,
                            quantity: 1
                        });
                    }
                },

                removeExchangeItem(idx) {
                    this.returnExchangedItems.splice(idx, 1);
                },

                handleReturnSubmitClick() {
                    const returned = (this.returnOrderItems || [])
                        .filter(i => (i.return_qty || 0) > 0);

                    if (returned.length === 0) {
                        this.showToast('Pilih minimal 1 barang yang akan diretur', 'error');
                        return;
                    }

                    const net = this.returnNetAmount;

                    if (this.returnType === 'exchange' && net > 0) {
                        this.showToast('Barang yang ditukar lebih mahal. Silakan gunakan opsi Refund dan buat transaksi baru.', 'error');
                        return;
                    }

                    // Alur eksekusi langsung (Refund murni ATAU Exchange yang harganya lebih murah/sama)
                    const refundAmount = Math.abs(net);
                    const isRefundOrNegative = (this.returnType === 'refund' || net < 0);
                    const requiresSupervisor = isRefundOrNegative && (refundAmount > this.refundMaxWithoutPin);

                    if (isRefundOrNegative && this.refundPaymentMethod === 'bank' && !(this.refundBankAccount || '').trim()) {
                        this.showToast('Masukkan Nomor Rekening / E-Wallet & Nama Pemilik untuk Refund Transfer Bank', 'error');
                        return;
                    }

                    if (requiresSupervisor) {
                        this.returnSupervisorIdInput = this.supervisors.length === 1 ? this.supervisors[0].id : '';
                        this.returnSupervisorPinInput = '';
                        this.showReturnSupervisorModal = true;
                    } else {
                        // Jika di bawah threshold tanpa PIN atau tukar senilai (Rp 0), eksekusi langsung
                        this.submitReturnProcess();
                    }
                },

                confirmReturnWithSupervisor() {
                    if (!this.returnSupervisorIdInput) {
                        this.showToast('Pilih Supervisor pengizin pengembalian uang terlebih dahulu', 'error');
                        return;
                    }
                    const pin = (this.returnSupervisorPinInput || '').toString().trim();
                    if (!pin || pin.length !== 6) {
                        this.showToast('Masukkan 6-digit PIN Supervisor yang valid', 'error');
                        return;
                    }
                    this.showReturnSupervisorModal = false;
                    this.submitReturnProcess();
                },

                submitReturnProcess() {
                    const returned = (this.returnOrderItems || [])
                        .filter(i => (i.return_qty || 0) > 0)
                        .map(i => ({
                            product_id: i.product_id,
                            product_variant_id: i.product_variant_id,
                            quantity: parseInt(i.return_qty)
                        }));

                    if (returned.length === 0) {
                        this.showToast('Pilih minimal 1 barang yang akan diretur', 'error');
                        return;
                    }

                    const exchanged = this.returnType === 'exchange' ? this.returnExchangedItems.map(i => ({
                        product_id: i.product_id,
                        product_variant_id: i.product_variant_id,
                        quantity: parseInt(i.quantity)
                    })) : [];

                    const payload = {
                        order_id: this.returnOrderId,
                        type: this.returnType,
                        reason: this.returnReasonInput,
                        returned_items: returned,
                        exchanged_items: exchanged,
                        refund_payment_method: this.refundPaymentMethod || 'cash',
                        refund_bank_name: this.refundBankName,
                        refund_bank_account: this.refundBankAccount,
                        supervisor_id: this.returnSupervisorIdInput,
                        supervisor_pin: this.returnSupervisorPinInput
                    };

                    this.isProcessing = true;
                    this.showReturnModal = false;
                    this.showReceiptPreviewModal = true; // tetap buka untuk loading state
                    @this.call('processReturn', JSON.stringify(payload));
                },

                // Input Modal State
                showInputModal: false,
                showPettyCashModal: false,
                showPettyCashLimitConfirmModal: false,
                pettyCashLimitMessage: '',
                showChangePinModal: false,
                showSupervisorPinModal: false,
                showSupervisorPinText: false,
                showVoidPinText: false,
                supervisorPinInput: '',
                supervisorErrorMessage: '',
                supervisorReasonMessage: '',
                pendingSupervisorCallback: null,

                proceedPettyCashSupervisorAuth() {
                    this.showPettyCashLimitConfirmModal = false;
                    this.requestSupervisorAuth(this.pettyCashLimitMessage || 'Otorisasi Pengeluaran Kas di Atas Limit', () => {
                        $wire.addPettyCash(this.selectedSupervisorId, this.supervisorPinInput);
                    });
                },

                requestSupervisorAuth(reason, callback) {
                    this.supervisorReasonMessage = reason;
                    this.selectedSupervisorId = this.supervisors.length === 1 ? this.supervisors[0].id : '';
                    this.supervisorPinInput = '';
                    this.showSupervisorPinText = false;
                    this.supervisorErrorMessage = '';
                    this.pendingSupervisorCallback = callback;
                    this.showSupervisorPinModal = true;
                    setTimeout(() => {
                        if (this.$refs.supervisorPinField) this.$refs.supervisorPinField.focus();
                    }, 100);
                },

                submitSupervisorAuth() {
                    if (!this.selectedSupervisorId) {
                        this.supervisorErrorMessage = 'Pilih Supervisor yang bertugas terlebih dahulu.';
                        return;
                    }
                    const pin = (this.supervisorPinInput || '').toString().trim();
                    if (!pin || pin.length !== 6) {
                        this.supervisorErrorMessage = 'Masukkan 6 digit angka PIN Supervisor.';
                        return;
                    }
                    this.supervisorErrorMessage = '';
                    @this.call('verifySupervisorPin', this.selectedSupervisorId, pin, 'general');
                },
                isLocked: false,
                isLockError: false,
                lockPasswordInput: '',
                lockErrorMessage: '',
                lastActivityTime: Date.now(),

                appendLockPin(num) {
                    this.lockPasswordInput = (this.lockPasswordInput || '').toString();
                    if (this.lockPasswordInput.length < 6) {
                        this.lockPasswordInput += num.toString();
                    }
                },

                clearLockPin() {
                    this.lockPasswordInput = '';
                    this.lockErrorMessage = '';
                },

                lockScreen() {
                    this.isLocked = true;
                    this.lockPasswordInput = '';
                    this.lockErrorMessage = '';
                    sessionStorage.setItem('pos_locked', 'true');
                    setTimeout(() => {
                        const el = document.getElementById('posLockPasswordField');
                        if (el) el.focus();
                    }, 100);
                },

                submitUnlock() {
                    const pin = (this.lockPasswordInput || '').toString().trim();
                    if (!pin || pin.length !== 6) {
                        this.lockErrorMessage = 'Masukkan 6 digit angka PIN.';
                        return;
                    }
                    this.lockErrorMessage = '';
                    @this.call('unlockScreenWithPin', pin);
                },

                handleUnlockFailed(message) {
                    this.isLockError = true;
                    this.lockErrorMessage = message || 'PIN POS 6-digit salah!';
                    setTimeout(() => {
                        this.lockPasswordInput = '';
                        this.isLockError = false;
                        const el = document.getElementById('posLockPasswordField');
                        if (el) el.focus();
                    }, 600);
                },

                resetAutoLockTimer() {
                    this.lastActivityTime = Date.now();
                },

                startAutoLockChecker() {
                    // Check every 10 seconds for 5 minutes (300,000 ms) inactivity
                    setInterval(() => {
                        if (!this.isLocked && (Date.now() - this.lastActivityTime > 300000)) {
                            this.lockScreen();
                        }
                    }, 10000);

                    const events = ['mousemove', 'keydown', 'touchstart', 'click'];
                    events.forEach(evt => {
                        window.addEventListener(evt, () => this.resetAutoLockTimer(), { passive: true });
                    });
                },
                inputTitle: '',
                inputMessage: '',
                inputValue: '',
                inputPlaceholder: '',
                inputAction: null,
                
                askInput(title, message, placeholder, defaultValue, callback) {
                    this.inputTitle = title;
                    this.inputMessage = message;
                    this.inputPlaceholder = placeholder;
                    this.inputValue = defaultValue || '';
                    this.inputAction = callback;
                    this.showInputModal = true;
                    // Focus input after modal renders
                    setTimeout(() => {
                        const el = document.getElementById('alpineInputModalField');
                        if(el) el.focus();
                    }, 50);
                },
                
                executeInput() {
                    if (this.inputAction) this.inputAction(this.inputValue);
                    this.showInputModal = false;
                },

                init() {
                    // Load dari localStorage
                    const storedHold = localStorage.getItem('pos_held_carts');
                    if (storedHold) {
                        try {
                            this.heldCarts = JSON.parse(storedHold);
                        } catch(e) {}
                    }

                    this.loadActiveCart();

                    this.$watch('cart', () => this.saveActiveCart());
                    this.$watch('activeVoucher', () => this.saveActiveCart());
                    this.$watch('manualDiscountValue', () => this.saveActiveCart());
                    this.$watch('customerName', () => this.saveActiveCart());
                    this.$watch('customerPhone', () => this.saveActiveCart());
                    this.$watch('isReserved', () => this.saveActiveCart());
                    this.$watch('pickupDate', () => this.saveActiveCart());

                    // Listener Fullscreen & PWA Desktop App
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                    });
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredInstallPrompt = e;
                        this.canInstallApp = true;
                    });

                    // Listener Sinkronisasi Multi-Tab Browser Realtime
                    window.addEventListener('storage', (e) => {
                        if (e.key === 'pos_cross_tab_sync' && e.newValue) {
                            try {
                                const data = JSON.parse(e.newValue);
                                if (data && data.tabId !== this.posTabId) {
                                    this.$wire.$refresh();
                                    const storedHold = localStorage.getItem('pos_held_carts');
                                    if (storedHold) {
                                        try { this.heldCarts = JSON.parse(storedHold); } catch(err) {}
                                    }
                                }
                            } catch(err) {}
                        }
                    });

                    // Listener Event Notify (Reset Loading & Tampilkan Toast)
                    window.addEventListener('notify', (e) => {
                        this.isProcessing = false;
                        this.showCheckoutModal = false;
                        const data = (e.detail && e.detail[0]) ? e.detail[0] : (e.detail || {});
                        if (data && data.message) {
                            this.showToast(data.message, data.type || 'info');
                        }
                    });

                    // Menerima event dari Livewire
                    if (sessionStorage.getItem('pos_locked') === 'true') {
                        this.isLocked = true;
                    }
                    window.addEventListener('screen-unlocked', () => {
                        this.isLocked = false;
                        this.lockPasswordInput = '';
                        this.lockErrorMessage = '';
                        sessionStorage.removeItem('pos_locked');
                        this.showToast('Layar kasir berhasil dibuka', 'success');
                    });
                    window.addEventListener('screen-unlock-failed', (e) => {
                        this.lockErrorMessage = (e.detail && e.detail[0] && e.detail[0].message) ? e.detail[0].message : (e.detail && e.detail.message ? e.detail.message : 'Password salah!');
                        this.showToast(this.lockErrorMessage, 'error');
                    });
                    window.addEventListener('session-opened', () => { 
                        this.activePage = 'kasir'; 
                        this.clearCart(true); 
                        localStorage.removeItem('pos_active_cart'); 
                        this.broadcastCrossTabSync('session-opened');
                        this.showToast('Sesi kasir berhasil dibuka', 'success'); 
                    });
                    window.addEventListener('session-closed', () => { 
                        this.showCloseSession = false; 
                        this.activePage = 'kasir'; 
                        this.clearCart(true); 
                        localStorage.removeItem('pos_active_cart'); 
                        this.broadcastCrossTabSync('session-closed');
                        this.showToast('Sesi kasir berhasil ditutup', 'success'); 
                    });
                    window.addEventListener('petty-cash-saved', () => { 
                        this.showPettyCashModal = false; 
                        this.broadcastCrossTabSync('petty-cash-saved');
                    });
                    window.addEventListener('require-supervisor-pin', (e) => {
                        const data = e.detail[0] || e.detail;
                        if (data.actionType === 'petty_cash_limit') {
                            this.pettyCashLimitMessage = data.message || 'Pengeluaran kas melebihi limit mandiri kasir.';
                            this.showPettyCashLimitConfirmModal = true;
                        } else {
                            this.requestSupervisorAuth(data.message || 'Verifikasi PIN Supervisor Dibutuhkan', () => {
                                if (data.actionType === 'manual_drawer') {
                                    $wire.openManualDrawer(this.selectedSupervisorId, this.supervisorPinInput, 'Buka Laci Manual (Authorized)');
                                } else if (data.actionType === 'out_of_hours_shift' || data.actionType === 'takeover_other_shift') {
                                    $wire.openSession(this.selectedSupervisorId, this.supervisorPinInput);
                                }
                            });
                        }
                    });
                    window.addEventListener('trigger-cash-drawer', (e) => {
                        const reason = (e.detail && e.detail[0] && e.detail[0].reason) ? e.detail[0].reason : 'Buka Laci Kasir';
                        this.kickEscPosDrawer(reason);
                    });
                    window.addEventListener('pin-created', () => { this.showToast('PIN POS 6-digit berhasil dibuat!', 'success'); });
                    window.addEventListener('pin-changed', () => { this.showChangePinModal = false; this.showToast('PIN POS berhasil diperbarui', 'success'); });
                    window.addEventListener('supervisor-authorized', () => {
                        this.showSupervisorPinModal = false;
                        this.showToast('Otorisasi Supervisor Berhasil', 'success');
                        if (this.pendingSupervisorCallback) {
                            const cb = this.pendingSupervisorCallback;
                            this.pendingSupervisorCallback = null;
                            cb();
                        }
                    });
                    window.addEventListener('supervisor-auth-failed', (e) => {
                        this.supervisorErrorMessage = e.detail[0].message || 'PIN Supervisor Salah!';
                        this.showToast(this.supervisorErrorMessage, 'error');
                    });
                    window.addEventListener('order-voided', () => {
                        this.showVoidModal = false;
                        this.broadcastCrossTabSync('order-voided');
                    });
                    window.addEventListener('return-success', () => {
                        this.broadcastCrossTabSync('return-success');
                    });
                    this.startAutoLockChecker();
                    window.addEventListener('checkout-success', (e) => {
                        this.isProcessing = false;
                        this.showCheckoutModal = false;
                        this.previewReceiptData = {
                            title: (e.detail[0].cash_change > 0 ? 'Pembayaran Berhasil' : 'Struk Transaksi'),
                            order_id: e.detail[0].order_id,
                            orderNumber: e.detail[0].order_number,
                            cashChange: e.detail[0].cash_change,
                            text: e.detail[0].receipt_text,
                            base64: e.detail[0].base64,
                            has_returns: false,
                            return_id: null,
                            return_number: null,
                            return_text: '',
                            return_base64: '',
                            activeTab: 'sales'
                        };
                        if (e.detail[0].allPosCustomers) {
                            this.allPosCustomers = e.detail[0].allPosCustomers;
                            if (this.activeCustomerLoyalty) {
                                const updatedCust = this.allPosCustomers.find(c => c.id === this.activeCustomerLoyalty.id || (c.phone && c.phone === this.activeCustomerLoyalty.phone));
                                if (updatedCust) {
                                    this.activeCustomerLoyalty = updatedCust;
                                }
                            }
                        }
                        this._openReceiptOrPrinterModal();
                        this.clearCart(true);
                        this.broadcastCrossTabSync('checkout-success');
                        this.showToast('Pembayaran Berhasil! Kembalian: Rp ' + this.formatMoney(e.detail[0].cash_change), 'success');
                        
                        if (this.autoPrintReceipt) {
                            setTimeout(() => {
                                this.printBase64(this.previewReceiptData.base64, this.previewReceiptData.order_id);
                            }, 500);
                        }
                    });
                    window.addEventListener('print-receipt', (e) => {
                        this.isProcessing = false;
                        const detail = (e.detail && e.detail[0]) ? e.detail[0] : (e.detail || {});
                        const b64 = detail.base64;
                        const orderId = detail.order_id || null;
                        const orderNumber = detail.order_number || 'Struk Transaksi';
                        const text = detail.text || '';
                        const cashChange = detail.cash_change || 0;
                        const title = detail.title || (cashChange > 0 ? 'Pembayaran Berhasil' : 'Pratinjau Struk Cetak');

                        if (b64) {
                            this.previewReceiptData = {
                                title: title,
                                order_id: orderId,
                                orderNumber: orderNumber,
                                cashChange: cashChange,
                                text: text,
                                base64: b64,
                                has_returns: detail.has_returns || false,
                                return_id: detail.return_id || null,
                                return_number: detail.return_number || null,
                                return_text: detail.return_text || '',
                                return_base64: detail.return_base64 || '',
                                activeTab: 'sales'
                            };
                            this._openReceiptOrPrinterModal();
                        }
                    });
                    window.addEventListener('print-z-report', (e) => {
                        const detail = (e.detail && e.detail[0]) ? e.detail[0] : (e.detail || {});
                        const b64 = detail.base64;
                        const text = detail.text || '';
                        const title = detail.title || 'Laporan Laci Kasir (Z-Report)';
                        const orderNumber = detail.order_number || 'Tutup Shift';

                        if (b64) {
                            this.previewReceiptData = {
                                title: title,
                                order_id: null,
                                orderNumber: orderNumber,
                                cashChange: 0,
                                text: text,
                                base64: b64,
                                isCloseSession: true,
                                activeTab: 'sales'
                            };
                            this.showCloseSession = false;
                            this._openReceiptOrPrinterModal();

                            // Auto-print Z-Report saat tutup shift (jika opsi auto-print aktif)
                            if (this.autoPrintReceipt) {
                                setTimeout(() => {
                                    this.printBase64(b64, null);
                                    this.showToast('Mencetak struk tutup shift...', 'info');
                                }, 500);
                            }
                        }
                    });

                    // Auto-reconnect printer jika sebelumnya pernah terhubung
                    setTimeout(() => {
                        this.autoConnectPrinter();
                    }, 800);
                },

                saveHeldCarts() {
                    localStorage.setItem('pos_held_carts', JSON.stringify(this.heldCarts));
                },

                saveActiveCart() {
                    if (this.cart && this.cart.length > 0) {
                        const payload = {
                            cart: this.cart,
                            activeVoucher: this.activeVoucher,
                            manualDiscountType: this.manualDiscountType,
                            manualDiscountValue: this.manualDiscountValue,
                            customerName: this.customerName,
                            customerPhone: this.customerPhone,
                            isReserved: this.isReserved,
                            pickupDate: this.pickupDate,
                            loyaltyRedeemStamps: this.loyaltyRedeemStamps,
                            activeCustomerLoyalty: this.activeCustomerLoyalty,
                        };
                        localStorage.setItem('pos_active_cart', JSON.stringify(payload));
                    } else {
                        localStorage.removeItem('pos_active_cart');
                    }
                },

                loadActiveCart() {
                    const stored = localStorage.getItem('pos_active_cart');
                    if (stored) {
                        try {
                            const payload = JSON.parse(stored);
                            if (payload && Array.isArray(payload.cart) && payload.cart.length > 0) {
                                this.cart = payload.cart;
                                this.activeVoucher = payload.activeVoucher || null;
                                this.manualDiscountType = payload.manualDiscountType || 'rp';
                                this.manualDiscountValue = payload.manualDiscountValue || 0;
                                this.customerName = payload.customerName || '';
                                this.customerPhone = payload.customerPhone || '';
                                this.isReserved = payload.isReserved || false;
                                this.pickupDate = payload.pickupDate || '';
                                this.loyaltyRedeemStamps = payload.loyaltyRedeemStamps || 0;
                                this.activeCustomerLoyalty = payload.activeCustomerLoyalty || null;
                            }
                        } catch(e) {}
                    }
                },

                holdCart() {
                    if (this.cart.length === 0) return;
                    
                    let defaultName = this.customerName;
                    if (!defaultName) {
                        const firstItem = this.cart[0].name;
                        const otherItems = this.cart.length > 1 ? ` + ${this.cart.length - 1} item` : '';
                        defaultName = `${firstItem}${otherItems}`;
                    }

                    const holdId = 'HOLD-' + Date.now();
                    const newHold = {
                        id: holdId,
                        time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                        customerName: this.customerName || 'Umum',
                        customerPhone: this.customerPhone || '',
                        isReserved: this.isReserved,
                        pickupDate: this.pickupDate,
                        cart: JSON.parse(JSON.stringify(this.cart)),
                        activeVoucher: this.activeVoucher,
                        manualDiscountType: this.manualDiscountType,
                        manualDiscountValue: this.manualDiscountValue,
                        total: this.subtotal
                    };

                    this.heldCarts.unshift(newHold);
                    this.saveHeldCarts();

                    // Sync ke database toko agar kasir lain & shift berikutnya bisa akses!
                    @this.call('saveHeldCartToDb', JSON.stringify({
                        hold_id: holdId,
                        customer_name: this.customerName || 'Umum',
                        customer_phone: this.customerPhone || '',
                        cart_data: newHold,
                        total: this.subtotal
                    }));

                    this.clearCart(true);
                    this.showToast('Pesanan berhasil dimasukkan ke antrean toko', 'success');
                },

                resumeCart(id, holdItem = null) {
                    const doResume = (holdData) => {
                        const updatedCart = (holdData.cart || []).map(item => {
                            if (!item.product_id) return item;
                            const p = (this.allProducts || []).find(prod => prod.id === item.product_id);
                            let currentStock = item.stock;
                            if (p) {
                                if (item.product_variant_id && p.variants) {
                                    const v = p.variants.find(varItem => varItem.id === item.product_variant_id);
                                    if (v) currentStock = v.stock;
                                } else {
                                    currentStock = p.stock;
                                }
                            }
                            const itemStock = parseInt(currentStock !== undefined && currentStock !== null ? currentStock : 999999);
                            const clampedQty = Math.min(item.quantity, itemStock);
                            if (clampedQty < item.quantity) {
                                this.showToast(`Qty ${item.name} disesuaikan ke ${clampedQty} pcs sesuai stok terbaru.`, 'warning');
                            }
                            return {
                                ...item,
                                stock: itemStock,
                                quantity: clampedQty
                            };
                        });

                        this.cart = updatedCart;
                        this.activeVoucher = holdData.activeVoucher || null;
                        this.manualDiscountType = holdData.manualDiscountType || 'rp';
                        this.manualDiscountValue = holdData.manualDiscountValue || 0;
                        this.customerName = holdData.customerName || '';
                        this.customerPhone = holdData.customerPhone || '';
                        this.isReserved = holdData.isReserved || false;
                        this.pickupDate = holdData.pickupDate || '';
                        
                        this.heldCarts = this.heldCarts.filter(h => h.id !== id);
                        this.saveHeldCarts();
                        @this.call('deleteHeldCartFromDb', id);

                        this.showHoldModal = false;
                        this.showToast('Antrean berhasil dilanjutkan', 'success');
                    };

                    const targetHold = holdItem || this.heldCarts.find(h => h.id === id);
                    if (!targetHold) return;

                    if (this.cart.length > 0) {
                        this.askConfirm(
                            'Tumpuk Keranjang?', 
                            'Keranjang Anda saat ini tidak kosong. Jika dilanjutkan, belanjaan saat ini akan terganti oleh antrean yang dipanggil. Lanjutkan?', 
                            () => doResume(targetHold)
                        );
                    } else {
                        doResume(targetHold);
                    }
                },

                deleteHeldCart(id) {
                    this.askConfirm(
                        'Hapus Antrean?', 
                        'Apakah Anda yakin ingin menghapus antrean ini? Data tidak bisa dikembalikan.', 
                        () => {
                            this.heldCarts = this.heldCarts.filter(h => h.id !== id);
                            this.saveHeldCarts();
                            @this.call('deleteHeldCartFromDb', id);
                        }
                    );
                },

                async saveCustomProductModal() {
                    if (!this.customProductName.trim()) {
                        this.showToast('Nama produk kustom wajib diisi.', 'error');
                        return;
                    }
                    if (!this.customPrice || this.customPrice <= 0) {
                        this.showToast('Harga jual nego harus lebih dari 0.', 'error');
                        return;
                    }
                    if (!this.customQty || this.customQty <= 0) {
                        this.showToast('Jumlah qty stok harus minimal 1.', 'error');
                        return;
                    }

                    const processSave = async () => {
                        if (this.customSaveToCatalog) {
                            // Simpan langsung ke database Katalog POS via Livewire!
                            const res = await @this.call('saveCustomProduct', {
                                name: this.customProductName.trim(),
                                purchase_price: parseFloat(this.customPurchasePrice || 0),
                                normal_price: parseFloat(this.customNormalPrice || 0),
                                price: parseFloat(this.customPrice || 0),
                                quantity: parseInt(this.customQty || 1)
                            });

                            if (res && this.customAddToCart) {
                                this.triggerCartBounce();
                                this.cart.unshift({
                                    is_custom: false,
                                    product_id: res.id,
                                    product_variant_id: null,
                                    name: res.name,
                                    price: res.price,
                                    purchase_price: res.purchase_price,
                                    quantity: 1
                                });
                                this.calculateVoucherDiscount();
                            }
                        } else if (this.customAddToCart) {
                            this.pushCustomItemToCart();
                            return;
                        }

                        this.customProductName = '';
                        this.customPrice = '';
                        this.customNormalPrice = '';
                        this.customPurchasePrice = '';
                        this.customQty = 1;
                        this.customSaveToCatalog = true;
                        this.customAddToCart = false;
                        this.showCustomProductModal = false;
                    };

                    // Proteksi Margin: jika harga nego < harga modal (HPP), minta otorisasi PIN Supervisor
                    if (this.customPurchasePrice > 0 && this.customPrice < this.customPurchasePrice) {
                        this.requestSupervisorAuth(
                            'Otorisasi Produk di Bawah Modal (HPP): Harga jual (Rp ' + this.formatMoney(this.customPrice) + ') berada di bawah HPP (Rp ' + this.formatMoney(this.customPurchasePrice) + ').',
                            () => {
                                processSave();
                            }
                        );
                        return;
                    }

                    processSave();
                },

                getCartItemQty(productId, variantId) {
                    return (this.cart || []).reduce((sum, item) => {
                        if (item.product_id === productId && item.product_variant_id === variantId) {
                            return sum + (parseInt(item.quantity) || 0);
                        }
                        return sum;
                    }, 0);
                },

                confirmCustomNegoAddToCart() {
                    if (!this.customNegoProduct || !this.customNegoProduct.id) {
                        this.showCustomNegoModal = false;
                        return;
                    }

                    const p = this.customNegoProduct;
                    const negoPrice = parseFloat(p.negoPrice || 0);
                    const qty = parseInt(p.qty || 1);
                    const stock = parseInt(p.stock !== undefined ? p.stock : 999999);

                    if (isNaN(negoPrice) || negoPrice < 0) {
                        this.showToast('Harga nego tidak valid.', 'error');
                        return;
                    }

                    const inCartQty = this.getCartItemQty(p.id, null);
                    if (inCartQty + qty > stock) {
                        const avail = Math.max(0, stock - inCartQty);
                        if (avail === 0) {
                            this.showToast(`Stok ${p.name} sudah habis terpakai di keranjang.`, 'error');
                        } else {
                            this.showToast(`Hanya bisa menambah maksimal ${avail} pcs lagi. (Stok: ${stock}, Di keranjang: ${inCartQty})`, 'warning');
                        }
                        return;
                    }

                    const purchasePrice = parseFloat(p.purchasePrice || 0);
                    const processAddToCart = () => {
                        this.addToCart(p.id, null, p.name, negoPrice, qty, p.originalPrice, purchasePrice, stock);
                        this.showCustomNegoModal = false;
                        this.showToast(`Produk ${p.name} berhasil ditambahkan ke keranjang!`, 'success');
                    };

                    if (purchasePrice > 0 && negoPrice < purchasePrice) {
                        this.requestSupervisorAuth(
                            `Otorisasi Nego Harga (${p.name}): Harga nego (Rp ${this.formatMoney(negoPrice)}) di bawah HPP (Rp ${this.formatMoney(purchasePrice)}).`,
                            () => {
                                processAddToCart();
                            }
                        );
                    } else {
                        processAddToCart();
                    }
                },

                addCustomProductToCart() {
                    this.saveCustomProductModal();
                },

                pushCustomItemToCart() {
                    this.triggerCartBounce();
                    this.cart.unshift({
                        is_custom: true,
                        product_id: null,
                        product_variant_id: null,
                        name: this.customProductName.trim(),
                        price: parseFloat(this.customPrice),
                        purchase_price: parseFloat(this.customPurchasePrice || 0),
                        quantity: parseInt(this.customQty),
                        save_to_catalog: this.customSaveToCatalog
                    });

                    this.customProductName = '';
                    this.customPrice = '';
                    this.customNormalPrice = '';
                    this.customPurchasePrice = '';
                    this.customQty = 1;
                    this.customSaveToCatalog = false;
                    this.showCustomProductModal = false;
                    this.showToast('Produk kustom berhasil ditambahkan ke keranjang!', 'success');
                    this.calculateVoucherDiscount();
                },

                addProduct(id, name, price, hasVariants, variants = null, defaultImage = null, isCustom = false, purchasePrice = 0, originalPrice = null, promoPrice = null, stock = 999999) {
                    if (hasVariants && variants) {
                        this.initVariantSelector(id, name, price, variants, defaultImage, originalPrice);
                        return;
                    }

                    this.customNegoProduct = {
                        id: id,
                        name: name,
                        originalPrice: originalPrice !== null ? parseFloat(originalPrice) : price,
                        promoPrice: (promoPrice !== null && !isNaN(promoPrice)) ? parseFloat(promoPrice) : null,
                        purchasePrice: parseFloat(purchasePrice || 0),
                        negoPrice: price,
                        qty: 1,
                        defaultImage: defaultImage || '',
                        stock: parseInt(stock !== null && stock !== undefined ? stock : 999999)
                    };
                    this.showCustomNegoModal = true;
                },

                initVariantSelector(id, name, price, variants, defaultImage, originalPrice = null) {
                    this.currentProductForVariant = { 
                        id, 
                        name, 
                        price, 
                        originalPrice: originalPrice !== null ? parseFloat(originalPrice) : price, 
                        defaultImage: defaultImage || '' 
                    };
                    this.currentVariants = variants || [];
                    this.variantShowOutOfStock = false;
                    this.selectedVariantAttributes = {};
                    this.variantAttributeSearch = {};

                    // Extract ALL attribute groups (INCLUDE ALL ATTRIBUTES: Bahan, Ukuran, Warna, etc.)
                    const attrMap = {};
                    this.currentVariants.forEach(v => {
                        if (v.attributes && Array.isArray(v.attributes)) {
                            v.attributes.forEach(attr => {
                                if (attr.attr_name) {
                                    if (!attrMap[attr.attr_name]) {
                                        attrMap[attr.attr_name] = {
                                            name: attr.attr_name,
                                            slug: attr.attr_slug,
                                            options: new Set()
                                        };
                                    }
                                    if (attr.value) attrMap[attr.attr_name].options.add(attr.value);
                                }
                            });
                        }
                    });

                    // Order attribute groups: Ukuran/Size first, then others
                    const sortedGroupNames = Object.keys(attrMap).sort((a, b) => {
                        const aL = a.toLowerCase();
                        const bL = b.toLowerCase();
                        if (aL.includes('ukuran') || aL.includes('size')) return -1;
                        if (bL.includes('ukuran') || bL.includes('size')) return 1;
                        return 0;
                    });

                    this.variantAttributeGroups = sortedGroupNames.map(attrName => {
                        const opts = Array.from(attrMap[attrName].options);
                        this.variantAttributeSearch[attrName] = '';

                        // AUTO-SELECT if attribute only has 1 option for this product!
                        if (opts.length === 1) {
                            this.selectedVariantAttributes[attrName] = opts[0];
                        }

                        return {
                            name: attrMap[attrName].name,
                            slug: attrMap[attrName].slug,
                            optionsArray: opts
                        };
                    });

                    this.showVariantModal = true;
                },

                toggleVariantAttributeOption(attrName, optionValue) {
                    if (this.selectedVariantAttributes[attrName] === optionValue) {
                        const group = (this.variantAttributeGroups || []).find(g => g.name === attrName);
                        if (group && group.optionsArray.length === 1) return; // single option stays selected
                        delete this.selectedVariantAttributes[attrName];
                        this.selectedVariantAttributes = { ...this.selectedVariantAttributes };
                    } else {
                        this.selectedVariantAttributes[attrName] = optionValue;
                        this.selectedVariantAttributes = { ...this.selectedVariantAttributes };
                    }
                },

                get selectedMatchedVariant() {
                    if (!this.currentVariants || this.currentVariants.length === 0) return null;
                    if (!this.variantAttributeGroups || this.variantAttributeGroups.length === 0) return null;

                    return this.currentVariants.find(v => {
                        if (!v.attributes || !Array.isArray(v.attributes)) return false;
                        return this.variantAttributeGroups.every(g => {
                            const selectedVal = this.selectedVariantAttributes[g.name];
                            if (!selectedVal) return false;
                            return v.attributes.some(a => a.attr_name === g.name && a.value === selectedVal);
                        });
                    }) || null;
                },

                get isAllVariantAttributesSelected() {
                    if (!this.variantAttributeGroups || this.variantAttributeGroups.length === 0) return true;
                    return this.variantAttributeGroups.every(g => !!this.selectedVariantAttributes[g.name]);
                },

                get selectedVariantPreviewImage() {
                    const matched = this.selectedMatchedVariant;
                    if (matched && matched.image) return matched.image;
                    return this.currentProductForVariant ? this.currentProductForVariant.defaultImage : '';
                },

                getOptionStockForAttribute(attrName, optionValue) {
                    const matchingVars = (this.currentVariants || []).filter(v => {
                        if (!v.attributes || !Array.isArray(v.attributes)) return false;
                        for (const g of (this.variantAttributeGroups || [])) {
                            if (g.name === attrName) continue;
                            const selectedVal = this.selectedVariantAttributes[g.name];
                            if (selectedVal) {
                                const match = v.attributes.some(a => a.attr_name === g.name && a.value === selectedVal);
                                if (!match) return false;
                            }
                        }
                        return v.attributes.some(a => a.attr_name === attrName && a.value === optionValue);
                    });

                    return matchingVars.reduce((sum, v) => sum + Math.max(0, v.stock), 0);
                },

                confirmAddSelectedVariantToCart() {
                    const matched = this.selectedMatchedVariant;
                    if (!matched) {
                        this.showToast('Silakan pilih semua opsi atribut varian terlebih dahulu.', 'error');
                        return;
                    }
                    if (matched.stock <= 0) {
                        this.showToast('Stok varian terpilih sedang habis.', 'error');
                        return;
                    }
                    const inCartQty = this.getCartItemQty(this.currentProductForVariant.id, matched.id);
                    if (inCartQty + 1 > matched.stock) {
                        this.showToast(`Stok varian ${matched.name} tidak mencukupi! (Stok: ${matched.stock}, Di keranjang: ${inCartQty})`, 'warning');
                        return;
                    }
                    this.addVariantToCart(matched.id, matched.name, matched.price, matched.original_price, matched.stock);
                },

                addVariantToCart(variantId, variantName, variantPrice, variantOriginalPrice = null, variantStock = 999999) {
                    const fullName = this.currentProductForVariant.name + ' - ' + variantName;
                    const finalOrigPrice = variantOriginalPrice !== null ? parseFloat(variantOriginalPrice) : (this.currentProductForVariant ? this.currentProductForVariant.originalPrice : variantPrice);
                    this.addToCart(this.currentProductForVariant.id, variantId, fullName, variantPrice, 1, finalOrigPrice, 0, variantStock);
                    this.showVariantModal = false;
                },

                saveAutoPrintSettings() {
                    localStorage.setItem('pos_auto_print', this.autoPrintReceipt);
                },

                /**
                 * Wrapper kasir-friendly untuk koneksi Bluetooth.
                 * Urutan: Print Agent (Classic BT) → Web Bluetooth BLE (fallback)
                 * Kasir tidak perlu tahu mekanisme di balik ini.
                 */
                /**
                 * Koneksi manual dengan MAC address atau COM port yang diisi user.
                 * Simpan ke localStorage (browser) DAN kirim ke Print Agent (config file).
                 */
                async connectManual() {
                    const addr = this.manualPrinterAddress.trim();
                    if (!addr || this.isConnectingBT) return;

                    this.isConnectingBT = true;
                    try {
                        // Simpan ke localStorage supaya pre-fill next time
                        localStorage.setItem('pos_printer_manual_addr', addr);

                        // Kirim ke Print Agent
                        const result = await new Promise((resolve) => {
                            const ws = new WebSocket('ws://127.0.0.1:8765');

                            const timeout = setTimeout(() => {
                                ws.close();
                                resolve({ success: false, reason: 'timeout' });
                            }, 15000); // Lebih lama karena harus bind rfcomm di Linux

                            ws.onopen = () => {
                                // Kirim config ke Print Agent
                                // Print Agent akan detect sendiri apakah ini MAC atau COM port
                                ws.send(JSON.stringify({ type: 'config', value: addr }));
                            };

                            ws.onmessage = (event) => {
                                try {
                                    const msg = JSON.parse(event.data);
                                    if (msg.type === 'scanning' || msg.type === 'status') return;

                                    if (msg.type === 'scan_result' || msg.type === 'config_result') {
                                        clearTimeout(timeout);
                                        if (msg.found || msg.connected) {
                                            this.bridgeSocket = ws;
                                            this.printerConnectionMethod = 'bridge';
                                            this.printerType = 'bridge';
                                            this.printerDeviceName = msg.name || addr;
                                            this.printerConnected = true;

                                            localStorage.setItem('pos_printer_name', this.printerDeviceName);
                                            localStorage.setItem('pos_printer_type', 'bridge');

                                            ws.onclose = () => {
                                                this.printerConnected = false;
                                                this.bridgeSocket = null;
                                                this.printerConnectionMethod = null;
                                                this.showToast('Koneksi printer terputus.', 'error');
                                            };
                                            ws.onmessage = (e) => {
                                                try {
                                                    const m = JSON.parse(e.data);
                                                    if (m.type === 'error') this.showToast('Error Printer: ' + m.message, 'error');
                                                    // Sync status dari Print Agent (misal: printer putus karena EIO)
                                                    if (m.type === 'status') {
                                                        const wasConnected = this.printerConnected;
                                                        this.printerConnected = m.connected === true;
                                                        if (wasConnected && !this.printerConnected) {
                                                            this.showToast('Koneksi printer terputus. Klik Sambungkan untuk menghubungkan kembali.', 'error');
                                                        }
                                                    }
                                                } catch (ex) {}
                                            };

                                            resolve({ success: true });
                                        } else {
                                            ws.close();
                                            resolve({ success: false, message: msg.message });
                                        }
                                    }

                                    if (msg.type === 'error') {
                                        clearTimeout(timeout);
                                        ws.close();
                                        resolve({ success: false, message: msg.message });
                                    }
                                } catch (e) {}
                            };

                            ws.onerror = () => {
                                clearTimeout(timeout);
                                resolve({ success: false, reason: 'no_agent', message: 'Print Agent tidak berjalan di komputer ini.' });
                            };
                        });

                        if (result.success) {
                            this.showToast('Printer ' + this.printerDeviceName + ' berhasil terhubung!', 'success');
                            this._afterPrinterConnected();
                        } else {
                            const msg = result.message || 'Gagal terhubung. Pastikan alamat benar dan printer menyala.';
                            this.showToast(msg, 'error');
                        }

                    } catch (err) {
                        console.error('[Manual Connect]', err);
                        this.showToast('Terjadi kesalahan saat menghubungkan.', 'error');
                    } finally {
                        this.isConnectingBT = false;
                    }
                },

                /**
                 * Muat daftar perangkat Bluetooth yang sudah di-pair di sistem
                 * via Print Agent → bluetoothctl devices
                 */
                async loadPairedDevices() {
                    this.loadingDevices = true;
                    this.pairedDevices = [];
                    try {
                        await new Promise((resolve, reject) => {
                            const ws = new WebSocket('ws://127.0.0.1:8765');
                            const timeout = setTimeout(() => { ws.close(); resolve(); }, 5000);

                            ws.onopen = () => { ws.send(JSON.stringify({ type: 'list_devices' })); };
                            ws.onmessage = (event) => {
                                try {
                                    const msg = JSON.parse(event.data);
                                    if (msg.type === 'devices_list') {
                                        clearTimeout(timeout);
                                        this.pairedDevices = msg.devices || [];
                                        ws.close();
                                        resolve();
                                    }
                                } catch (e) {}
                            };
                            ws.onerror = () => { clearTimeout(timeout); resolve(); };
                        });
                    } catch (e) {}
                    this.loadingDevices = false;
                },

                /**
                 * Hubungkan ke printer Bluetooth Classic menggunakan MAC address yang dipilih dari daftar
                 */
                async connectWithMac(mac, name) {
                    if (this.isConnectingBT) return;
                    this.isConnectingBT = true;
                    this.showToast('Menghubungkan ke ' + name + '...', 'info');
                    try {
                        await new Promise((resolve, reject) => {
                            const ws = new WebSocket('ws://127.0.0.1:8765');
                            const timeout = setTimeout(() => {
                                ws.close();
                                reject(new Error('Timeout menghubungkan ke ' + name));
                            }, 30000); // rfcomm bind butuh waktu lebih lama

                            ws.onopen = () => {
                                ws.send(JSON.stringify({ type: 'config', value: mac, target: 'bluetooth' }));
                            };

                            ws.onmessage = (event) => {
                                try {
                                    const msg = JSON.parse(event.data);
                                    if (msg.type === 'scanning') return; // masih proses, tunggu

                                    if (msg.type === 'config_result') {
                                        clearTimeout(timeout);
                                        if (msg.found || msg.connected) {
                                            this.bridgeSocket = ws;
                                            this.printerConnectionMethod = 'bridge';
                                            this.printerType = 'bridge';
                                            this.printerDeviceName = name;
                                            this.printerConnected = true;

                                            localStorage.setItem('pos_printer_name', name);
                                            localStorage.setItem('pos_printer_type', 'bridge');
                                            localStorage.setItem('pos_printer_manual_addr', mac);

                                            ws.onclose = () => {
                                                this.printerConnected = false;
                                                this.bridgeSocket = null;
                                                this.printerConnectionMethod = null;
                                                this.showToast('Koneksi printer ' + name + ' terputus.', 'error');
                                            };
                                            ws.onmessage = (e) => {
                                                try {
                                                    const m = JSON.parse(e.data);
                                                    if (m.type === 'status') {
                                                        const wasConnected = this.printerConnected;
                                                        this.printerConnected = m.connected === true;
                                                        if (wasConnected && !this.printerConnected) {
                                                            this.showToast('Koneksi printer ' + name + ' terputus.', 'error');
                                                        }
                                                    }
                                                } catch (ex) {}
                                            };

                                            resolve();
                                        } else {
                                            ws.close();
                                            reject(new Error(msg.message || 'Printer tidak merespons. Pastikan printer menyala.'));
                                        }
                                    }
                                } catch (e) {}
                            };

                            ws.onerror = () => {
                                clearTimeout(timeout);
                                reject(new Error('Tidak bisa terhubung ke Print Agent.'));
                            };
                        });

                        this.showToast('Printer ' + name + ' berhasil terhubung!', 'success');
                        this._afterPrinterConnected();
                    } catch (err) {
                        this.showToast(err.message, 'error');
                    } finally {
                        this.isConnectingBT = false;
                    }
                },

                async connectBluetooth() {

                    if (this.isConnectingBT) return;
                    this.isConnectingBT = true;
                    try {
                        // Step 1: Coba Print Agent lokal (untuk Classic BT seperti RPP02N)
                        const bridgeResult = await new Promise((resolve) => {
                            const ws = new WebSocket('ws://127.0.0.1:8765');
                            const timeout = setTimeout(() => {
                                ws.close();
                                resolve({ success: false, reason: 'timeout' });
                            }, 3000);

                            ws.onopen = () => {
                                // Minta Print Agent scan printer Bluetooth spesifik
                                ws.send(JSON.stringify({ type: 'scan', target: 'bluetooth' }));
                            };

                            ws.onmessage = (event) => {
                                try {
                                    const msg = JSON.parse(event.data);
                                    if (msg.type === 'scanning') {
                                        // Print Agent sedang scanning, biarkan loading terus
                                        return;
                                    }
                                    if (msg.type === 'scan_result') {
                                        clearTimeout(timeout);
                                        if (msg.found) {
                                            // Printer ditemukan — simpan bridge untuk print
                                            this.bridgeSocket = ws;
                                            this.printerConnectionMethod = 'bridge';
                                            this.printerType = 'bridge';
                                            this.printerDeviceName = msg.name || 'Printer';
                                            this.printerConnected = true;

                                            localStorage.setItem('pos_printer_name', this.printerDeviceName);
                                            localStorage.setItem('pos_printer_type', 'bridge');
                                            // Hanya simpan MAC address, BUKAN rfcomm port
                                            // agar tidak campur dengan alamat USB printer
                                            if (msg.mac) {
                                                localStorage.setItem('pos_printer_manual_addr', msg.mac);
                                            } else {
                                                localStorage.removeItem('pos_printer_manual_addr');
                                            }

                                            ws.onclose = () => {
                                                this.printerConnected = false;
                                                this.bridgeSocket = null;
                                                this.printerConnectionMethod = null;
                                                this.showToast('Koneksi printer terputus.', 'error');
                                            };

                                            ws.onmessage = (e) => {
                                                try {
                                                    const m = JSON.parse(e.data);
                                                    if (m.type === 'error') this.showToast('Error Printer: ' + m.message, 'error');
                                                    // Sync status dari Print Agent (misal: printer putus karena EIO)
                                                    if (m.type === 'status') {
                                                        const wasConnected = this.printerConnected;
                                                        this.printerConnected = m.connected === true;
                                                        if (wasConnected && !this.printerConnected) {
                                                            this.showToast('Koneksi printer terputus. Klik Sambungkan untuk menghubungkan kembali.', 'error');
                                                        }
                                                    }
                                                } catch (ex) {}
                                            };

                                            resolve({ success: true });
                                        } else {
                                            // Print Agent jalan tapi printer tidak ditemukan
                                            ws.close();
                                            resolve({ success: false, reason: 'not_found', message: msg.message });
                                        }
                                    } else if (msg.type === 'status') {
                                        // Abaikan status awal, tunggu scan_result
                                    }
                                } catch (e) {}
                            };

                            ws.onerror = () => {
                                clearTimeout(timeout);
                                resolve({ success: false, reason: 'no_agent' });
                            };
                        });

                        if (bridgeResult.success) {
                            this.showToast('Printer ' + this.printerDeviceName + ' berhasil terhubung!', 'success');
                            this._afterPrinterConnected();
                            return;
                        }

                        if (bridgeResult.reason === 'not_found') {
                            // Print Agent jalan tapi tidak temukan printer
                            this.showToast(
                                'Printer tidak ditemukan. Pastikan printer sudah dipasangkan di Bluetooth Settings dan dalam keadaan menyala.',
                                'error'
                            );
                            return;
                        }

                        // Step 2: Print Agent tidak ada — fallback ke Web Bluetooth BLE
                        await this.scanAndConnectWebBluetooth();

                    } catch (err) {
                        console.error('[BT Wrapper]', err);
                        this.showToast('Gagal menghubungkan printer. Pastikan printer menyala.', 'error');
                    } finally {
                        this.isConnectingBT = false;
                    }
                },

                async scanAndConnectWebBluetooth() {
                    if (!navigator.bluetooth) {
                        this.showToast('Browser Anda tidak mendukung koneksi Bluetooth langsung. Gunakan Chrome atau Edge.', 'error');
                        return;
                    }
                    this.showToast('Memindai printer Bluetooth di sekitar...', 'info');
                    try {
                        const knownServices = [
                            '000018f0-0000-1000-8000-00805f9b34fb',
                            '0000e025-0000-1000-8000-00805f9b34fb',
                            '0000ff00-0000-1000-8000-00805f9b34fb',
                            '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                            '00001101-0000-1000-8000-00805f9b34fb',
                        ];
                        const device = await navigator.bluetooth.requestDevice({
                            acceptAllDevices: true,
                            optionalServices: knownServices
                        });
                        
                        this.showToast('Menghubungkan ke ' + (device.name || 'Printer') + '...', 'info');
                        const server = await device.gatt.connect();
                        
                        let characteristic = null;
                        const services = await server.getPrimaryServices();
                        for (const service of services) {
                            try {
                                const characteristics = await service.getCharacteristics();
                                for (const c of characteristics) {
                                    if (c.properties.write || c.properties.writeWithoutResponse) {
                                        characteristic = c;
                                        break;
                                    }
                                }
                                if (characteristic) break;
                            } catch (e) {
                                console.warn('Skipping service:', service.uuid, e);
                            }
                        }
                        
                        if (!characteristic) {
                            throw new Error('Tidak ditemukan saluran penulisan data (writable characteristic) pada printer ini.');
                        }
                        
                        this.printerDevice = device;
                        this.printerCharacteristic = characteristic;
                        this.printerConnected = true;
                        this.printerConnectionMethod = 'ble';
                        this.printerDeviceName = device.name || 'Printer Bluetooth';
                        this.printerType = 'bluetooth';
                        
                        localStorage.setItem('pos_printer_name', this.printerDeviceName);
                        localStorage.setItem('pos_printer_type', 'bluetooth');
                        
                        this.showToast('Printer Bluetooth ' + this.printerDeviceName + ' terhubung!', 'success');
                        this._afterPrinterConnected();
                        
                        device.addEventListener('gattserverdisconnected', () => {
                            this.printerConnected = false;
                            this.printerConnectionMethod = null;
                            this.showToast('Koneksi printer terputus.', 'error');
                        });
                    } catch (error) {
                        console.error(error);
                        if (error.name === 'NotFoundError' || error.message.includes('cancelled')) {
                            this.showToast('Pencarian printer dibatalkan.', 'error');
                        } else if (error.name === 'SecurityError') {
                            this.showToast('Akses Bluetooth ditolak. Pastikan halaman dibuka via HTTPS.', 'error');
                        } else {
                            this.showToast('Gagal terhubung: ' + error.message, 'error');
                        }
                    }
                },

                async scanAndConnectWebSerial() {
                    if (this.isConnectingBT || this.isConnectingUSB) return;
                    this.isConnectingUSB = true;
                    try {
                        // Step 1: Coba via Print Agent dulu (mendukung /dev/usb/lp0 di Linux & COM port di Windows)
                        const bridgeResult = await new Promise((resolve) => {
                            const ws = new WebSocket('ws://127.0.0.1:8765');
                            const timeout = setTimeout(() => {
                                ws.close();
                                resolve({ success: false, reason: 'timeout' });
                            }, 5000);

                            ws.onopen = () => {
                                const savedAddr = localStorage.getItem('pos_printer_manual_addr');
                                // Hanya gunakan savedAddr jika itu adalah path USB (bukan rfcomm/MAC BT)
                                const isUsbAddr = savedAddr && (
                                    savedAddr.startsWith('/dev/usb/lp') ||
                                    /^COM\d+$/i.test(savedAddr)
                                );
                                if (isUsbAddr) {
                                    ws.send(JSON.stringify({ type: 'config', value: savedAddr, target: 'usb' }));
                                } else {
                                    ws.send(JSON.stringify({ type: 'scan', target: 'usb' }));
                                }
                            };

                            ws.onmessage = (event) => {
                                try {
                                    const msg = JSON.parse(event.data);
                                    if (msg.type === 'scanning' || msg.type === 'status') return;

                                    if (msg.type === 'scan_result' || msg.type === 'config_result') {
                                        clearTimeout(timeout);
                                        if (msg.found || msg.connected) {
                                            this.bridgeSocket = ws;
                                            this.printerConnectionMethod = 'bridge';
                                            this.printerType = 'bridge';
                                            this.printerDeviceName = msg.name || 'Printer USB';
                                            this.printerConnected = true;

                                            localStorage.setItem('pos_printer_name', this.printerDeviceName);
                                            localStorage.setItem('pos_printer_type', 'bridge');
                                            // Simpan path USB untuk auto-reconnect, hapus MAC BT lama
                                            if (msg.serial_port) {
                                                localStorage.setItem('pos_printer_manual_addr', msg.serial_port);
                                            } else {
                                                localStorage.removeItem('pos_printer_manual_addr');
                                            }

                                            ws.onclose = () => {
                                                this.printerConnected = false;
                                                this.bridgeSocket = null;
                                                this.printerConnectionMethod = null;
                                                this.showToast('Koneksi printer terputus.', 'error');
                                            };

                                            resolve({ success: true });
                                        } else {
                                            ws.close();
                                            resolve({ success: false });
                                        }
                                    }
                                } catch (e) {}
                            };

                            ws.onerror = () => {
                                clearTimeout(timeout);
                                resolve({ success: false });
                            };
                        });

                        if (bridgeResult.success) {
                            this.showToast('Printer USB ' + this.printerDeviceName + ' berhasil terhubung!', 'success');
                            this._afterPrinterConnected();
                            return;
                        }

                        // Step 2: Fallback ke Web Serial API jika Print Agent tidak ada
                        if (!navigator.serial) {
                            this.showToast('Gagal terhubung ke Print Agent. Browser juga tidak mendukung Web Serial.', 'error');
                            return;
                        }

                        const port = await navigator.serial.requestPort();
                        await port.open({ baudRate: 9600 });
                        this.printerPort = port;
                        this.printerConnected = true;
                        this.printerConnectionMethod = 'serial';
                        this.printerDeviceName = 'Printer USB / Serial Cable';
                        this.printerType = 'serial';

                        localStorage.setItem('pos_printer_name', this.printerDeviceName);
                        localStorage.setItem('pos_printer_type', 'serial');

                        this.showToast('Printer USB/Serial berhasil terhubung!', 'success');
                        this._afterPrinterConnected();
                    } catch (error) {
                        console.error(error);
                        if (error.name === 'NotFoundError' || error.message.includes('No port selected')) {
                            this.showToast('Tidak ada port USB yang dipilih.', 'error');
                        } else {
                            this.showToast('Gagal terhubung ke USB. Gunakan Koneksi Manual dengan mengisi /dev/usb/lp0 atau COM port.', 'error');
                        }
                    } finally {
                        this.isConnectingUSB = false;
                    }
                },

                async connectToBridge(showNotify = false) {
                    return new Promise((resolve, reject) => {
                        const wsUrl = 'ws://127.0.0.1:8765';
                        const ws = new WebSocket(wsUrl);
                        
                        ws.onopen = () => {
                            this.bridgeSocket = ws;
                            this.printerConnectionMethod = 'bridge';
                            this.printerType = 'bridge';
                            this.printerDeviceName = 'Raabiha Print Agent';
                            
                            ws.onmessage = (event) => {
                                try {
                                    const msg = JSON.parse(event.data);
                                    if (msg.type === 'status') {
                                        this.printerConnected = msg.connected;
                                        if (msg.connected) {
                                            this.showToast('Terhubung via Raabiha Print Agent!', 'success');
                                            this._afterPrinterConnected();
                                            resolve();
                                        } else {
                                            this.showToast('Print Agent berjalan, tetapi printer belum terhubung. Nyalakan printer.', 'warning');
                                            resolve();
                                        }
                                    } else if (msg.type === 'error') {
                                        this.showToast('Error Printer Agent: ' + msg.message, 'error');
                                    } else if (msg.type === 'print_ok') {
                                        console.log('Print success via bridge');
                                    }
                                } catch (e) {}
                            };
                            
                            ws.onclose = () => {
                                this.printerConnected = false;
                                this.bridgeSocket = null;
                                this.printerConnectionMethod = null;
                                if (showNotify) {
                                    this.showToast('Koneksi ke Print Agent terputus.', 'error');
                                }
                            };
                        };
                        ws.onerror = (e) => {
                            if (showNotify) {
                                this.showToast('Tidak bisa terhubung ke Print Agent. Pastikan agent berjalan di port 8765.', 'error');
                            }
                            reject(e);
                        };
                    });
                },

                disconnectPrinter() {
                    if (this.printerDevice && this.printerDevice.gatt && this.printerDevice.gatt.connected) {
                        try { this.printerDevice.gatt.disconnect(); } catch (e) {}
                    }
                    if (this.printerPort) {
                        try { this.printerPort.close(); } catch (e) {}
                    }
                    if (this.bridgeSocket) {
                        try { this.bridgeSocket.close(); } catch (e) {}
                    }
                    this.printerConnected = false;
                    this.printerConnectionMethod = null;
                    this.printerDevice = null;
                    this.printerCharacteristic = null;
                    this.printerPort = null;
                    this.bridgeSocket = null;
                    this.printerDeviceName = '';
                    localStorage.removeItem('pos_printer_type');
                    this.showToast('Koneksi printer berhasil diputuskan.', 'info');
                },

                autoConnectPrinter() {
                    if (this.printerConnected) return;

                    const savedType = localStorage.getItem('pos_printer_type');
                    const savedAddr = localStorage.getItem('pos_printer_manual_addr') || '';
                    const savedName = localStorage.getItem('pos_printer_name') || '';

                    if (savedType !== 'bridge' && !savedAddr) return;

                    const wsUrl = 'ws://127.0.0.1:8765';
                    
                    let ws;
                    try {
                        ws = new WebSocket(wsUrl);
                    } catch (e) {
                        return; // Silent fail jika Print Agent tidak berjalan
                    }

                    const timeout = setTimeout(() => {
                        try { ws.close(); } catch (e) {}
                    }, 5000);

                    ws.onopen = () => {
                        // Request status saat ini ke Print Agent
                        ws.send(JSON.stringify({ type: 'status' }));
                    };

                    ws.onmessage = (event) => {
                        try {
                            const msg = JSON.parse(event.data);

                            if (msg.type === 'status') {
                                if (msg.connected) {
                                    clearTimeout(timeout);
                                    this.bridgeSocket = ws;
                                    this.printerConnectionMethod = 'bridge';
                                    this.printerType = 'bridge';
                                    this.printerDeviceName = msg.name || savedName || 'Printer Bluetooth';
                                    this.printerConnected = true;
                                    this.showToast('Printer ' + this.printerDeviceName + ' terhubung otomatis!', 'success');

                                    ws.onclose = () => {
                                        this.printerConnected = false;
                                        this.bridgeSocket = null;
                                        this.printerConnectionMethod = null;
                                    };
                                    ws.onmessage = (e) => {
                                        try {
                                            const m = JSON.parse(e.data);
                                            if (m.type === 'status') {
                                                this.printerConnected = m.connected === true;
                                            }
                                        } catch (ex) {}
                                    };
                                } else if (savedAddr) {
                                    // Print Agent jalan tapi printer belum terhubung -> coba hubungkan ke alamat tersimpan
                                    // Deteksi tipe agar Print Agent tahu cara scan yang benar
                                    const isUsbSaved = savedAddr.startsWith('/dev/usb/lp') || /^COM\d+$/i.test(savedAddr);
                                    ws.send(JSON.stringify({ type: 'config', value: savedAddr, target: isUsbSaved ? 'usb' : 'bluetooth' }));
                                }
                            } else if (msg.type === 'scan_result' || msg.type === 'config_result') {
                                clearTimeout(timeout);
                                if (msg.found || msg.connected) {
                                    this.bridgeSocket = ws;
                                    this.printerConnectionMethod = 'bridge';
                                    this.printerType = 'bridge';
                                    this.printerDeviceName = msg.name || savedName || savedAddr;
                                    this.printerConnected = true;
                                    this.showToast('Printer ' + this.printerDeviceName + ' terhubung otomatis!', 'success');

                                    ws.onclose = () => {
                                        this.printerConnected = false;
                                        this.bridgeSocket = null;
                                        this.printerConnectionMethod = null;
                                    };
                                    ws.onmessage = (e) => {
                                        try {
                                            const m = JSON.parse(e.data);
                                            if (m.type === 'status') {
                                                this.printerConnected = m.connected === true;
                                            }
                                        } catch (ex) {}
                                    };
                                } else {
                                    try { ws.close(); } catch (e) {}
                                }
                            }
                        } catch (e) {}
                    };

                    ws.onerror = () => {
                        clearTimeout(timeout);
                    };
                },

                printTestReceipt() {
                    let escpos = "\x1B\x40"; // Init
                    escpos += "\x1B\x61\x01"; // Center align
                    escpos += "RAABIHA STORE\n";
                    escpos += "TES PRINTER THERMAL\n";
                    escpos += "--------------------------------\n";
                    escpos += "\x1B\x61\x00"; // Left align
                    escpos += "Ini adalah cetak uji coba untuk\nmemastikan koneksi printer.\n";
                    escpos += "Jika tulisan ini bisa terbaca\ndengan jelas tanpa garis-garis,\nberarti printer berjalan normal.\n";
                    escpos += "--------------------------------\n";
                    escpos += "1x Produk Tes          50.000\n";
                    escpos += "2x Kopi Susu          100.000\n";
                    escpos += "--------------------------------\n";
                    escpos += "\x1B\x61\x02"; // Right align
                    escpos += "TOTAL: 150.000\n";
                    escpos += "\x1B\x61\x01"; // Center
                    escpos += "TERIMA KASIH\n";
                    escpos += "\x0A\x0A\x0A\x0A\x0A"; // Feed
                    
                    const b64 = btoa(escpos);
                    this.printBase64(b64, null);
                    this.showToast('Mengirim struk uji coba ke printer...', 'info');
                },

                async printBase64(base64Data, orderId = null) {
                    if (!this.printerConnected) {
                        this.showToast('Gagal mencetak: Printer belum dihubungkan!', 'error');
                        return;
                    }
                    try {
                        if (this.printerConnectionMethod === 'bridge' && this.bridgeSocket) {
                            this.bridgeSocket.send(JSON.stringify({ type: 'print', data: base64Data }));
                        } else {
                            const binaryString = window.atob(base64Data);
                            const bytes = new Uint8Array(binaryString.length);
                            for (let i = 0; i < binaryString.length; i++) {
                                bytes[i] = binaryString.charCodeAt(i);
                            }
                            
                            if (this.printerType === 'bluetooth' && this.printerCharacteristic) {
                                const chunkSize = 100;
                                for (let i = 0; i < bytes.length; i += chunkSize) {
                                    const chunk = bytes.slice(i, i + chunkSize);
                                    await this.printerCharacteristic.writeValue(chunk);
                                }
                            } else if (this.printerType === 'serial' && this.printerPort) {
                                const writer = this.printerPort.writable.getWriter();
                                await writer.write(bytes);
                                writer.releaseLock();
                            }
                        }
                        
                        if (orderId) {
                            @this.call('logPrint', orderId);
                        }
                    } catch (error) {
                        console.error(error);
                        this.showToast('Kesalahan cetak: ' + error.message, 'error');
                    }
                },

                triggerCartBounce() {
                    this.cartBouncing = true;
                    if (this.bounceTimeout) clearTimeout(this.bounceTimeout);
                    this.bounceTimeout = setTimeout(() => { this.cartBouncing = false; }, 500);
                },

                addToCart(productId, variantId, name, price, qty = 1, originalPrice = null, purchasePrice = 0, stock = 999999) {
                    this.triggerCartBounce();
                    const itemQty = parseInt(qty) || 1;
                    const itemPrice = parseFloat(price) || 0;
                    const itemStock = parseInt(stock !== null && stock !== undefined ? stock : 999999);

                    const existingIndex = this.cart.findIndex(item => item.product_id === productId && item.product_variant_id === variantId && item.price === itemPrice);
                    if (existingIndex > -1) {
                        const currentQty = parseInt(this.cart[existingIndex].quantity) || 0;
                        const newQty = currentQty + itemQty;
                        const maxStock = this.cart[existingIndex].stock !== undefined ? parseInt(this.cart[existingIndex].stock) : itemStock;
                        
                        if (newQty > maxStock) {
                            this.cart[existingIndex].quantity = maxStock;
                            this.showToast(`Jumlah produk ${name} telah mencapai stok maksimal (${maxStock} pcs).`, 'warning');
                        } else {
                            this.cart[existingIndex].quantity = newQty;
                        }
                    } else {
                        const addedQty = Math.min(itemQty, itemStock);
                        this.cart.unshift({
                            product_id: productId,
                            product_variant_id: variantId,
                            name: name,
                            price: itemPrice,
                            original_price: originalPrice !== null ? parseFloat(originalPrice) : itemPrice,
                            purchase_price: parseFloat(purchasePrice) || 0,
                            quantity: addedQty,
                            stock: itemStock
                        });

                        if (addedQty < itemQty) {
                            this.showToast(`Hanya dapat memasukkan ${addedQty} pcs ${name} sesuai sisa stok.`, 'warning');
                        }
                    }
                    this.calculateVoucherDiscount();
                },

                updateQty(index, change) {
                    if (!this.cart[index]) return;
                    const item = this.cart[index];
                    const changeNum = parseInt(change) || 0;
                    let newQty = parseInt(item.quantity) + changeNum;
                    const maxStock = item.stock !== undefined ? parseInt(item.stock) : 999999;

                    if (newQty > maxStock) {
                        this.cart[index].quantity = maxStock;
                        this.showToast(`Jumlah melebihi stok yang tersedia (${maxStock} pcs).`, 'warning');
                        this.calculateVoucherDiscount();
                        return;
                    }

                    if (newQty > 0) {
                        this.cart[index].quantity = newQty;
                    }
                    this.calculateVoucherDiscount();
                },

                validateCartItemQty(index) {
                    if (!this.cart[index]) return;
                    const item = this.cart[index];
                    let qty = parseInt(item.quantity);
                    const maxStock = item.stock !== undefined ? parseInt(item.stock) : 999999;

                    if (isNaN(qty) || qty < 1) {
                        this.cart[index].quantity = 1;
                        this.calculateVoucherDiscount();
                        return;
                    }

                    if (qty > maxStock) {
                        this.cart[index].quantity = maxStock;
                        this.showToast(`Jumlah melebihi stok yang tersedia (${maxStock} pcs).`, 'warning');
                    }
                    this.calculateVoucherDiscount();
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                    this.calculateVoucherDiscount();
                },

                clearCart(force = false) {
                    const doClear = () => {
                        this.cart = [];
                        this.activeVoucher = null;
                        this.manualDiscountValue = 0;
                        this.cashPaid = 0;
                        this.customerName = '';
                        this.customerPhone = '';
                        this.customerSearchInput = '';
                        this.loyaltyRedeemStamps = 0;
                        this.activeCustomerLoyalty = null;
                        this.showCustomerDropdown = false;
                        this.currentCheckoutToken = null;
                        this.isReserved = false;
                        this.pickupDate = '';
                        // Reset split payment state
                        this.isSplitPayment = false;
                        this.splitPayments = [{method: 'cash', amount: ''}];
                        this.saveActiveCart();
                    };

                    if (force) {
                        doClear();
                        return;
                    }

                    this.askConfirm(
                        'Kosongkan Keranjang?',
                        'Semua barang di keranjang akan dihapus. Lanjutkan?',
                        () => {
                            doClear();
                        }
                    );
                },

                applyVoucher(voucher) {
                    if (!this.isVoucherEligible(voucher)) {
                        this.showToast('Minimal belanja untuk voucher ini belum terpenuhi', 'error');
                        return;
                    }
                    this.activeVoucher = voucher;
                    this.showVoucherModal = false;
                    this.calculateVoucherDiscount();
                    this.showToast('Voucher ' + voucher.name + ' dipasang!', 'success');
                },
                
                removeVoucher() {
                    this.activeVoucher = null;
                    this.calculateVoucherDiscount();
                    this.showToast('Voucher dilepas', 'success');
                },
                
                getVoucherLoyaltyTier(vId) {
                    if (!this.posLoyaltyTiers || !Array.isArray(this.posLoyaltyTiers)) return null;
                    return this.posLoyaltyTiers.find(t => t.voucher_id == vId) || null;
                },

                isVoucherLoyaltyLocked(v) {
                    const tier = this.getVoucherLoyaltyTier(v.id);
                    if (!tier) return false;
                    const stampsNeeded = parseInt(tier.min_stamps || 0);
                    const currentStamps = this.activeCustomerLoyalty ? parseInt(this.activeCustomerLoyalty.stamp_count || 0) : 0;
                    return currentStamps < stampsNeeded;
                },

                isVoucherEligible(voucher) {
                    if (!voucher) return false;
                    if (this.isVoucherLoyaltyLocked(voucher)) {
                        return false;
                    }
                    if (this.isVoucherUsedByActiveCustomer(voucher)) {
                        return false;
                    }
                    let eligible = true;
                    if (voucher.min_purchase > 0 && parseFloat(this.subtotal) < parseFloat(voucher.min_purchase)) {
                        eligible = false;
                    }
                    if (voucher.min_items > 0 && this.totalItems < voucher.min_items) {
                        eligible = false;
                    }
                    return eligible;
                },

                getVoucherDiscountLabel(v) {
                    if (!v) return '';
                    const dType = v.discount_type || v.type || 'fixed';
                    const rawVal = v.discount_amount !== undefined && v.discount_amount !== null ? v.discount_amount : (v.discount_value !== undefined && v.discount_value !== null ? v.discount_value : (v.value || 0));
                    const dVal = parseFloat(rawVal);
                    if (dType === 'fixed') {
                        return 'Potongan Rp ' + this.formatMoney(dVal);
                    }
                    return 'Diskon ' + dVal + '%';
                },

                customerSortCol: 'total_spent',
                customerSortDir: 'desc',

                sortCustomersClient(col) {
                    if (this.customerSortCol === col) {
                        this.customerSortDir = this.customerSortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.customerSortCol = col;
                        this.customerSortDir = 'desc';
                    }

                    const tableBody = document.getElementById('posCustomerTableBody');
                    if (!tableBody) return;

                    const rows = Array.from(tableBody.querySelectorAll('tr[data-cust-row]'));
                    const isAsc = this.customerSortDir === 'asc';

                    rows.sort((a, b) => {
                        let valA = a.getAttribute('data-sort-' + col);
                        let valB = b.getAttribute('data-sort-' + col);

                        if (col === 'name') {
                            return isAsc ? (valA || '').localeCompare(valB || '') : (valB || '').localeCompare(valA || '');
                        }

                        let numA = parseFloat(valA) || 0;
                        let numB = parseFloat(valB) || 0;
                        return isAsc ? numA - numB : numB - numA;
                    });

                    rows.forEach(r => tableBody.appendChild(r));
                },

                historySortCol: 'created_at',
                historySortDir: 'desc',

                sortHistoryClient(col) {
                    if (this.historySortCol === col) {
                        this.historySortDir = this.historySortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.historySortCol = col;
                        this.historySortDir = 'desc';
                    }

                    const tableBody = document.getElementById('posHistoryTableBody');
                    if (!tableBody) return;

                    const rows = Array.from(tableBody.querySelectorAll('tr[data-history-row]'));
                    const isAsc = this.historySortDir === 'asc';

                    rows.sort((a, b) => {
                        let valA = a.getAttribute('data-sort-' + col);
                        let valB = b.getAttribute('data-sort-' + col);

                        if (col === 'customer' || col === 'method' || col === 'status') {
                            return isAsc ? (valA || '').localeCompare(valB || '') : (valB || '').localeCompare(valA || '');
                        }

                        let numA = parseFloat(valA) || 0;
                        let numB = parseFloat(valB) || 0;
                        return isAsc ? numA - numB : numB - numA;
                    });

                    rows.forEach(r => tableBody.appendChild(r));
                },

                returnSortCol: 'created_at',
                returnSortDir: 'desc',

                sortReturnClient(col) {
                    if (this.returnSortCol === col) {
                        this.returnSortDir = this.returnSortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.returnSortCol = col;
                        this.returnSortDir = 'desc';
                    }

                    const tableBody = document.getElementById('posReturnTableBody');
                    if (!tableBody) return;

                    const rows = Array.from(tableBody.querySelectorAll('tr[data-return-row]'));
                    const isAsc = this.returnSortDir === 'asc';

                    rows.sort((a, b) => {
                        let valA = a.getAttribute('data-sort-' + col);
                        let valB = b.getAttribute('data-sort-' + col);

                        if (col === 'type' || col === 'cashier') {
                            return isAsc ? (valA || '').localeCompare(valB || '') : (valB || '').localeCompare(valA || '');
                        }

                        let numA = parseFloat(valA) || 0;
                        let numB = parseFloat(valB) || 0;
                        return isAsc ? numA - numB : numB - numA;
                    });

                    rows.forEach(r => tableBody.appendChild(r));
                },

                get availableLoyaltyVouchersForCustomer() {
                    if (!this.activeCustomerLoyalty || !this.vouchers) return [];
                    return this.vouchers.filter(v => {
                        const tier = this.getVoucherLoyaltyTier(v.id);
                        const isLocked = this.isVoucherLoyaltyLocked(v);
                        const isUsed = this.isVoucherUsedByActiveCustomer(v);
                        return tier && !isLocked && !isUsed;
                    });
                },

                get availableLoyaltyManualRewardsForCustomer() {
                    if (!this.activeCustomerLoyalty || !this.posLoyaltyTiers || !Array.isArray(this.posLoyaltyTiers)) return [];
                    const currentStamps = parseInt(this.activeCustomerLoyalty.stamp_count || 0);
                    const minSpend = currentStamps === 0 ? 150000 : 100000;
                    const willEarnStamp = (this.grandTotal || 0) >= minSpend ? 1 : 0;
                    const projectedStamps = currentStamps + willEarnStamp;

                    return this.posLoyaltyTiers.filter(tier => {
                        const minStamps = parseInt(tier.min_stamps || 0);
                        const isVoucher = tier.is_voucher === undefined || tier.is_voucher === true || tier.is_voucher === '1' || tier.is_voucher === 1;
                        return !isVoucher && projectedStamps >= minStamps && tier.description;
                    });
                },
                
                // Customer Live Search Methods
                get filteredCustomers() {
                    if (!this.allPosCustomers || !Array.isArray(this.allPosCustomers)) return [];
                    const q = (this.customerSearchInput || '').toLowerCase().trim();
                    if (!q) {
                        return this.allPosCustomers.slice(0, 5);
                    }
                    return this.allPosCustomers.filter(c => 
                        (c.name && c.name.toLowerCase().includes(q)) || 
                        (c.phone && c.phone.includes(q))
                    ).slice(0, 8);
                },

                selectCustomer(cust) {
                    if (!cust) return;
                    this.customerName = cust.name || '';
                    this.customerPhone = cust.phone || '';
                    this.activeCustomerLoyalty = cust;
                    this.customerSearchInput = (cust.name || '') + (cust.phone ? ' (' + cust.phone + ')' : '');
                    this.showCustomerDropdown = false;
                    this.saveActiveCart();
                },

                clearCustomer() {
                    this.customerName = '';
                    this.customerPhone = '';
                    this.customerSearchInput = '';
                    this.activeCustomerLoyalty = null;
                    this.showCustomerDropdown = false;
                    this.saveActiveCart();
                },

                onCustomerInput() {
                    const val = (this.customerSearchInput || '').trim();
                    this.customerName = val;
                    if (/^[0-9+]+$/.test(val)) {
                        this.customerPhone = val;
                    }
                    const match = (this.allPosCustomers || []).find(c => 
                        (c.phone && c.phone === val) || 
                        (c.name && c.name.toLowerCase() === val.toLowerCase())
                    );
                    if (match) {
                        this.activeCustomerLoyalty = match;
                        if (match.name) this.customerName = match.name;
                        if (match.phone) this.customerPhone = match.phone;
                    }
                    this.showCustomerDropdown = true;
                    this.saveActiveCart();
                },

                // Fullscreen & PWA Install Desktop App Methods
                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        const docEl = document.documentElement;
                        const req = docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.msRequestFullscreen;
                        if (req) {
                            req.call(docEl).catch(err => {
                                this.showToast('Gagal masuk mode Layar Penuh: ' + err.message, 'error');
                            });
                        }
                    } else {
                        const exit = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen;
                        if (exit) {
                            exit.call(document);
                        }
                    }
                },

                async installApp() {
                    if (!this.deferredInstallPrompt) return;
                    this.deferredInstallPrompt.prompt();
                    const { outcome } = await this.deferredInstallPrompt.userChoice;
                    if (outcome === 'accepted') {
                        this.showToast('Aplikasi Raabiha POS berhasil diinstal di PC!', 'success');
                        this.canInstallApp = false;
                    }
                    this.deferredInstallPrompt = null;
                },

                reloadPos() {
                    this.saveActiveCart();
                    window.location.reload();
                },
                
                // Diskon Manual State
                manualDiscountType: 'rp',
                manualDiscountValue: 0,
                showManualDiscountModal: false,
                tempManualDiscountType: 'rp',
                tempManualDiscountValue: '',
                manualDiscountMaxPercentWithoutPin: {{ (float) (\App\Models\SiteSetting::where('key', 'pos_manual_discount_max_percent_without_pin')->value('value') ?? 20) }},
                manualDiscountMaxRpWithoutPin: {{ (float) (\App\Models\SiteSetting::where('key', 'pos_manual_discount_max_rp_without_pin')->value('value') ?? 50000) }},

                openManualDiscountModal() {
                    this.tempManualDiscountType = this.manualDiscountType;
                    this.tempManualDiscountValue = this.manualDiscountValue > 0 ? this.manualDiscountValue : '';
                    this.showManualDiscountModal = true;
                    setTimeout(() => {
                        const el = document.getElementById('alpineManualDiscountField');
                        if (el) el.focus();
                    }, 50);
                },

                applyManualDiscount() {
                    let val = parseFloat(this.tempManualDiscountValue) || 0;
                    if (val < 0) val = 0;

                    if (this.tempManualDiscountType === 'percent' && val > 100) {
                        this.showToast('Diskon persen maksimal 100%', 'error');
                        return;
                    }

                    const maxPercent = this.manualDiscountMaxPercentWithoutPin;
                    const maxRp = this.manualDiscountMaxRpWithoutPin;

                    const isHighDiscount = (this.tempManualDiscountType === 'percent' && val > maxPercent) || (this.tempManualDiscountType === 'rp' && val > maxRp);

                    if (val > 0 && isHighDiscount) {
                        const limitLabel = this.tempManualDiscountType === 'percent' ? maxPercent + '%' : 'Rp ' + this.formatMoney(maxRp);
                        const reasonText = `Diskon manual ${this.tempManualDiscountType === 'percent' ? val + '%' : 'Rp ' + this.formatMoney(val)} memerlukan otorisasi Supervisor (Batas Mandiri: > ${limitLabel}).`;
                        this.requestSupervisorAuth(reasonText, () => {
                            this.commitManualDiscount(val);
                        });
                    } else {
                        this.commitManualDiscount(val);
                    }
                },

                commitManualDiscount(val) {
                    this.manualDiscountType = this.tempManualDiscountType;
                    this.manualDiscountValue = val;
                    this.showManualDiscountModal = false;

                    if (val > 0) {
                        this.showToast('Diskon manual berhasil diterapkan', 'success');
                    }
                },

                removeManualDiscount() {
                    this.manualDiscountValue = 0;
                    this.showToast('Diskon manual dilepas', 'success');
                },

                get voucherDiscountAmount() {
                    if (!this.activeVoucher) return 0;
                    if (!this.isVoucherEligible(this.activeVoucher)) return 0;

                    const dType = this.activeVoucher.discount_type || this.activeVoucher.type || 'fixed';
                    const dVal = parseFloat(this.activeVoucher.discount_value !== undefined ? this.activeVoucher.discount_value : (this.activeVoucher.discount_amount !== undefined ? this.activeVoucher.discount_amount : (this.activeVoucher.value || 0)));

                    if (dType === 'percent') {
                        let disc = (this.subtotal * dVal) / 100;
                        if (this.activeVoucher.max_discount && disc > parseFloat(this.activeVoucher.max_discount)) {
                            disc = parseFloat(this.activeVoucher.max_discount);
                        }
                        return disc;
                    } else {
                        return dVal;
                    }
                },

                get manualDiscountAmount() {
                    if (!this.manualDiscountValue || this.manualDiscountValue <= 0 || this.subtotal <= 0) return 0;
                    if (this.manualDiscountType === 'percent') {
                        return Math.min(this.subtotal, (this.subtotal * parseFloat(this.manualDiscountValue)) / 100);
                    } else {
                        return Math.min(this.subtotal, parseFloat(this.manualDiscountValue));
                    }
                },

                get discount() {
                    return Math.min(this.subtotal, this.voucherDiscountAmount + this.manualDiscountAmount);
                },

                calculateVoucherDiscount() {
                    if (!this.activeVoucher) return;
                    
                    if (!this.isVoucherEligible(this.activeVoucher)) {
                        this.activeVoucher = null;
                        this.showToast('Voucher otomatis dilepas karena keranjang tidak memenuhi syarat minimum', 'error');
                    }
                },

                get totalItems() {
                    return this.cart.reduce((sum, item) => sum + item.quantity, 0);
                },

                get cartItemCount() {
                    return this.totalItems;
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get grandTotal() {
                    return Math.max(0, this.subtotal - this.discount);
                },

                isCashSelected() {
                    const found = this.paymentMethods.find(m => m.code === this.paymentMethod);
                    if (found) return found.is_cash;
                    return this.paymentMethod === 'tunai' || this.paymentMethod === 'cash';
                },

                selectPaymentMethod(method) {
                    this.paymentMethod = method.code;
                    if (method.is_cash) {
                        this.calculateChange();
                    } else {
                        this.cashPaid = this.grandTotal;
                        this.displayCashPaid = this.formatMoney(this.grandTotal);
                        this.calculateChange();
                    }
                },

                openCheckoutModal() {
                    if (this.cart.length === 0) return;
                    if (this.paymentMethods.length > 0) {
                        const cashMethod = this.paymentMethods.find(m => m.is_cash);
                        this.paymentMethod = cashMethod ? cashMethod.code : this.paymentMethods[0].code;
                    } else {
                        this.paymentMethod = 'tunai';
                    }
                    
                    this.cashPaid = this.grandTotal;
                    this.displayCashPaid = this.formatMoney(this.grandTotal);
                    this.calculateChange();
                    this.showCheckoutModal = true;
                },

                setCashPaid(amount) {
                    this.cashPaid = amount;
                    this.displayCashPaid = amount > 0 ? this.formatMoney(amount) : '';
                    this.calculateChange();
                },

                updateCashPaidInput(rawVal) {
                    const digitsOnly = String(rawVal || '').replace(/\D/g, '');
                    const numericVal = parseInt(digitsOnly, 10) || 0;
                    this.cashPaid = numericVal;
                    this.displayCashPaid = numericVal > 0 ? this.formatMoney(numericVal) : '';
                    this.calculateChange();
                },

                getNominalPresets() {
                    const total = this.grandTotal;
                    if (total <= 0) return [0];
                    const options = new Set();
                    options.add(total);
                    const p50 = Math.ceil(total / 50000) * 50000;
                    const p100 = Math.ceil(total / 100000) * 100000;
                    if (p50 > total) options.add(p50);
                    if (p100 > p50) options.add(p100);
                    if (options.size < 3) {
                        const maxCurrent = Math.max(...Array.from(options));
                        options.add(maxCurrent + (maxCurrent >= 100000 ? 100000 : 50000));
                    }
                    if (options.size < 3) {
                        const maxCurrent = Math.max(...Array.from(options));
                        options.add(maxCurrent + 100000);
                    }
                    return Array.from(options).slice(0, 3);
                },

                calculateChange() {
                    if (this.isSplitPayment) {
                        this.cashChange = this.totalSplitPaid - this.grandTotal;
                    } else {
                        this.cashChange = this.cashPaid - this.grandTotal;
                    }
                },

                submitOrder() {

                    if (this.isSplitPayment) {
                        if (this.totalSplitPaid < this.grandTotal) {
                            this.showToast('Total uang dari Split Payment masih kurang!', 'error');
                            return;
                        }
                    } else {
                        if (this.isCashSelected() && this.cashPaid < this.grandTotal) {
                            this.showToast('Uang yang dibayarkan kurang!', 'error');
                            return;
                        }
                    }

                    if (!this.currentCheckoutToken) {
                        this.currentCheckoutToken = (typeof crypto !== 'undefined' && crypto.randomUUID) 
                            ? crypto.randomUUID() 
                            : ('pos_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9));
                    }

                    this.isProcessing = true;
                    this.showCheckoutModal = false;
                    this.showReceiptPreviewModal = true;
                    this.previewReceiptData = null;
                    
                    const payload = {
                        items: this.cart,
                        discount: this.discount,
                        voucher_discount: this.voucherDiscountAmount || 0,
                        manual_discount: this.manualDiscountValue || 0,
                        voucher_id: this.activeVoucher ? this.activeVoucher.id : null,
                        loyalty_redeem_stamps: this.loyaltyRedeemStamps || 0,
                        payment_method: this.isSplitPayment ? 'split' : this.paymentMethod,
                        cash_paid: this.isSplitPayment ? this.totalSplitPaid : this.cashPaid,
                        cash_change: this.cashChange,
                        customer_name: this.customerName,
                        customer_phone: this.customerPhone,
                        claimed_physical_gifts: this.availableLoyaltyManualRewardsForCustomer ? this.availableLoyaltyManualRewardsForCustomer.map(r => r.description) : [],
                        is_reserved: this.isReserved,
                        pickup_date: this.pickupDate,
                        payment_details: {
                            type: this.isSplitPayment ? 'split' : this.paymentMethod,
                            idempotency_key: this.currentCheckoutToken,
                            is_split_payment: this.isSplitPayment,
                            split_payments: this.isSplitPayment ? this.splitPayments.map(sp => ({
                                method: sp.method,
                                amount: parseFloat(sp.amount) || 0
                            })) : []
                        }
                    };

                    // Safety timeout safeguard (10 detik)
                    setTimeout(() => {
                        if (this.isProcessing) {
                            this.isProcessing = false;
                            this.showToast('Respon server lambat. Silakan periksa koneksi atau coba kembali.', 'info');
                        }
                    }, 10000);

                    // Panggil Livewire backend
                    @this.call('processCheckout', payload);
                },

                formatMoney(amount) {
                    return Number(amount).toLocaleString('id-ID');
                },

                showToast(message, type = 'success') {
                    const id = this.toastId++;
                    this.toasts.push({ id, message, type });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 4000);
                },

                removeToast(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }
            }));
        });
    </script>
</div>
