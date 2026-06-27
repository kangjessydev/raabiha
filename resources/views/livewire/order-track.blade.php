<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Lacak Pesanan" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen pb-20 md:pb-0">
            <div class="max-w-[800px] mx-auto px-4 sm:px-6 py-8 md:py-16">
                
                <!-- Back Button & Header -->
                <div class="hidden md:flex items-center gap-4 mb-8">
                    <a href="/account" class="w-10 h-10 border border-[#e5e2de] bg-white flex items-center justify-center hover:bg-[#f0ede9] transition-colors shrink-0">
                        <svg class="w-5 h-5 text-[#1c1c1a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div>
                        <h1 class="font-serif text-[28px] font-bold text-[#1c1c1a] leading-tight">Status Pengiriman</h1>
                        <p class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#615e57]">Pesanan #{{ $order->order_number }}</p>
                    </div>
                </div>

                <div class="bg-white border border-[#e5e2de] shadow-sm">
                    <!-- Tracking Header Overview -->
                    <div class="p-6 md:p-8 border-b border-[#e5e2de] bg-[#064e3b] text-white">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div>
                                <p class="font-mono text-[10px] font-bold tracking-widest uppercase text-white/70 mb-1">Kurir Pengiriman</p>
                                <p class="font-serif text-[24px] font-bold tracking-widest uppercase mb-4">{{ explode('|', $order->courier)[0] }}</p>
                                
                                <p class="font-mono text-[10px] font-bold tracking-widest uppercase text-white/70 mb-1">Nomor Resi</p>
                                <div class="flex items-center gap-2">
                                    <p class="font-sans text-[18px] font-bold tracking-wider">{{ $order->awb_number }}</p>
                                    <button x-data="{ copied: false }" @click="navigator.clipboard.writeText('{{ $order->awb_number }}'); copied = true; setTimeout(() => copied = false, 2000)" class="p-2 hover:bg-white/10 rounded transition-colors relative flex items-center justify-center" title="Salin Resi">
                                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        <svg x-show="copied" style="display: none;" class="w-4 h-4 text-[#e1ecd6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        <span x-show="copied" style="display: none;" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-white text-[#064e3b] text-[10px] font-bold px-2 py-1 shadow-sm whitespace-nowrap font-sans">Tersalin!</span>
                                    </button>
                                </div>
                            </div>
                            <div class="md:text-right">
                                <p class="font-mono text-[10px] font-bold tracking-widest uppercase text-white/70 mb-1">Status Saat Ini</p>
                                @if($trackingLoading)
                                    <div class="flex items-center gap-2 md:justify-end">
                                        <svg class="animate-spin h-5 w-5 text-white/70" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="font-sans text-[16px] font-bold text-white">Memuat...</span>
                                    </div>
                                @elseif($trackingError)
                                    <span class="inline-block bg-red-500/20 text-white border border-red-500/30 px-3 py-1 font-mono text-[11px] font-bold tracking-widest uppercase mt-1">Tidak Ditemukan</span>
                                @elseif($trackingInfo && isset($trackingInfo['summary']['status']))
                                    <p class="font-sans text-[20px] font-bold text-[#e1ecd6]">{{ $trackingInfo['summary']['status'] }}</p>
                                @else
                                    <span class="inline-block bg-white/20 text-white px-3 py-1 font-mono text-[11px] font-bold tracking-widest uppercase mt-1">Menunggu Info</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Section -->
                    <div class="p-6 md:p-8 bg-white min-h-[300px]">
                        @if($trackingLoading)
                            <div class="flex flex-col items-center justify-center py-12">
                                <svg class="animate-spin h-8 w-8 text-[#064e3b] mb-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#615e57]">Menghubungkan ke server kurir...</span>
                            </div>
                        @elseif($trackingError)
                            <div class="flex flex-col items-center justify-center py-8 text-center max-w-md mx-auto">
                                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <h3 class="font-serif text-[18px] font-semibold text-[#1c1c1a] mb-2">Oops, ada kendala</h3>
                                <p class="font-sans text-[14px] text-[#615e57] mb-6">{{ $trackingError }}</p>
                                <button wire:click="trackPackage" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-6 py-3 hover:bg-[#1c1c1a] hover:text-white transition-colors">Coba Lagi</button>
                            </div>
                        @elseif($trackingInfo)
                            @php
                                $history = $trackingInfo['history'] ?? [];
                            @endphp
                            @if(empty($history))
                                <div class="text-center py-12">
                                    <p class="font-sans text-[14px] text-[#615e57]">Belum ada riwayat perjalanan paket dari kurir.</p>
                                </div>
                            @else
                                <div class="relative border-l-2 border-[#e5e2de] ml-4 md:ml-6 my-4 pl-8 md:pl-10 space-y-8">
                                    @foreach($history as $index => $step)
                                        <div class="relative">
                                            <!-- Timeline Dot -->
                                            <span class="absolute -left-[41px] md:-left-[49px] top-1.5 w-4 h-4 rounded-full {{ $index === 0 ? 'bg-[#064e3b] shadow-[0_0_0_4px_rgba(6,78,59,0.1)]' : 'bg-[#e5e2de]' }} border-2 border-white"></span>
                                            
                                            <!-- Date/Time -->
                                            <div class="font-mono text-[10px] font-bold tracking-widest uppercase {{ $index === 0 ? 'text-[#064e3b]' : 'text-[#615e57]' }} mb-1">
                                                {{ isset($step['date']) ? date('d M Y • H:i', strtotime($step['date'])) : '' }}
                                            </div>
                                            
                                            <!-- Description -->
                                            <div class="font-sans text-[14px] {{ $index === 0 ? 'text-[#1c1c1a] font-bold' : 'text-[#1c1c1a] font-medium' }} mb-1">
                                                {{ $step['desc'] ?? '' }}
                                            </div>
                                            
                                            <!-- Location -->
                                            @if(!empty($step['location']))
                                                <div class="flex items-start gap-1 font-sans text-[12px] text-[#615e57] italic">
                                                    <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    {{ $step['location'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Need Help Block -->
                <div class="mt-8 text-center bg-[#f0ede9] p-6 border border-[#e5e2de]">
                    <p class="font-sans text-[14px] text-[#1c1c1a] font-medium mb-2">Tidak bisa melacak pesanan atau status tidak berubah?</p>
                    <a href="/cara-lacak-pesanan" class="font-mono text-[10px] font-bold tracking-widest uppercase text-[#064e3b] hover:underline inline-flex items-center gap-1">
                        Klik di sini untuk bantuan
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

            </div>
        </main>
    </div>
</div>
