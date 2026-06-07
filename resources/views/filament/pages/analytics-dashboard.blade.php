<x-filament-panels::page>
    <div class="fi-ta-content bg-white ring-1 ring-gray-950/5 rounded-xl shadow-sm overflow-hidden dark:bg-gray-900 dark:ring-white/10 w-full min-h-[700px] flex flex-col">
        @if(empty($lookerStudioUrl))
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <x-heroicon-o-chart-bar class="w-8 h-8 text-gray-500 dark:text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Google Analytics Belum Dikonfigurasi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">
                    Silakan masukkan URL Embed Google Looker Studio Anda di Pengaturan Global agar grafik pengunjung dapat ditampilkan secara real-time di halaman ini.
                </p>
                <x-filament::button tag="a" href="{{ url('/admin/settings/global-settings') }}">
                    Buka Pengaturan Global
                </x-filament::button>
            </div>
        @else
            <div class="flex-1 w-full h-full min-h-[700px] relative">
                <iframe 
                    src="{{ $lookerStudioUrl }}" 
                    class="absolute inset-0 w-full h-full border-0" 
                    allowfullscreen
                    sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"
                ></iframe>
            </div>
        @endif
    </div>
</x-filament-panels::page>
