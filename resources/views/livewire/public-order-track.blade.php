<div class="w-full">
    <!-- Form Lacak -->
    <div class="bg-white border border-[#e5e2de] p-6 md:p-8 shadow-sm">
        <form wire:submit.prevent="track" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Dropdown Kurir -->
                <div class="md:col-span-1">
                    <label for="public_courier" class="block text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-2">Kurir Ekspedisi</label>
                    <select id="public_courier" wire:model="courier" class="w-full bg-[#fcf9f5] border border-[#d1cec9] px-4 py-3 text-sm text-[#1c1c1a] focus:outline-none focus:border-[#064e3b] transition-colors rounded-none appearance-none cursor-pointer">
                        <option value="jnt">J&T Express</option>
                        <option value="jne">JNE Express</option>
                        <option value="sicepat">SiCepat</option>
                        <option value="spx">Shopee Express (SPX)</option>
                    </select>
                </div>
                
                <!-- Input Resi -->
                <div class="md:col-span-2">
                    <label for="public_awb" class="block text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-2">Nomor Resi (AWB)</label>
                    <div class="flex gap-4">
                        <input type="text" id="public_awb" wire:model.defer="awb" placeholder="Masukkan nomor resi Anda..." class="flex-1 bg-[#fcf9f5] border border-[#d1cec9] px-4 py-3 text-sm text-[#1c1c1a] placeholder:text-[#a3a09b] focus:outline-none focus:border-[#064e3b] transition-colors rounded-none">
                        
                        <button type="submit" wire:loading.attr="disabled" class="bg-[#064e3b] text-white px-6 font-mono text-[10px] font-bold tracking-widest uppercase hover:bg-black transition-colors shrink-0">
                            <span wire:loading.remove wire:target="track">LACAK</span>
                            <span wire:loading wire:target="track">...</span>
                        </button>
                    </div>
                    @error('awb') <span class="text-red-500 text-xs mt-1 block font-sans">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <!-- Loading State -->
        <div wire:loading wire:target="track" class="w-full text-center py-12">
            <svg class="animate-spin h-8 w-8 text-[#064e3b] mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="font-mono text-[9px] font-bold tracking-widest uppercase text-[#615e57]">Menghubungkan ke server kurir...</span>
        </div>

        <!-- Error State & Fallback Info -->
        @if($trackingError && !$trackingLoading)
            <div class="mt-8 space-y-6">
                <!-- Error Box -->
                <div class="bg-red-50 border border-red-200 p-4 text-[#c62828]">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div>
                            <h4 class="font-sans text-[14px] font-bold mb-1">Gagal Melacak Paket</h4>
                            <p class="font-sans text-[13px] text-[#555] leading-relaxed">{{ $trackingError }}</p>
                        </div>
                    </div>
                </div>

                <!-- Fallback Info Box -->
                <div class="bg-[#f0ede9] border border-[#e5e2de] p-6 text-[#1c1c1a] font-sans">
                    <h4 class="text-[13px] font-bold uppercase tracking-wider mb-2 font-mono">Pengecekan Alternatif Mandiri</h4>
                    <p class="text-[13px] text-[#615e57] leading-relaxed mb-4">
                        Apabila nomor resi Anda valid namun tidak terdeteksi oleh sistem kami, kemungkinan server kurir pusat sedang mengalami pemeliharaan (maintenance) atau beban tinggi. Anda dapat mengeceknya secara langsung melalui tautan resmi ekspedisi berikut:
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 font-mono text-[10px] font-bold">
                        <a href="https://jet.co.id/track" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-[#064e3b] hover:text-black transition-colors underline">
                            J&T EXPRESS WEBSITE ↗
                        </a>
                        <a href="https://www.jne.co.id/id/tracking/trace" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-[#064e3b] hover:text-black transition-colors underline">
                            JNE WEBSITE ↗
                        </a>
                        <a href="https://www.sicepat.com/checkresi" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-[#064e3b] hover:text-black transition-colors underline">
                            SICEPAT WEBSITE ↗
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tracking Results State -->
        @if($trackingInfo && !$trackingLoading)
            @php
                $summary = $trackingInfo['summary'] ?? [];
                $history = $trackingInfo['history'] ?? [];
            @endphp
            
            <div class="mt-8 border-t border-[#e5e2de] pt-8">
                <!-- Summary Card -->
                <div class="bg-[#064e3b] text-white p-6 mb-8 flex flex-col sm:flex-row justify-between gap-6">
                    <div>
                        <span class="block font-mono text-[9px] uppercase tracking-wider text-white/60 mb-1">Kurir / Resi</span>
                        <h4 class="font-serif text-[18px] font-bold uppercase tracking-wide mb-3">{{ $summary['courier'] ?? $courier }} &mdash; {{ $summary['awb'] ?? $awb }}</h4>
                        
                        <div class="grid grid-cols-2 gap-4 text-xs font-sans mt-2">
                            <div>
                                <span class="block text-white/60 mb-0.5">Pengirim</span>
                                <span class="font-bold">{{ $summary['shipper'] ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-white/60 mb-0.5">Penerima</span>
                                <span class="font-bold">{{ $summary['receiver'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="sm:text-right shrink-0">
                        <span class="block font-mono text-[9px] uppercase tracking-wider text-white/60 mb-1">Status Paket</span>
                        <span class="inline-block bg-[#e1ecd6] text-[#064e3b] px-3 py-1 font-sans text-sm font-bold uppercase tracking-wider">
                            {{ $summary['status'] ?? 'ON PROCESS' }}
                        </span>
                    </div>
                </div>

                <!-- Timeline History -->
                <div class="relative border-l-2 border-[#e5e2de] ml-4 md:ml-6 pl-8 md:pl-10 space-y-8">
                    @forelse($history as $index => $step)
                        <div class="relative">
                            <!-- Timeline Dot -->
                            <span class="absolute -left-[41px] md:-left-[49px] top-1.5 w-4 h-4 rounded-full {{ $index === 0 ? 'bg-[#064e3b] shadow-[0_0_0_4px_rgba(6,78,59,0.1)]' : 'bg-[#e5e2de]' }} border-2 border-white"></span>
                            
                            <!-- Date / Time -->
                            <div class="font-mono text-[10px] font-bold tracking-widest uppercase {{ $index === 0 ? 'text-[#064e3b]' : 'text-[#615e57]' }} mb-1">
                                {{ isset($step['date']) ? date('d M Y • H:i', strtotime($step['date'])) : '-' }}
                            </div>
                            
                            <!-- Desc -->
                            <div class="font-sans text-[13px] {{ $index === 0 ? 'text-[#1c1c1a] font-bold' : 'text-[#615e57] font-medium' }} mb-1">
                                {{ $step['desc'] ?? '' }}
                            </div>
                            
                            <!-- Location -->
                            @if(!empty($step['location']))
                                <div class="flex items-start gap-1 font-sans text-[11px] text-[#615e57] italic">
                                    <svg class="w-3 h-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $step['location'] }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="font-sans text-xs text-[#615e57]">Belum ada riwayat pengiriman.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
