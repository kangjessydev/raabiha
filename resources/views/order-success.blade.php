<x-layouts.app title="Pesanan Berhasil" robots="noindex, nofollow">
    <x-slot:header>
        <x-global.mobile-subnav title="Pesanan Berhasil" backUrl="/" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen py-16">
            <div class="max-w-[640px] w-full mx-auto px-6">

                {{-- Header --}}
                <div class="text-center mb-10">
                    <div class="w-16 h-16 bg-[#064e3b]/10 text-[#064e3b] rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight mb-3">Terima Kasih!</h1>
                    <p class="font-sans text-[14px] text-[#615e57] max-w-sm mx-auto leading-relaxed">
                        Pesanan Anda telah berhasil dibuat. Selesaikan pembayaran sebelum batas waktu.
                    </p>
                </div>

                @if($order)

                    {{-- Order Summary Card --}}
                    <x-order.invoice-card :order="$order" />
                    
                @else
                    {{-- Fallback jika order tidak ditemukan --}}
                    <div class="border border-[#e5e2de] bg-white p-6 mb-6 text-center">
                        <p class="text-[#615e57] text-sm">Detail pesanan tidak ditemukan. Silakan cek riwayat pesanan Anda.</p>
                    </div>
                @endif
                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row justify-center gap-3 mt-6 print:hidden">
                    @if($order)
                        <a href="{{ url('/invoice/' . $order->order_number) }}" target="_blank"
                           class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-8 py-4 hover:bg-[#f0ede9] transition-colors text-center inline-flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh Invoice
                        </a>
                    @endif
                    <a href="{{ url('/account') }}"
                       class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-8 py-4 hover:bg-[#f0ede9] transition-colors text-center">
                        Lihat Pesanan
                    </a>
                    <a href="{{ url('/') }}" wire:navigate.hover
                       class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-8 py-4 hover:bg-[#043326] transition-colors text-center">
                        Kembali ke Beranda
                    </a>
                </div>

            </div>
        </main>
    </div>
</x-layouts.app>
