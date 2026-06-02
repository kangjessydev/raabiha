<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Pesanan Berhasil" backUrl="/" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen flex items-center justify-center py-20">
            <div class="max-w-[600px] w-full mx-auto px-6 text-center">
                
                <div class="w-20 h-20 bg-[#064e3b]/10 text-[#064e3b] rounded-full flex items-center justify-center mx-auto mb-8">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>

                <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight mb-4">Terima Kasih!</h1>
                <p class="font-sans text-[15px] text-[#615e57] mb-8">Pesanan Anda telah berhasil dibuat dan saat ini sedang kami proses. Konfirmasi pesanan telah dikirimkan ke email Anda.</p>
                
                <div class="border border-[#e5e2de] bg-white p-6 mb-10 text-left">
                    <p class="font-mono text-[10px] uppercase tracking-[0.2em] text-[#615e57] mb-2">Nomor Pesanan</p>
                    <p class="font-mono text-[16px] font-bold text-[#1c1c1a] mb-6">#RBH-8842-X9</p>

                    <p class="font-mono text-[10px] uppercase tracking-[0.2em] text-[#615e57] mb-2">Metode Pembayaran</p>
                    <p class="font-sans text-[14px] font-semibold text-[#1c1c1a] mb-2">Bank Transfer (BCA Virtual Account)</p>
                    
                    <div class="bg-[#f0ede9] p-4 mt-4 border-l-2 border-[#064e3b]">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-[#615e57] mb-1">Nomor Virtual Account</p>
                        <p class="font-mono text-[16px] font-bold text-[#1c1c1a] tracking-wider">8077 0812 3456 7890</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ url('/account') }}" wire:navigate class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-[#1c1c1a] border border-[#1c1c1a] px-8 py-4 hover:bg-[#f0ede9] transition-colors w-full sm:w-auto text-center">Lihat Pesanan</a>
                    <a href="{{ url('/') }}" wire:navigate class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#064e3b] px-8 py-4 hover:bg-[#043326] transition-colors w-full sm:w-auto text-center">Kembali ke Beranda</a>
                </div>

            </div>
        </main>
    </div>
</x-layouts.app>
