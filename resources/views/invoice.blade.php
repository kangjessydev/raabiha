<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Invoice Pesanan" backUrl="{{ url('/order-success?order=' . $order->order_number) }}" />
    </x-slot:header>

    <div class="page-slide-in">
        <main class="site-main bg-[#fcf9f5] min-h-screen py-16 print:py-0 print:bg-white">
            <div class="max-w-[640px] w-full mx-auto px-6 print:px-0">

                <div class="mb-8 text-center print:text-left print:mb-6">
                    <h1 class="font-serif text-[28px] md:text-[32px] font-bold text-[#1c1c1a] tracking-tight">Invoice</h1>
                    <p class="font-sans text-[14px] text-[#615e57]">RAABIHA.ID</p>
                </div>

                {{-- Reusing the same card component --}}
                <x-order.invoice-card :order="$order" />

                {{-- Action Buttons --}}
                <div class="flex justify-center mt-8 print:hidden">
                    <button onclick="window.print()" class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase text-white bg-[#1c1c1a] px-8 py-4 hover:bg-black transition-colors text-center inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print / Download PDF
                    </button>
                </div>

            </div>
        </main>
    </div>
    
    {{-- Auto trigger print on page load --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</x-layouts.app>
