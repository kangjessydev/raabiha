<div x-data="posSystem()" class="h-screen w-full flex flex-col md:flex-row overflow-hidden bg-gray-50/50 relative">

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

    <!-- Notifications Toast -->
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
        <template x-for="(toast, index) in toasts" :key="toast.id">
            <div x-show="true" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="glass px-6 py-4 rounded-xl flex items-center gap-3 shadow-lg"
                 :class="toast.type === 'error' ? 'border-red-400 bg-red-50/80' : 'border-green-400 bg-green-50/80'">
                <div x-text="toast.message" class="font-medium" :class="toast.type === 'error' ? 'text-red-800' : 'text-green-800'"></div>
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4 font-sans">
            <div class="bg-white border border-gray-200 rounded-xl p-6 sm:p-8 shadow-lg w-full max-w-md space-y-6">
                <div class="text-center space-y-1.5">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Buka Shift Kasir</h2>
                    <p class="text-xs font-medium text-gray-500">Masukkan modal awal (uang kas di laci) untuk memulai transaksi.</p>
                </div>
                
                <form wire:submit.prevent="openSession" class="space-y-5" x-data="{
                    displayCash: '',
                    formatRupiah(val) {
                        if (!val || val === 0 || val === '0') return '';
                        let num = val.toString().replace(/\D/g, '');
                        return num ? parseInt(num, 10).toLocaleString('id-ID') : '';
                    },
                    updateCash(val) {
                        let clean = val.replace(/\D/g, '');
                        this.displayCash = clean ? parseInt(clean, 10).toLocaleString('id-ID') : '';
                        $wire.set('openingCash', clean ? parseInt(clean, 10) : 0);
                    }
                }" x-init="displayCash = formatRupiah($wire.openingCash)">
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
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-lg shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer flex items-center justify-center gap-2"
                    >
                        <svg wire:loading wire:target="openSession" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="openSession">Mulai Sesi Shift</span>
                        <span wire:loading wire:target="openSession">Memproses...</span>
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- MAIN POS AREA -->

        <!-- Sidebar (Clean Filament Native Style) -->
        <div :class="isSidebarOpen ? 'w-64' : 'w-20'" class="bg-white border-r border-gray-200 flex flex-col justify-between h-full transition-all duration-300 z-30 flex-shrink-0 relative font-sans">
            <div class="overflow-y-auto overflow-x-hidden no-scrollbar">
                <!-- Logo -->
                <div class="h-16 flex items-center border-b border-gray-200 px-4 transition-all duration-300" :class="isSidebarOpen ? 'justify-start' : 'justify-center'">
                    <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center text-white font-bold shadow-xs flex-shrink-0">R</div>
                    <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-bold text-gray-900 text-lg tracking-tight whitespace-nowrap">Raabiha POS</span>
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
                    <button @click="activePage = 'cashsummary'" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer" :class="[activePage==='cashsummary' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Rekap Kas">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Rekap Kas</span>
                    </button>
                    <button @click="lockScreen()" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-amber-700 hover:bg-amber-50 transition-colors cursor-pointer" :class="[isSidebarOpen ? 'justify-start' : 'justify-center']" title="Kunci Layar">
                        <svg class="w-5 h-5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
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
                                <button @click="connectPrinter()" :class="[printerConnected ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white hover:border-red-300 hover:text-red-600 text-red-600 border-gray-200', isSidebarOpen ? 'justify-center px-3 py-1.5 border shadow-xs' : 'justify-center p-2.5']" class="w-full rounded-md text-xs font-semibold transition-all flex items-center gap-2 relative cursor-pointer" title="Koneksi Printer">
                                    <span class="relative flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        <span x-show="!isSidebarOpen" class="absolute -top-1 -right-1 h-2 w-2 rounded-full border-2 border-white" :class="printerConnected ? 'bg-emerald-500' : 'bg-red-500 animate-ping'" style="display:none;"></span>
                                    </span>
                                    <span x-show="isSidebarOpen" x-text="printerConnected ? 'Tersambung' : 'Belum Terhubung'" class="whitespace-nowrap"></span>
                                </button>
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
        <div x-show="activePage === 'kasir'" wire:key="pos-page-kasir" class="flex-1 flex flex-col h-full bg-gray-50/40 min-w-0 overflow-hidden font-sans">
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
                <button @click="connectPrinter()"
                    :title="printerConnected ? 'Printer: Tersambung (klik untuk ganti)' : 'Printer: Belum Terhubung — Klik untuk Sambungkan'"
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
            </div>

            <!-- Product Grid (Clean Filament Native Style) -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6">
                <div class="grid gap-4 grid-cols-[repeat(auto-fill,minmax(175px,1fr))]">
                    @forelse($products as $product)
                        @php
                            // Cek jika produk punya varian
                            $hasVariants = $product->variants->count() > 0;
                            $computedStock = $product->computed_stock ?? ($hasVariants ? $product->variants->sum('stock') : $product->stock);
                            $isOutOfStock = $computedStock <= 0;
                            
                            // Harga display
                            $hasPromo = false;
                            $promoPrice = null;
                            $originalPrice = null;
                            $priceDisplay = '';
                            
                            if ($product->pos_discount_price) {
                                $hasPromo = true;
                                $promoPrice = $product->pos_discount_price;
                                $originalPrice = $product->pos_price ?: $product->price;
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
                             x-data="{ variantsData: {{ $hasVariants ? \Illuminate\Support\Js::from($product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => $product->pos_discount_price ?: ($product->pos_price ?: ($v->price ?: $product->price)), 'stock' => $v->stock])) : 'null' }} }"
                             @click="addProduct({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $priceForJs }}, {{ $hasVariants ? 'true' : 'false' }}, variantsData)"
                             @endif
                             >
                             
                             @if($isOutOfStock)
                                <div class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none bg-gray-50/40">
                                    <span class="bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20 text-xs font-bold px-3 py-1 rounded-md shadow-xs transform -rotate-6">HABIS</span>
                                </div>
                             @endif

                            <div class="aspect-square bg-gray-50 relative overflow-hidden">
                                <img src="{{ $image }}" alt="{{ $product->name }}" class="object-cover w-full h-full {{ !$isOutOfStock ? 'group-hover:scale-102' : '' }} transition-transform duration-300">
                                @if(isset($product->is_best_seller) && $product->is_best_seller)
                                    <span class="absolute top-2 left-2 bg-amber-400 text-amber-950 text-[10px] px-2 py-0.5 rounded-md font-bold shadow-xs flex items-center gap-1 z-10">
                                        Terlaris
                                    </span>
                                @endif
                                @if($hasVariants)
                                    <span class="absolute top-2 right-2 bg-gray-900/80 text-white text-[10px] px-2 py-0.5 rounded-md font-medium z-10">{{ $product->variants->count() }} Varian</span>
                                @endif
                                <span class="absolute bottom-2 left-2 {{ $isOutOfStock ? 'bg-red-600' : 'bg-emerald-600' }} text-white text-[10px] px-2 py-0.5 rounded-md font-semibold shadow-xs z-10">Stok: {{ $computedStock }}</span>
                            </div>
                            <div class="p-3 space-y-1.5">
                                <h3 class="font-semibold text-gray-900 text-xs line-clamp-2 leading-tight {{ !$isOutOfStock ? 'group-hover:text-emerald-600' : '' }} transition-colors">{{ $product->name }}</h3>
                                <div class="flex flex-col">
                                    @if($hasPromo)
                                        <span class="text-[10px] text-gray-400 line-through">Rp {{ number_format($originalPrice, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="text-emerald-600 font-bold text-sm leading-tight">{{ $priceDisplay }}</span>
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
        </div>

        <!-- Right: Cart Sidebar (Clean Filament Native Style) -->
        <div x-show="activePage === 'kasir'" class="w-full md:w-[380px] lg:w-[420px] bg-white border-l border-gray-200 flex flex-col relative z-20 font-sans">
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
                            <div class="text-[11px] text-gray-500" x-text="'Rp ' + formatMoney(item.price) + ' / item'"></div>
                            <!-- Qty Controls -->
                            <div class="flex items-center border border-gray-200 rounded-md bg-gray-50">
                                <button @click="updateQty(index, -1)" class="w-6 h-6 flex items-center justify-center bg-white rounded-l-md text-gray-700 hover:text-emerald-600 font-bold text-xs cursor-pointer">-</button>
                                <input type="number" x-model.number="item.quantity" class="w-8 text-center bg-transparent border-none focus:ring-0 text-xs font-semibold p-0 mx-0.5" min="1">
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
                    <span class="flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        <span x-text="activeVoucher ? activeVoucher.name : 'Gunakan Kupon Promo'"></span>
                    </span>
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
                    <span class="flex items-center gap-1.5 text-amber-700 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-8 5h8"/></svg>
                        <span>Diskon Manual</span>
                    </span>
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

        <!-- MODAL: Pilih Varian Produk - Filament Native Style -->
        <div x-show="showVariantModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
            <div @click.away="showVariantModal = false"
                 x-show="showVariantModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white w-full max-w-lg rounded-xl border border-gray-200 shadow-2xl overflow-hidden font-sans">
                 
                 <!-- Modal Header -->
                 <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                     <div>
                         <h3 class="text-base font-bold text-gray-950" x-text="currentProductForVariant ? currentProductForVariant.name : 'Pilih Varian Produk'"></h3>
                         <p class="text-xs text-gray-500 font-medium">Pilih salah satu varian opsi produk di bawah ini</p>
                     </div>
                     <button type="button" @click="showVariantModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                     </button>
                 </div>
                 
                 <!-- Modal Body -->
                 <div class="p-6 space-y-2 max-h-[60vh] overflow-y-auto">
                     <template x-for="variant in currentVariants" :key="variant.id">
                         <button @click="addVariantToCart(variant.id, variant.name, variant.price)" 
                                 :disabled="variant.stock <= 0"
                                 class="w-full flex justify-between items-center p-3.5 border rounded-lg hover:border-emerald-500 hover:bg-emerald-50/50 transition-all text-left cursor-pointer"
                                 :class="variant.stock <= 0 ? 'opacity-50 cursor-not-allowed bg-gray-50 border-gray-200' : 'bg-white border-gray-200 shadow-xs'">
                             <div>
                                 <div class="font-bold text-xs text-gray-900" x-text="variant.name"></div>
                                 <div class="text-[11px] font-medium" :class="variant.stock > 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="variant.stock > 0 ? 'Stok Tersedia: ' + variant.stock : 'Stok Habis'"></div>
                             </div>
                             <div class="font-bold text-xs text-gray-950" x-text="'Rp ' + formatMoney(variant.price)"></div>
                         </button>
                     </template>
                 </div>

                 <!-- Modal Footer -->
                 <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end rounded-b-xl">
                     <button type="button" @click="showVariantModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                         Batal
                     </button>
                 </div>
            </div>
        </div>

        <!-- MODAL: Checkout / Payment - Filament Native Style -->
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
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white flex-shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-950">Pembayaran Transaksi Kasir</h3>
                            <p class="text-xs text-gray-500 font-medium">Pilih metode pembayaran dan masukkan nominal bayar</p>
                        </div>
                    </div>
                    <button type="button" @click="showCheckoutModal = false" :disabled="isProcessing" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body 2 Kolom: Scrollable -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 p-6 overflow-y-auto flex-1 bg-gray-50/50">
                    
                    <!-- Kolom Kiri: Rincian Barang (Col 5) -->
                    <div class="md:col-span-5 space-y-4 flex flex-col justify-between">
                        <div>
                            <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider mb-2.5">Rincian Barang Belanja</h4>
                            <div class="max-h-56 overflow-y-auto space-y-2 pr-1">
                                <template x-for="(item, idx) in cart" :key="idx">
                                    <div class="bg-white p-3 rounded-lg shadow-xs border border-gray-200 space-y-0.5 text-xs">
                                        <div class="flex justify-between items-start font-bold text-gray-900">
                                            <span x-text="item.name" class="pr-2 leading-snug"></span>
                                            <span class="whitespace-nowrap" x-text="'Rp ' + formatMoney(item.price * item.quantity)"></span>
                                        </div>
                                        <div class="text-[11px] text-gray-500 font-medium" x-text="'Rp ' + formatMoney(item.price) + ' × ' + item.quantity + ' pcs'"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Card Total Tagihan -->
                        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-200 space-y-2 mt-auto">
                            <div class="flex justify-between text-xs text-gray-500 font-medium">
                                <span>Subtotal Belanja</span>
                                <span class="font-bold text-gray-900" x-text="'Rp ' + formatMoney(subtotal)"></span>
                            </div>
                            <div x-show="activeVoucher" class="flex justify-between text-xs text-emerald-700 font-medium">
                                <span>Diskon Promo</span>
                                <span class="font-bold" x-text="'- Rp ' + formatMoney(voucherDiscountAmount)"></span>
                            </div>
                            <div x-show="manualDiscountValue > 0" class="flex justify-between text-xs text-amber-700 font-medium">
                                <span>Diskon Manual</span>
                                <span class="font-bold" x-text="'- Rp ' + formatMoney(manualDiscountAmount)"></span>
                            </div>
                            <div class="border-t border-gray-200 pt-2.5 flex justify-between items-center">
                                <span class="font-bold text-gray-900 text-xs uppercase tracking-wider">Total Tagihan</span>
                                <span class="text-xl font-extrabold text-emerald-600" x-text="'Rp ' + formatMoney(grandTotal)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Metode Bayar & Nominal (Col 7) -->
                    <div class="md:col-span-7 space-y-4">
                        <div>
                            <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider mb-2.5">Metode Pembayaran</h4>
                            
                            <div class="grid grid-cols-2 gap-2.5 max-h-44 overflow-y-auto pr-1">
                                <template x-for="method in paymentMethods" :key="method.code">
                                    <button type="button"
                                            @click="selectPaymentMethod(method)"
                                            class="h-11 px-3 rounded-lg text-xs font-semibold flex items-center justify-between transition duration-150 shadow-xs cursor-pointer"
                                            :class="paymentMethod === method.code ? 'border-2 border-emerald-600 bg-emerald-50/80 text-emerald-950 font-bold' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'">
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
                        <div x-show="isCashSelected()" x-transition.opacity class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Uang Diterima (Rp)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-gray-500">Rp</div>
                                    <input type="number"
                                           x-model.number="cashPaid"
                                           @input="calculateChange"
                                           class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-xl font-bold text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs"
                                           placeholder="0">
                                </div>
                            </div>

                            <!-- Preset Nominal Buttons -->
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="setCashPaid(grandTotal)" class="py-2 bg-white border border-gray-300 rounded-lg font-bold text-xs text-gray-800 shadow-xs hover:bg-gray-50 transition cursor-pointer">Uang Pas</button>
                                <button type="button" @click="setCashPaid(Math.ceil(grandTotal/50000)*50000)" class="py-2 bg-white border border-gray-300 rounded-lg font-bold text-xs text-gray-800 shadow-xs hover:bg-gray-50 transition cursor-pointer" x-text="'Rp ' + formatMoney(Math.ceil(grandTotal/50000)*50000)"></button>
                                <button type="button" @click="setCashPaid(Math.ceil(grandTotal/100000)*100000)" class="py-2 bg-white border border-gray-300 rounded-lg font-bold text-xs text-gray-800 shadow-xs hover:bg-gray-50 transition cursor-pointer" x-text="'Rp ' + formatMoney(Math.ceil(grandTotal/100000)*100000)"></button>
                            </div>

                            <!-- Status Kembalian / Uang Kurang -->
                            <div class="p-3 rounded-lg flex items-center justify-between text-xs font-bold border shadow-xs"
                                 :class="cashPaid < grandTotal ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-emerald-50 border-emerald-200 text-emerald-900'">
                                <span x-text="cashPaid < grandTotal ? 'UANG KURANG' : 'KEMBALIAN'"></span>
                                <span class="text-sm font-extrabold" x-text="'Rp ' + formatMoney(Math.abs(cashChange))"></span>
                            </div>
                        </div>

                        <!-- Catatan Pembayaran Non-Tunai -->
                        <div x-show="!isCashSelected()" x-transition.opacity class="p-3.5 bg-white rounded-lg border border-gray-200 text-xs text-gray-600 leading-relaxed shadow-xs">
                            Metode pembayaran non-tunai (<strong x-text="paymentMethods.find(m => m.code === paymentMethod)?.name || paymentMethod"></strong>) dipilih. Transaksi akan dicatat di laporan kasir.
                        </div>

                        <!-- Identitas Pembeli (Opsional) -->
                        <div class="pt-2 border-t border-gray-200">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Data Pelanggan (Opsional)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" x-model="customerName" class="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-xs" placeholder="Nama Pembeli">
                                <input type="text" x-model="customerPhone" class="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-xs" placeholder="No WhatsApp">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl flex-shrink-0">
                    <button type="button" @click="showCheckoutModal = false" :disabled="isProcessing" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="submitOrder()" 
                            :disabled="isProcessing || (isCashSelected() && cashPaid < grandTotal)" 
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isProcessing" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Selesaikan Pembayaran</span>
                        </span>
                        <span x-show="isProcessing" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memproses...</span>
                        </span>
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
                 class="bg-white border border-gray-200 rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
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
                
                <form wire:submit.prevent="closeSession" class="space-y-0" x-data="{
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
                    <div class="p-6 space-y-4">
                        @if($activeSession)
                        <div class="bg-gray-50 p-3.5 rounded-lg border border-gray-200 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Modal Awal Shift:</span>
                                <span class="font-semibold text-gray-900">Rp {{ number_format($activeSession->opening_cash, 0, ',', '.') }}</span>
                            </div>
                            @php
                                $sales = $activeSession->orders()->sum('cash_paid') - $activeSession->orders()->sum('cash_change');
                                $expected = $activeSession->opening_cash + $sales;
                            @endphp
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Penjualan Tunai:</span>
                                <span class="font-semibold text-emerald-600">+ Rp {{ number_format($sales, 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 flex justify-between items-center">
                                <span class="font-semibold text-gray-700">Estimasi Uang Fisik Laci:</span>
                                <span class="font-bold text-emerald-700 text-xs">Rp {{ number_format($expected, 0, ',', '.') }}</span>
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
                    <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl">
                        <button type="button" @click="showCloseSession = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Batal</button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
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
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
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
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-950">Daftar Antrean Pesanan</h3>
                            <p class="text-xs text-gray-500 font-medium">Keranjang belanja yang ditahan sementara</p>
                        </div>
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
             @screen-unlock-failed.window="
                 isLockError = true;
                 lockErrorMessage = $event.detail[0]?.message || $event.detail?.message || 'PIN POS 6-digit salah!';
                 setTimeout(() => {
                     lockPasswordInput = '';
                     isLockError = false;
                     const el = document.getElementById('posLockPasswordField');
                     if (el) el.focus();
                 }, 600);
             "
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
                                    <template x-if="(lockPasswordInput || '').length >= i">
                                        <span class="w-3 h-3 rounded-full inline-block" :class="isLockError ? 'bg-red-500 animate-pulse' : 'bg-emerald-600'"></span>
                                    </template>
                                    <template x-if="(lockPasswordInput || '').length < i">
                                        <span class="w-2 h-2 rounded-full inline-block" :class="isLockError ? 'bg-red-300' : 'bg-gray-300'"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="lockErrorMessage" class="text-xs text-red-600 font-medium text-center" x-text="lockErrorMessage"></div>

                    <button 
                        type="submit" 
                        :disabled="!lockPasswordInput || String(lockPasswordInput).trim().length !== 6" 
                        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-medium text-sm rounded-lg shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer flex items-center justify-center gap-2 mt-2"
                    >
                        <span>Buka Kunci Layar</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- MODAL: Otorisasi PIN Supervisor (Clean Filament Native Style) -->
        <div x-show="showSupervisorPinModal"
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
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">2. Masukkan 6-Digit PIN Supervisor <span class="text-red-500">*</span></label>
                        <input type="password" id="posSupervisorPinField" x-model="supervisorPinInput" maxlength="6" pattern="[0-9]*" inputmode="numeric" placeholder="6 Digit PIN"
                               class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 text-center text-xl font-bold tracking-[0.5em] py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition duration-150">
                        <div x-show="supervisorErrorMessage" class="text-xs text-red-600 font-medium mt-1" x-text="supervisorErrorMessage"></div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showSupervisorPinModal = false" class="flex-1 py-2 px-4 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-medium text-sm rounded-lg shadow-sm transition duration-150 cursor-pointer">Batal</button>
                        <button type="submit" :disabled="!selectedSupervisorId || !supervisorPinInput || String(supervisorPinInput).trim().length !== 6"
                                class="flex-1 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-medium text-sm rounded-lg shadow-sm transition duration-150 cursor-pointer">Verifikasi PIN</button>
                    </div>
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
                            <label class="block text-xs font-semibold text-gray-700 mb-1">3. PIN Supervisor (6 Digit)</label>
                            <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" x-model="voidSupervisorPinInput" placeholder="6 Digit PIN"
                                   class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-center text-lg font-bold tracking-[0.4em] text-gray-950 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-xs">
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

                <form @submit.prevent="submitReturnProcess()" class="flex flex-col flex-1 overflow-hidden space-y-0">
                    <div class="p-6 space-y-5 overflow-y-auto flex-1 bg-gray-50/50">
                        <!-- Step 1: Pilih Barang Di-retur -->
                        <div>
                            <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">1. Pilih Barang & Jumlah yang Dikembalikan</label>
                            <div class="space-y-2 bg-white rounded-lg p-3 border border-gray-200 shadow-xs">
                                <template x-for="(item, idx) in returnOrderItems" :key="item.id">
                                    <div class="flex items-center justify-between bg-gray-50/80 p-2.5 rounded-lg border border-gray-200">
                                        <div class="flex-1 pr-3">
                                            <div class="font-bold text-xs text-gray-900" x-text="item.name"></div>
                                            <div class="text-[11px] text-gray-500" x-text="'Rp ' + formatMoney(item.price) + ' · Dibeli: ' + item.quantity + ' pcs'"></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="text-[11px] font-medium text-gray-500">Retur:</label>
                                            <div class="flex items-center border border-gray-300 rounded-md overflow-hidden bg-white shadow-xs">
                                                <button type="button" @click="item.return_qty = Math.max(0, (item.return_qty || 0) - 1)" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 text-xs">-</button>
                                                <input type="number" x-model.number="item.return_qty" min="0" :max="item.quantity" class="w-10 text-center text-xs font-bold border-none bg-transparent p-0.5 focus:ring-0">
                                                <button type="button" @click="item.return_qty = Math.min(item.quantity, (item.return_qty || 0) + 1)" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 text-xs">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Step 2: Pilih Tipe Aksi -->
                        <div>
                            <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">2. Jenis Transaksi Retur</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition"
                                       :class="returnType === 'exchange' ? 'border-2 border-amber-600 bg-amber-50/70 text-amber-950 font-bold' : 'border-gray-300 bg-white text-gray-700'">
                                    <input type="radio" x-model="returnType" value="exchange" class="text-amber-600 focus:ring-amber-500">
                                    <div>
                                        <div class="text-xs font-bold">Tukar Ukuran / Barang</div>
                                        <div class="text-[11px] font-normal text-gray-500">Tukar ke varian/produk pengganti</div>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition"
                                       :class="returnType === 'refund' ? 'border-2 border-rose-600 bg-rose-50/70 text-rose-950 font-bold' : 'border-gray-300 bg-white text-gray-700'">
                                    <input type="radio" x-model="returnType" value="refund" class="text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <div class="text-xs font-bold">Pengembalian Uang (Refund)</div>
                                        <div class="text-[11px] font-normal text-gray-500">Kembalikan kas ke pelanggan</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Step 3: Pilih Barang Pengganti (Exchange only) -->
                        <template x-if="returnType === 'exchange'">
                            <div>
                                <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">3. Pilih Barang Pengganti (Tukar)</label>

                                <template x-if="returnExchangedItems.length > 0">
                                    <div class="mb-2.5 space-y-1.5">
                                        <template x-for="(eItem, eIdx) in returnExchangedItems" :key="eIdx">
                                            <div class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-xs">
                                                <div>
                                                    <span class="font-bold text-gray-900" x-text="eItem.name"></span>
                                                    <span class="text-gray-500 ml-1" x-text="'Rp ' + formatMoney(eItem.price)"></span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <div class="flex items-center border border-gray-300 rounded bg-white">
                                                        <button type="button" @click="if(eItem.quantity > 1) eItem.quantity--; else removeExchangeItem(eIdx)" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 font-bold text-gray-600">-</button>
                                                        <span class="px-2 font-bold" x-text="eItem.quantity"></span>
                                                        <button type="button" @click="eItem.quantity++" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 font-bold text-gray-600">+</button>
                                                    </div>
                                                    <button type="button" @click="removeExchangeItem(eIdx)" class="text-rose-600 font-bold text-xs hover:underline">Hapus</button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <div class="border border-gray-300 rounded-lg overflow-hidden bg-white shadow-xs">
                                    <input type="text" x-model="exchangeSearchQuery"
                                           placeholder="Cari produk pengganti..."
                                           class="w-full text-xs border-0 border-b border-gray-200 px-3.5 py-2 focus:ring-0 bg-white"
                                           autocomplete="off">
                                    <div class="max-h-44 overflow-y-auto divide-y divide-gray-100">
                                        <template x-for="p in allProducts.filter(p => !exchangeSearchQuery || p.name.toLowerCase().includes(exchangeSearchQuery.toLowerCase())).slice(0,20)" :key="p.id">
                                            <div>
                                                <template x-if="!p.has_variants">
                                                    <button type="button"
                                                            :disabled="p.stock <= 0"
                                                            @click="addExchangeItem(p); exchangeSearchQuery = '';"
                                                            class="w-full text-left px-3 py-2 text-xs hover:bg-amber-50 disabled:opacity-40 disabled:cursor-not-allowed flex justify-between items-center cursor-pointer">
                                                        <div>
                                                            <span class="font-semibold text-gray-900" x-text="p.name"></span>
                                                            <span class="text-gray-500 ml-2" x-text="'Rp ' + formatMoney(p.price)"></span>
                                                        </div>
                                                        <span class="text-gray-400 text-[11px]" x-text="'Stok: ' + p.stock"></span>
                                                    </button>
                                                </template>
                                                <template x-if="p.has_variants">
                                                    <div class="px-3 py-1.5 bg-gray-50/50">
                                                        <div class="text-[11px] font-bold text-gray-700 mb-1" x-text="p.name"></div>
                                                        <div class="flex flex-wrap gap-1.5">
                                                            <template x-for="v in p.variants" :key="v.id">
                                                                <button type="button"
                                                                        :disabled="v.stock <= 0"
                                                                        @click="addExchangeItem({id: p.id, name: p.name, price: p.price, stock: p.stock, has_variants: true}, {id: v.id, name: v.name, price: v.price, stock: v.stock}); exchangeSearchQuery = '';"
                                                                        class="px-2 py-1 bg-white border border-gray-300 rounded text-xs font-medium hover:bg-amber-50 hover:border-amber-400 cursor-pointer disabled:opacity-40 shadow-xs">
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

                        <!-- Summary Box -->
                        <div class="bg-amber-50/70 rounded-lg p-3.5 border border-amber-200 space-y-1.5">
                            <div class="flex justify-between text-xs font-medium text-gray-600">
                                <span>Subtotal Barang Retur:</span>
                                <span class="font-bold text-gray-900" x-text="'- Rp ' + formatMoney(returnSubtotal)"></span>
                            </div>
                            <template x-if="returnType === 'exchange'">
                                <div class="flex justify-between text-xs font-medium text-gray-600">
                                    <span>Subtotal Barang Tukar:</span>
                                    <span class="font-bold text-gray-900" x-text="'+ Rp ' + formatMoney(exchangeSubtotal)"></span>
                                </div>
                            </template>
                            <div class="pt-2 border-t border-amber-200 flex justify-between items-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Status Selisih:</span>
                                <template x-if="returnNetAmount > 0">
                                    <span class="text-xs font-bold text-emerald-700" x-text="'Pelanggan Tambah Bayar: Rp ' + formatMoney(returnNetAmount)"></span>
                                </template>
                                <template x-if="returnNetAmount < 0">
                                    <span class="text-xs font-bold text-rose-700" x-text="'Pengembalian Uang Kas: Rp ' + formatMoney(Math.abs(returnNetAmount))"></span>
                                </template>
                                <template x-if="returnNetAmount === 0">
                                    <span class="text-xs font-bold text-gray-700">Pas (Selisih Rp 0)</span>
                                </template>
                            </div>
                        </div>

                        <!-- Reason Field -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Retur / Tukar Barang</label>
                            <input type="text" x-model="returnReasonInput" placeholder="Contoh: Ukuran kekecilan / Tukar warna / Cacat jahitan"
                                   class="w-full px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-xs">
                        </div>

                        <!-- Supervisor PIN Otorisasi -->
                        <template x-if="returnType === 'refund' || returnNetAmount < 0">
                            <div class="p-3.5 bg-rose-50 rounded-lg border border-rose-200 space-y-2.5">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-rose-700 uppercase tracking-wider">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Otorisasi PIN Supervisor Wajib (Pengembalian Uang)
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Supervisor Pengizin</label>
                                        <select x-model="returnSupervisorIdInput" class="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-xs">
                                            <option value="">-- Pilih Supervisor --</option>
                                            <template x-for="sup in supervisors" :key="sup.id">
                                                <option :value="sup.id" x-text="sup.name + ' (' + sup.role + ')'"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">PIN 6-Digit</label>
                                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" x-model="returnSupervisorPinInput" placeholder="6 Digit PIN"
                                               class="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-center text-xs font-bold tracking-[0.3em] text-gray-950 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-xs">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl flex-shrink-0">
                        <button type="button" @click="showReturnModal = false" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Batal</button>
                        <button type="submit" :disabled="returnSubtotal <= 0"
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-40 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">Proses Retur / Tukar</button>
                    </div>
                </form>
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

                <form wire:submit.prevent="changePosPin" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">PIN Lama (6 Digit)</label>
                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" wire:model="oldPosPin" placeholder="PIN Lama" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-center text-lg font-bold tracking-[0.4em] py-3 px-4 shadow-sm">
                        @error('oldPosPin') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">PIN Baru (6 Digit)</label>
                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" wire:model="newPosPin" placeholder="PIN Baru" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-center text-lg font-bold tracking-[0.4em] py-3 px-4 shadow-sm">
                        @error('newPosPin') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Konfirmasi PIN Baru</label>
                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" wire:model="newPosPinConfirm" placeholder="Ulangi PIN Baru" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-center text-lg font-bold tracking-[0.4em] py-3 px-4 shadow-sm">
                        @error('newPosPinConfirm') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-950">Catat Kas Masuk / Keluar</h3>
                            <p class="text-xs text-gray-500 font-medium">Pencatatan kas petty kasir shift ini</p>
                        </div>
                    </div>
                    <button type="button" @click="showPettyCashModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="recordPettyCash" class="space-y-0">
                    <div class="p-6 space-y-4">
                        <!-- Segmented Switch: Kas Keluar vs Kas Masuk -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tipe Pencatatan Kas</label>
                            <div class="grid grid-cols-2 gap-1.5 p-1 bg-gray-100 rounded-lg border border-gray-200/80">
                                <button type="button" wire:click="$set('pettyCashType', 'out')"
                                        class="py-2 rounded-md text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer {{ $pettyCashType === 'out' ? 'bg-white text-rose-700 shadow-xs border border-gray-200' : 'text-gray-600 hover:text-gray-900' }}">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                    <span>Kas Keluar (Pengeluaran)</span>
                                </button>
                                <button type="button" wire:click="$set('pettyCashType', 'in')"
                                        class="py-2 rounded-md text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer {{ $pettyCashType === 'in' ? 'bg-white text-emerald-700 shadow-xs border border-gray-200' : 'text-gray-600 hover:text-gray-900' }}">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                    <span>Kas Masuk (Pemasukan)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Input Nominal -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Uang (Rp)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-gray-500">Rp</div>
                                <input type="number" wire:model="pettyCashAmount" class="w-full pl-9 pr-3.5 py-2 bg-white border border-gray-300 rounded-lg text-base font-bold text-gray-950 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs" placeholder="0">
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
                        <button type="submit" class="px-4 py-2 {{ $pettyCashType === 'out' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Kas</span>
                        </button>
                    </div>
                </form>
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
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 backdrop-blur-xs p-4" style="display: none;">
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
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-950">Pilih Kupon Promo</h3>
                            <p class="text-xs text-gray-500 font-medium">Voucher diskon aktif untuk transaksi ini</p>
                        </div>
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
                                    <h4 class="font-bold text-xs text-gray-950" x-text="v.name"></h4>
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
                                
                                <span x-show="!isVoucherEligible(v)" class="text-[11px] font-semibold text-rose-600">Syarat belum terpenuhi</span>
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
        <div x-show="activePage === 'history'" wire:key="pos-page-history" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50/50 overflow-y-auto font-sans">
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

                <!-- Ringkasan KPI (Filament Stat Cards - Grid 3 Kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total Omzet Filtered</p>
                            <p class="text-xl font-bold text-gray-950 mt-1">Rp {{ number_format($sessionOrders->where('status', '!=', 'cancelled')->sum('grand_total'), 0, ',', '.') }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Nota Selesai</p>
                            <p class="text-xl font-bold text-gray-950 mt-1">{{ $sessionOrders->where('status', '!=', 'cancelled')->count() }} <span class="text-xs font-normal text-gray-500">Nota</span></p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Nota VOID / Batal</p>
                            <p class="text-xl font-bold text-gray-950 mt-1">{{ $sessionOrders->where('status', 'cancelled')->count() }} <span class="text-xs font-normal text-gray-500">Nota</span></p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- UNIFIED TABLE CARD (Filament Native Card with Toolbar Header) -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    
                    <!-- Filament Table Header Toolbar (Search FAR LEFT + Filter Button FAR RIGHT) -->
                    <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between gap-4 bg-white">
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
                        <div class="relative flex-shrink-0" x-data="{ showFilterPopover: false }" wire:key="history-filter-container">
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
                                            <option value="cash">Tunai (Cash)</option>
                                            <option value="non_cash">Non-Tunai (QRIS / Transfer / EDC)</option>
                                        </select>
                                    </div>

                                    <!-- Status Filter -->
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Status Transaksi</label>
                                        <select wire:key="filter-status-select" wire:model.live="historyStatusFilter"
                                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                            <option value="all">Semua Status</option>
                                            <option value="completed">Selesai</option>
                                            <option value="cancelled">VOID / Batal</option>
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
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">No. Nota & Waktu</th>
                                    <th class="py-3 px-4">Pelanggan</th>
                                    <th class="py-3 px-4">Item Barang</th>
                                    <th class="py-3 px-4 text-right">Total Belanja</th>
                                    <th class="py-3 px-4 text-center">Metode</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @foreach($sessionOrders as $order)
                                <tr wire:key="order-row-{{ $order->id }}" class="hover:bg-gray-50/80 transition-colors {{ $order->status === 'cancelled' ? 'bg-rose-50/20' : '' }}">
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
                                            <div class="text-[10px] font-medium text-emerald-600">Hemat Rp {{ number_format($order->discount_total, 0, ',', '.') }}</div>
                                        @endif
                                    </td>

                                    <!-- Metode -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if(in_array(strtolower($order->payment_method), ['cash', 'tunai']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                Tunai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20">
                                                {{ strtoupper($order->payment_method) }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if($order->status === 'cancelled')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                                VOID
                                            </span>
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
                                                'payment_method' => strtoupper($order->payment_method),
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
                                            <button type="button" wire:click="reprintReceipt({{ $order->id }})" wire:loading.attr="disabled"
                                                    class="p-1.5 bg-white border border-gray-300 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-gray-700 rounded-lg transition-colors cursor-pointer" title="Cetak Ulang Struk Thermal">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            </button>

                                            <!-- Void Order Button (Supervisor Authorization required) -->
                                            <button type="button" @click="requestSupervisorAuth('void_order', {{ $order->id }}, 'Membatalkan (Void) Nota #{{ $order->order_number }}')"
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
                </div>
                @endif
            </div>
        </div>

        <!-- ============================================ -->
        <!-- PAGE: Riwayat Retur (Filament Native Table)  -->
        <!-- ============================================ -->
        <div x-show="activePage === 'returns'" wire:key="pos-page-returns" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50/50 overflow-hidden font-sans">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <button @click="activePage = 'kasir'" class="p-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Kembali ke Kasir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-950">
                            Riwayat Retur & Penukaran Barang
                        </h1>
                        <p class="text-xs text-gray-500 font-medium">Shift hari ini &mdash; {{ count($sessionReturns ?? []) }} retur/penukaran tercatat</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
                <!-- Ringkasan KPI Retur (Filament Stat Cards - Grid 3 Kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total Transaksi Retur</p>
                            <p class="text-xl font-bold text-gray-950 mt-1">{{ count($sessionReturns ?? []) }} <span class="text-xs font-normal text-gray-500">Transaksi</span></p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total Refund Kas</p>
                            <p class="text-xl font-bold text-rose-600 mt-1">Rp {{ number_format(abs(collect($sessionReturns ?? [])->where('net_amount', '<', 0)->sum('net_amount')), 0, ',', '.') }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total Tambah Bayar (Tukar)</p>
                            <p class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format(collect($sessionReturns ?? [])->where('net_amount', '>', 0)->sum('net_amount'), 0, ',', '.') }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- UNIFIED TABLE CARD (Filament Native Card with Table) -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between gap-4 bg-white">
                        <div class="text-sm font-bold text-gray-900">Daftar Retur & Penukaran Shift Ini</div>
                        <div class="text-xs font-medium text-gray-500">Total {{ count($sessionReturns ?? []) }} Data</div>
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
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3 px-4">No. Retur & Waktu</th>
                                    <th class="py-3 px-4">Nota Asli</th>
                                    <th class="py-3 px-4 text-center">Tipe Transaksi</th>
                                    <th class="py-3 px-4">Rincian Barang</th>
                                    <th class="py-3 px-4 text-right">Selisih Nominal</th>
                                    <th class="py-3 px-4">Petugas</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @foreach($sessionReturns as $ret)
                                <tr wire:key="return-row-{{ $ret->id }}" class="hover:bg-gray-50/80 transition-colors">
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
                                        <button wire:click="reprintReturnReceipt({{ $ret->id }})" wire:loading.attr="disabled"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
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
             x-data="{ customerSearch: '' }"
             wire:key="pos-page-customers"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="flex-1 flex flex-col h-full bg-gray-50/50 overflow-hidden font-sans">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <button @click="activePage = 'kasir'" class="p-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Kembali ke Kasir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-950">Pelanggan POS</h1>
                        <p class="text-xs text-gray-500 font-medium">{{ count($sessionCustomers) }} pelanggan terdaftar pada shift ini</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
                <!-- Ringkasan KPI Pelanggan (Filament Stat Cards - Grid 3 Kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total Pelanggan Shift Ini</p>
                            <p class="text-xl font-bold text-gray-950 mt-1">{{ count($sessionCustomers) }} <span class="text-xs font-normal text-gray-500">Orang</span></p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total Belanja Pelanggan</p>
                            <p class="text-xl font-bold text-gray-950 mt-1">Rp {{ number_format($sessionCustomers->sum('total_spent'), 0, ',', '.') }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Kartu Stempel Gratis Ready</p>
                            <p class="text-xl font-bold text-amber-600 mt-1">{{ $sessionCustomers->filter(fn($c) => ($c->stamp_count ?? 0) >= 10 || ($c->completed_cards_count ?? 0) > 0)->count() }} <span class="text-xs font-normal text-gray-500">Voucher</span></p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 13C10.832 21 4 15.8 4 10a8 8 0 1116 0c0 5.8-6.832 11-8 11z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- UNIFIED TABLE CARD (Filament Native Card with Search Toolbar) -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <!-- Filament Table Header Toolbar -->
                    <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between gap-4 bg-white">
                        <div class="relative min-w-[220px] max-w-xs flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text"
                                   x-model="customerSearch"
                                   placeholder="Cari nama atau No. HP pelanggan..."
                                   class="w-full pl-9 pr-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs">
                        </div>
                        <div class="text-xs font-medium text-gray-500">Total {{ count($sessionCustomers) }} Pelanggan</div>
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
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3 px-4">Nama Pelanggan</th>
                                    <th class="py-3 px-4">No. Telepon / HP</th>
                                    <th class="py-3 px-4 text-center">Total Kunjungan</th>
                                    <th class="py-3 px-4 text-center">Loyalty & Stempel</th>
                                    <th class="py-3 px-4 text-right">Total Belanja</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @foreach($sessionCustomers as $idx => $customer)
                                <tr wire:key="cust-row-{{ $idx }}"
                                    x-show="!customerSearch || '{{ strtolower($customer->customer_name) }}'.includes(customerSearch.toLowerCase()) || '{{ $customer->customer_phone }}'.includes(customerSearch)"
                                    class="hover:bg-gray-50/80 transition-colors">

                                    <!-- Nama Pelanggan -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center flex-shrink-0 border border-emerald-200">
                                                {{ strtoupper(substr($customer->customer_name, 0, 1)) }}
                                            </div>
                                            <div class="font-bold text-gray-900 text-xs">{{ $customer->customer_name }}</div>
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

                                    <!-- Total Kunjungan -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-700">
                                            {{ $customer->total_orders ?? $customer->visit_count ?? 1 }}x Kunjungan
                                        </span>
                                    </td>

                                    <!-- Loyalty & Stempel -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if(($customer->stamp_count ?? 0) >= 10 || ($customer->completed_cards_count ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20" title="Pelanggan memiliki voucher kartu stempel gratis siap klaim!">
                                                <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 13C10.832 21 4 15.8 4 10a8 8 0 1116 0c0 5.8-6.832 11-8 11z"/></svg>
                                                <span>{{ $customer->completed_cards_count ?: 1 }} Kartu Gratis Siap Klaim</span>
                                            </span>
                                        @elseif(($customer->stamp_count ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>{{ $customer->stamp_count }}/10 Stempel</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-50 text-gray-400 ring-1 ring-inset ring-gray-200">
                                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>0/10 Stempel</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Total Belanja -->
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="font-bold text-xs text-gray-900">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</div>
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
        <div x-show="activePage === 'cashsummary'" wire:key="pos-page-cashsummary" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50/50 overflow-hidden font-sans">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <button @click="activePage = 'kasir'" class="p-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500" title="Kembali ke Kasir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-950">Rekap Kas Shift</h1>
                        @if(!empty($sessionStats))
                        <p class="text-xs text-gray-500 font-medium">Shift dibuka sejak {{ $sessionStats['opened_at'] }}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if(!empty($sessionStats))
            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
                <!-- Ringkasan KPI Rekap Kas (Filament Stat Cards - Grid 3 Kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Stat 1: Estimasi Kas di Laci (Featured) -->
                    <div class="bg-emerald-50/70 rounded-xl border border-emerald-200/80 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Estimasi Kas di Laci Kasir</p>
                            <p class="text-xl font-extrabold text-emerald-950 mt-1">Rp {{ number_format($sessionStats['expected_cash'], 0, ',', '.') }}</p>
                            <p class="text-[10px] text-emerald-700 font-medium mt-0.5">Modal + Tunai + Kas Masuk - Out</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>

                    <!-- Stat 2: Total Omzet Sales -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total Omzet Penjualan</p>
                            <p class="text-xl font-bold text-gray-950 mt-1">Rp {{ number_format($sessionStats['total_sales'], 0, ',', '.') }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">Tunai + Non-Tunai Shift Ini</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>

                    <!-- Stat 3: Total Transaksi Selesai -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Total Nota Selesai</p>
                            <p class="text-xl font-bold text-gray-950 mt-1">{{ $sessionStats['total_trx'] }} <span class="text-xs font-normal text-gray-500">Nota</span></p>
                            <p class="text-[10px] text-gray-500 mt-0.5">Berhasil Diproses</p>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- 2-COLUMN GRID (Breakdown Arus Kas + Tabel Petty Cash) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- KOLOM KIRI (5/12): Rincian Arus Kas & Akses Tutup Shift -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 space-y-4">
                            <div class="border-b border-gray-100 pb-3">
                                <h3 class="font-bold text-gray-900 text-sm">Rincian Arus Kas Shift</h3>
                                <p class="text-xs text-gray-500">Komposisi penerimaan & pengeluaran kasir</p>
                            </div>

                            <div class="space-y-3">
                                <!-- Modal Awal -->
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <span class="text-gray-700 font-medium">Modal Awal Shift</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">Rp {{ number_format($sessionStats['opening_cash'], 0, ',', '.') }}</span>
                                </div>

                                <!-- Penjualan Tunai -->
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <span class="text-gray-700 font-medium">Penjualan Tunai (Cash)</span>
                                    </div>
                                    <span class="font-bold text-emerald-600">+ Rp {{ number_format($sessionStats['cash_sales'], 0, ',', '.') }}</span>
                                </div>

                                <!-- Non-Tunai -->
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 bg-sky-50 rounded-lg flex items-center justify-center text-sky-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        </div>
                                        <span class="text-gray-700 font-medium">Non-Tunai (QRIS / Transfer / EDC)</span>
                                    </div>
                                    <span class="font-bold text-sky-600">+ Rp {{ number_format($sessionStats['non_cash_sales'], 0, ',', '.') }}</span>
                                </div>

                                <!-- Kas Masuk -->
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                        </div>
                                        <span class="text-gray-700 font-medium">Kas Masuk Petty Cash</span>
                                    </div>
                                    <span class="font-bold text-emerald-600">+ Rp {{ number_format($sessionStats['petty_cash_in'], 0, ',', '.') }}</span>
                                </div>

                                <!-- Kas Keluar -->
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 bg-rose-50 rounded-lg flex items-center justify-center text-rose-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                        </div>
                                        <span class="text-gray-700 font-medium">Kas Keluar Petty Cash</span>
                                    </div>
                                    <span class="font-bold text-rose-600">- Rp {{ number_format($sessionStats['petty_cash_out'], 0, ',', '.') }}</span>
                                </div>

                                <!-- Total Omzet -->
                                <div class="flex justify-between items-center pt-2 text-xs">
                                    <span class="font-bold text-gray-900">Total Omzet Penjualan</span>
                                    <span class="font-extrabold text-base text-gray-950">Rp {{ number_format($sessionStats['total_sales'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button Tutup Shift -->
                        <button @click="activePage = 'kasir'; $nextTick(() => showCloseSession = true)"
                                class="w-full py-3 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition duration-150 flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Tutup Shift Kasir Sekarang</span>
                        </button>
                    </div>

                    <!-- KOLOM KANAN (7/12): Tabel Riwayat Kas Masuk / Keluar (Petty Cash Table) -->
                    <div class="lg:col-span-7">
                        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden h-full flex flex-col">
                            <!-- Table Header Toolbar -->
                            <div class="px-5 py-3.5 border-b border-gray-200 flex items-center justify-between gap-4 bg-white">
                                <div>
                                    <div class="text-sm font-bold text-gray-900">Riwayat Kas Masuk / Keluar</div>
                                    <div class="text-xs text-gray-500">Pencatatan uang masuk/keluar shift ini</div>
                                </div>
                                <button @click="showPettyCashModal = true"
                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    <span>Catat Kas</span>
                                </button>
                            </div>

                            @if(count($sessionPettyCash) === 0)
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
                                            <!-- Waktu -->
                                            <td class="py-3 px-4 whitespace-nowrap font-mono text-gray-500">
                                                {{ $cashLog->created_at->format('H:i') }} WIB
                                            </td>

                                            <!-- Tipe -->
                                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                                @if($cashLog->type === 'out')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                                        KAS KELUAR
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                        KAS MASUK
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Keterangan -->
                                            <td class="py-3 px-4">
                                                <div class="font-semibold text-gray-900">{{ $cashLog->description }}</div>
                                            </td>

                                            <!-- Nominal -->
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
            @endif
        </div>

    <!-- Modal Preview Struk & Sukses Pembayaran - Filament Native Style -->
    <div x-show="showReceiptPreviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/50 backdrop-blur-xs font-sans" x-transition.opacity>
        <div class="bg-white w-full max-w-md rounded-xl overflow-hidden shadow-2xl border border-gray-200 flex flex-col max-h-[90vh]" @click.away="showReceiptPreviewModal = false">
            <!-- Header -->
            <div class="px-6 py-4 bg-emerald-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base leading-tight">Pembayaran Berhasil!</h3>
                        <p class="text-xs text-emerald-100 font-medium" x-text="previewReceiptData.orderNumber"></p>
                    </div>
                </div>
                <button @click="showReceiptPreviewModal = false" class="text-emerald-100 hover:text-white p-1 rounded-lg transition">&times;</button>
            </div>

            <!-- Content Area -->
            <div class="p-6 space-y-4 overflow-y-auto flex-1 bg-gray-50/50">
                <!-- High Contrast Change Display -->
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Uang Kembalian Pelanggan</div>
                    <div class="text-2xl font-black text-emerald-800 mt-0.5">
                        Rp <span x-text="formatMoney(previewReceiptData.cashChange)"></span>
                    </div>
                </div>

                <!-- Receipt Thermal Text Paper Preview -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-700">Pratinjau Struk Thermal (ESC/POS)</span>
                        <span class="text-[11px] text-gray-400">32 Kolom</span>
                    </div>
                    <div class="bg-gray-950 p-4 rounded-xl border border-gray-800 shadow-inner">
                        <pre class="font-mono text-xs leading-relaxed text-emerald-400 whitespace-pre overflow-x-auto select-all" x-text="previewReceiptData.text"></pre>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-xl flex-shrink-0">
                <button @click="showReceiptPreviewModal = false" class="px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-semibold text-xs rounded-lg shadow-xs transition duration-150 cursor-pointer">
                    Selesai
                </button>
                <button @click="printBase64(previewReceiptData.base64)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition duration-150 flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Cetak Struk</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Detail Rincian Nota Transaksi - Filament Native Style -->
    <div x-show="showDetailOrderModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/50 backdrop-blur-xs font-sans" x-transition.opacity>
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
    @endif

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
                
                // Voucher State
                vouchers: @json($vouchers ?? []),
                paymentMethods: @json($paymentMethods ?? []),
                allProducts: @json($allProductsJson ?? []),
                exchangeSearchQuery: '',
                showVoucherModal: false,
                activeVoucher: null,
                
                // Modals
                showVariantModal: false,
                showCheckoutModal: false,
                showCloseSession: false,
                activePage: 'kasir',
                
                // Checkout State
                cashPaid: 0,
                cashChange: 0,
                paymentMethod: 'cash',
                customerName: '',
                customerPhone: '',
                isProcessing: false,
                
                // Toasts
                toasts: [],
                toastId: 0,

                currentProductForVariant: null,
                currentVariants: [],
                
                // Printer State
                printerConnected: false,
                printerDevice: null,
                printerType: 'bluetooth',
                printerCharacteristic: null,
                printerPort: null,
                heldCarts: [],
                showHoldModal: false,
                showReceiptPreviewModal: false,
                previewReceiptData: { orderNumber: '', cashChange: 0, text: '', base64: '' },

                // Confirmation Modal State
                showConfirmModal: false,
                confirmTitle: '',
                confirmMessage: '',
                confirmAction: null,
                
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
                supervisors: @json($supervisorsList ?? []),
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
                returnOrderId: null,
                returnOrderNumber: '',
                returnType: 'exchange',
                returnReasonInput: '',
                returnSupervisorIdInput: '',
                returnSupervisorPinInput: '',
                returnOrderItems: [],
                returnExchangedItems: [],

                openReturnModal(id, number, items) {
                    this.returnOrderId = id;
                    this.returnOrderNumber = number;
                    this.returnType = 'exchange';
                    this.returnReasonInput = '';
                    this.returnSupervisorIdInput = this.supervisors.length === 1 ? this.supervisors[0].id : '';
                    this.returnSupervisorPinInput = '';
                    this.returnOrderItems = (items || []).map(i => ({
                        ...i,
                        return_qty: 0
                    }));
                    this.returnExchangedItems = [];
                    this.showReturnModal = true;
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
                    const price = variant ? (variant.pos_discount_price || variant.pos_price || variant.price || product.price) : (product.pos_discount_price || product.pos_price || product.price);
                    const name = variant ? (product.name + ' - ' + variant.name) : product.name;
                    
                    const existing = this.returnExchangedItems.find(i => i.product_id === product.id && i.product_variant_id === (variant ? variant.id : null));
                    if (existing) {
                        existing.quantity += 1;
                    } else {
                        this.returnExchangedItems.push({
                            product_id: product.id,
                            product_variant_id: variant ? variant.id : null,
                            name: name,
                            price: parseFloat(price),
                            quantity: 1
                        });
                    }
                },

                removeExchangeItem(index) {
                    this.returnExchangedItems.splice(index, 1);
                },

                submitReturnProcess() {
                    const returned = this.returnOrderItems
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

                    const net = this.returnNetAmount;
                    if (net < 0 || this.returnType === 'refund') {
                        if (!this.returnSupervisorIdInput) {
                            this.showToast('Pilih Supervisor pengizin pengembalian uang terlebih dahulu', 'error');
                            return;
                        }
                        const pin = (this.returnSupervisorPinInput || '').toString().trim();
                        if (!pin || pin.length !== 6) {
                            this.showToast('Masukkan 6-digit PIN Supervisor', 'error');
                            return;
                        }
                    }

                    const payload = {
                        order_id: this.returnOrderId,
                        type: this.returnType,
                        reason: this.returnReasonInput,
                        returned_items: returned,
                        exchanged_items: exchanged,
                        supervisor_id: this.returnSupervisorIdInput,
                        supervisor_pin: this.returnSupervisorPinInput
                    };

                    @this.call('processReturn', JSON.stringify(payload));
                    this.showReturnModal = false;
                },

                // Input Modal State
                showInputModal: false,
                showPettyCashModal: false,
                showChangePinModal: false,
                showSupervisorPinModal: false,
                supervisorPinInput: '',
                supervisorErrorMessage: '',
                supervisorReasonMessage: '',
                pendingSupervisorCallback: null,

                requestSupervisorAuth(reason, callback) {
                    this.supervisorReasonMessage = reason;
                    this.selectedSupervisorId = this.supervisors.length === 1 ? this.supervisors[0].id : '';
                    this.supervisorPinInput = '';
                    this.supervisorErrorMessage = '';
                    this.pendingSupervisorCallback = callback;
                    this.showSupervisorPinModal = true;
                    setTimeout(() => {
                        const el = document.getElementById('posSupervisorPinField');
                        if (el) el.focus();
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
                        this.lockErrorMessage = e.detail[0].message || 'Password salah!';
                        this.showToast(this.lockErrorMessage, 'error');
                    });
                    window.addEventListener('session-opened', () => { this.showToast('Sesi kasir berhasil dibuka', 'success'); });
                    window.addEventListener('session-closed', () => { this.showCloseSession = false; this.showToast('Sesi kasir berhasil ditutup', 'success'); });
                    window.addEventListener('petty-cash-saved', () => { this.showPettyCashModal = false; });
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
                    });
                    this.startAutoLockChecker();
                    window.addEventListener('checkout-success', (e) => {
                        this.isProcessing = false;
                        this.showCheckoutModal = false;
                        this.previewReceiptData = {
                            orderNumber: e.detail[0].order_number,
                            cashChange: e.detail[0].cash_change,
                            text: e.detail[0].receipt_text,
                            base64: e.detail[0].base64
                        };
                        this.showReceiptPreviewModal = true;
                        this.clearCart(true);
                        this.showToast('Pembayaran Berhasil! Kembalian: Rp ' + this.formatMoney(e.detail[0].cash_change), 'success');
                    });
                    window.addEventListener('print-receipt', (e) => {
                        const b64 = e.detail?.base64 || e.detail?.[0]?.base64;
                        if (b64) this.printBase64(b64);
                    });
                    window.addEventListener('print-z-report', (e) => {
                        const b64 = e.detail?.base64 || e.detail?.[0]?.base64;
                        if (b64) this.printBase64(b64);
                    });
                },

                saveHeldCarts() {
                    localStorage.setItem('pos_held_carts', JSON.stringify(this.heldCarts));
                },

                holdCart() {
                    if (this.cart.length === 0) return;
                    
                    let defaultName = this.customerName;
                    if (!defaultName) {
                        const firstItem = this.cart[0].name;
                        const otherItems = this.cart.length > 1 ? ` + ${this.cart.length - 1} item` : '';
                        defaultName = `${firstItem}${otherItems}`;
                    }

                    this.askInput(
                        'Simpan ke Antrean',
                        'Berikan catatan, nomor meja, atau nama pelanggan untuk antrean ini:',
                        'Contoh: Meja 4 / Budi',
                        defaultName,
                        (val) => {
                            const holdId = Date.now();
                            const now = new Date();
                            const holdTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                            this.heldCarts.push({
                                id: holdId,
                                time: holdTime,
                                name: val || defaultName,
                                cart: JSON.parse(JSON.stringify(this.cart)),
                                activeVoucher: this.activeVoucher,
                                manualDiscountType: this.manualDiscountType,
                                manualDiscountValue: this.manualDiscountValue,
                                customerName: this.customerName,
                                customerPhone: this.customerPhone,
                                total: this.subtotal
                            });
                            
                            this.saveHeldCarts();
                            this.clearCart(true);
                            this.showToast('Pesanan berhasil dimasukkan ke antrean', 'success');
                        }
                    );
                },

                resumeCart(id) {
                    const index = this.heldCarts.findIndex(h => h.id === id);
                    if (index !== -1) {
                        const doResume = () => {
                            const hold = this.heldCarts[index];
                            this.cart = hold.cart;
                            this.activeVoucher = hold.activeVoucher || null;
                            this.manualDiscountType = hold.manualDiscountType || 'rp';
                            this.manualDiscountValue = hold.manualDiscountValue || 0;
                            this.customerName = hold.customerName || '';
                            this.customerPhone = hold.customerPhone || '';
                            
                            this.heldCarts.splice(index, 1);
                            this.saveHeldCarts();
                            this.showHoldModal = false;
                            this.showToast('Antrean berhasil dilanjutkan', 'success');
                        };

                        if (this.cart.length > 0) {
                            this.askConfirm(
                                'Tumpuk Keranjang?', 
                                'Keranjang Anda saat ini tidak kosong. Jika dilanjutkan, belanjaan saat ini akan terganti oleh antrean yang dipanggil. Lanjutkan?', 
                                doResume
                            );
                        } else {
                            doResume();
                        }
                    }
                },

                deleteHeldCart(id) {
                    this.askConfirm(
                        'Hapus Antrean?', 
                        'Apakah Anda yakin ingin menghapus antrean ini? Data tidak bisa dikembalikan.', 
                        () => {
                            this.heldCarts = this.heldCarts.filter(h => h.id !== id);
                            this.saveHeldCarts();
                        }
                    );
                },

                addProduct(id, name, price, hasVariants, variants = null) {
                    if (hasVariants && variants) {
                        this.currentProductForVariant = { id, name };
                        this.currentVariants = variants;
                        this.showVariantModal = true;
                        return;
                    }
                    this.addToCart(id, null, name, price);
                },

                addVariantToCart(variantId, variantName, variantPrice) {
                    const fullName = this.currentProductForVariant.name + ' - ' + variantName;
                    this.addToCart(this.currentProductForVariant.id, variantId, fullName, variantPrice);
                    this.showVariantModal = false;
                },

                async connectPrinter() {
                    if (this.printerType === 'bluetooth') {
                        // Cek apakah browser mendukung Bluetooth
                        if (!navigator.bluetooth) {
                            this.showToast('Browser Anda tidak mendukung koneksi Bluetooth. Gunakan Google Chrome atau Microsoft Edge, dan pastikan halaman dibuka lewat HTTPS.', 'error');
                            return;
                        }
                        try {
                            const device = await navigator.bluetooth.requestDevice({
                                filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
                                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                            });
                            const server = await device.gatt.connect();
                            const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                            const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                            
                            this.printerDevice = device;
                            this.printerCharacteristic = characteristic;
                            this.printerConnected = true;
                            this.showToast('Printer Bluetooth berhasil terhubung!', 'success');
                            
                            device.addEventListener('gattserverdisconnected', () => {
                                this.printerConnected = false;
                                this.showToast('Koneksi printer terputus. Klik "Printer Offline" untuk menyambung kembali.', 'error');
                            });
                        } catch (error) {
                            console.error(error);
                            // Pesan error yang ramah pengguna
                            if (error.name === 'NotFoundError' || error.message.includes('cancelled')) {
                                this.showToast('Pencarian printer dibatalkan.', 'error');
                            } else if (error.name === 'SecurityError') {
                                this.showToast('Akses Bluetooth ditolak. Pastikan halaman dibuka lewat HTTPS dan izin Bluetooth diaktifkan di browser.', 'error');
                            } else {
                                this.showToast('Tidak bisa terhubung ke printer. Pastikan printer sudah dinyalakan dan dalam jangkauan Bluetooth.', 'error');
                            }
                        }
                    } else if (this.printerType === 'serial') {
                        // Cek apakah browser mendukung Serial/USB
                        if (!navigator.serial) {
                            this.showToast('Browser Anda tidak mendukung koneksi USB/Serial. Gunakan Google Chrome atau Microsoft Edge.', 'error');
                            return;
                        }
                        try {
                            const port = await navigator.serial.requestPort();
                            await port.open({ baudRate: 9600 });
                            this.printerPort = port;
                            this.printerConnected = true;
                            this.showToast('Printer USB/Serial berhasil terhubung!', 'success');
                        } catch (error) {
                            console.error(error);
                            if (error.name === 'NotFoundError' || error.message.includes('No port selected')) {
                                this.showToast('Tidak ada printer USB yang dipilih.', 'error');
                            } else {
                                this.showToast('Tidak bisa terhubung ke printer USB. Pastikan kabel terpasang dengan benar.', 'error');
                            }
                        }
                    }
                },

                async printBase64(base64Data) {
                    if (!this.printerConnected) {
                        this.showToast('Gagal mencetak: Printer belum dihubungkan!', 'error');
                        return;
                    }
                    try {
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

                addToCart(productId, variantId, name, price) {
                    this.triggerCartBounce();
                    // Cek jika produk sudah ada di keranjang
                    const existingIndex = this.cart.findIndex(item => item.product_id === productId && item.product_variant_id === variantId);
                    if (existingIndex > -1) {
                        this.cart[existingIndex].quantity++;
                    } else {
                        this.cart.unshift({ // Add to top
                            product_id: productId,
                            product_variant_id: variantId,
                            name: name,
                            price: price,
                            quantity: 1
                        });
                    }
                    this.calculateVoucherDiscount();
                },

                updateQty(index, change) {
                    let newQty = this.cart[index].quantity + change;
                    if (newQty > 0) {
                        this.cart[index].quantity = newQty;
                    }
                    this.calculateVoucherDiscount();
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                    this.calculateVoucherDiscount();
                },

                clearCart(force = false) {
                    if (force) {
                        this.cart = [];
                        this.activeVoucher = null;
                        this.manualDiscountValue = 0;
                        this.cashPaid = 0;
                        this.customerName = '';
                        this.customerPhone = '';
                        this.loyaltyRedeemStamps = 0;
                        this.activeCustomerLoyalty = null;
                        return;
                    }

                    this.askConfirm(
                        'Kosongkan Keranjang?',
                        'Semua barang di keranjang akan dihapus. Lanjutkan?',
                        () => {
                            this.cart = [];
                            this.activeVoucher = null;
                            this.manualDiscountValue = 0;
                            this.cashPaid = 0;
                            this.customerName = '';
                            this.customerPhone = '';
                            this.loyaltyRedeemStamps = 0;
                            this.activeCustomerLoyalty = null;
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
                
                isVoucherEligible(voucher) {
                    let eligible = true;
                    if (voucher.min_purchase > 0 && parseFloat(this.subtotal) < parseFloat(voucher.min_purchase)) {
                        eligible = false;
                    }
                    if (voucher.min_items > 0 && this.totalItems < voucher.min_items) {
                        eligible = false;
                    }
                    return eligible;
                },
                
                // Diskon Manual State
                manualDiscountType: 'rp',
                manualDiscountValue: 0,
                showManualDiscountModal: false,
                tempManualDiscountType: 'rp',
                tempManualDiscountValue: '',

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

                    const isHighDiscount = (this.tempManualDiscountType === 'percent' && val > 20) || (this.tempManualDiscountType === 'rp' && val > 50000);

                    if (val > 0 && isHighDiscount) {
                        const reasonText = `Diskon manual ${this.tempManualDiscountType === 'percent' ? val + '%' : 'Rp ' + this.formatMoney(val)} memerlukan otorisasi Supervisor (> 20% / > Rp 50k).`;
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

                    if (this.activeVoucher.discount_type === 'percent') {
                        let disc = (this.subtotal * parseFloat(this.activeVoucher.discount_amount)) / 100;
                        if (this.activeVoucher.max_discount && disc > parseFloat(this.activeVoucher.max_discount)) {
                            disc = parseFloat(this.activeVoucher.max_discount);
                        }
                        return disc;
                    } else {
                        return parseFloat(this.activeVoucher.discount_amount);
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
                    this.calculateChange();
                    this.showCheckoutModal = true;
                },

                setCashPaid(amount) {
                    this.cashPaid = amount;
                    this.calculateChange();
                },

                calculateChange() {
                    this.cashChange = this.cashPaid - this.grandTotal;
                },

                submitOrder() {
                    if (this.isCashSelected() && this.cashPaid < this.grandTotal) {
                        this.showToast('Uang yang dibayarkan kurang!', 'error');
                        return;
                    }

                    this.isProcessing = true;
                    
                    const payload = {
                        items: this.cart,
                        discount: this.discount,
                        voucher_id: this.activeVoucher ? this.activeVoucher.id : null,
                        loyalty_redeem_stamps: this.loyaltyRedeemStamps || 0,
                        payment_method: this.paymentMethod,
                        cash_paid: this.cashPaid,
                        cash_change: this.cashChange,
                        customer_name: this.customerName,
                        customer_phone: this.customerPhone,
                        payment_details: {
                            type: this.paymentMethod,
                            // UUID/Idempotency key dapat digenerate di sini
                            idempotency_key: crypto.randomUUID()
                        }
                    };

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
                }
            }));
        });
    </script>
</div>
