<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Verifikasi Akun" backUrl="/account" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen flex items-center justify-center py-20">
            <div class="max-w-[480px] w-full mx-auto px-6">
                
                <div class="mb-8 text-center">
                    <a href="/" class="inline-flex items-center gap-2 text-xs font-mono tracking-widest text-[#615e57] hover:text-[#1c1c1a] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        KEMBALI KE BERANDA
                    </a>
                </div>

                <h1 class="font-serif text-[32px] md:text-[40px] font-bold text-[#1c1c1a] tracking-tight mb-4 text-center">Verifikasi Email</h1>
                <p class="font-sans text-[14px] text-[#615e57] text-center mb-8 leading-relaxed">
                    Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke email Anda. Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan ulang.
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 p-4 bg-[#064e3b]/10 border border-[#064e3b]/20 text-[#064e3b] font-sans text-xs text-center leading-relaxed">
                        Tautan verifikasi baru telah berhasil dikirim ke alamat email yang Anda berikan saat mendaftar.
                    </div>
                @endif

                <div class="flex flex-col gap-4">
                    <button wire:click="resend" class="w-full bg-[#064e3b] hover:bg-[#043326] text-white py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors flex items-center justify-center gap-2">
                        <span wire:loading wire:target="resend" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>Kirim Ulang Email Verifikasi</span>
                    </button>

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full border border-[#1c1c1a] hover:bg-[#f0ede9] text-[#1c1c1a] py-4 font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors">
                            Keluar dari Akun
                        </button>
                    </form>
                </div>

            </div>
        </main>
    </div>
</div>
