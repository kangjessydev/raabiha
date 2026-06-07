<div>
    <x-slot:header>
        <x-global.mobile-subnav title="Selamat Datang Reseller" backUrl="/account" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen py-12 md:py-24">
            <div class="max-w-[720px] w-full mx-auto px-6">
                
                <div class="bg-white border border-[#e5e2de] p-8 md:p-12 text-center mb-8">
                    <svg class="w-16 h-16 text-[#064e3b] mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    <h1 class="font-serif text-[28px] md:text-[36px] font-bold text-[#1c1c1a] tracking-tight mb-4">Pendaftaran Berhasil!</h1>
                    <p class="font-sans text-[15px] text-[#615e57] leading-relaxed mb-6">
                        Terima kasih telah mendaftar sebagai mitra reseller Raabiha. Saat ini akun Anda sedang dalam status <strong>Pending</strong>.
                        Silakan baca syarat dan ketentuan di bawah ini dengan saksama.
                    </p>

                    @if($fee > 0)
                        <div class="bg-[#fcf9f5] p-6 mb-6 inline-block text-left w-full border border-[#e5e2de]">
                            <p class="font-mono text-[10px] uppercase tracking-widest text-[#615e57] mb-2">Biaya Pendaftaran / Deposit Awal</p>
                            <p class="font-sans text-[24px] font-bold text-[#1c1c1a]">Rp{{ number_format($fee, 0, ',', '.') }}</p>
                            <p class="font-sans text-[12px] text-[#615e57] mt-2 mb-4">Mohon segera lakukan pembayaran/deposit sebesar nominal di atas agar tim kami dapat mengaktifkan akun Anda.</p>
                            
                            @if(count($banks) > 0)
                            <div class="bg-white border border-[#e5e2de] p-4 mb-4">
                                <p class="font-mono text-[9px] uppercase tracking-widest text-[#1c1c1a] font-bold mb-3 border-b border-[#e5e2de] pb-2">Tujuan Transfer</p>
                                <div class="flex flex-col gap-3">
                                    @foreach($banks as $bank)
                                    <div class="flex flex-col gap-1">
                                        <span class="font-sans text-[13px] font-bold text-[#1c1c1a] uppercase">{{ $bank['bank_name'] ?? '' }}</span>
                                        <span class="font-mono text-[14px] text-[#064e3b] font-bold tracking-wider">{{ $bank['account_number'] ?? '' }}</span>
                                        <span class="font-sans text-[11px] text-[#615e57] uppercase">A.N {{ $bank['account_name'] ?? '' }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($whatsapp)
                            @php
                                $waMsg = "Halo Admin Raabiha, saya ingin mengkonfirmasi pembayaran pendaftaran akun Reseller dengan rincian sebagai berikut:%0A%0ANama: " . auth()->user()->name . "%0AEmail: " . auth()->user()->email . "%0ATanggal: " . now()->format('d M Y');
                            @endphp
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}?text={{ $waMsg }}" target="_blank" class="w-full bg-[#25D366] text-white px-6 py-3 font-mono text-[10px] font-bold tracking-[0.2em] uppercase hover:bg-[#128C7E] transition-colors inline-flex justify-center items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                                Kirim Bukti Pembayaran ke WhatsApp
                            </a>
                            @endif
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                        <a href="/account?tab=reseller" class="bg-[#064e3b] text-white px-8 py-3 font-mono text-[11px] font-bold tracking-[0.2em] uppercase hover:bg-[#043326] transition-colors inline-block">
                            Masuk Portal Reseller
                        </a>
                    </div>
                </div>

                <div class="bg-white border border-[#e5e2de] p-8 md:p-12">
                    <h2 class="font-serif text-[24px] font-bold text-[#1c1c1a] mb-6">Syarat & Ketentuan Reseller</h2>
                    <div class="prose prose-sm max-w-none font-sans text-[#615e57]">
                        @if($terms)
                            {!! $terms !!}
                        @else
                            <p><strong>1. Keuntungan Reseller</strong></p>
                            <p>Anda akan mendapatkan potongan diskon sebesar {{ $discount }}% untuk setiap pembelian produk di toko kami. Tidak ada batasan minimal pesanan untuk setiap transaksi.</p>
                            
                            <p><strong>2. Proses Pembayaran & Deposit</strong></p>
                            <p>Untuk mengaktifkan akun reseller Anda, silakan transfer biaya deposit ke rekening yang akan diinformasikan oleh admin kami melalui WhatsApp. Deposit ini nantinya akan menjadi saldo awal yang bisa Anda belanjakan.</p>
                            
                            <p><strong>3. Pelanggaran</strong></p>
                            <p>Reseller dilarang menjual produk Raabiha di bawah Harga Eceran Tertinggi (HET) yang telah ditetapkan. Pelanggaran terhadap aturan ini akan berakibat pada penonaktifan akun secara sepihak.</p>
                        @endif
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
