<x-filament-widgets::widget>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1: Produk Paling Laku -->
        <div class="p-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400 rounded-lg">
                    <x-heroicon-o-trophy class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">🏆 Produk Paling Laku (Top Sellers)</h3>
                    <p class="text-xs text-gray-500">Penjualan terbanyak pada rentang waktu terpilih</p>
                </div>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($topProducts as $index => $item)
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                #{{ $index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate max-w-[200px]" title="{{ $item->name }}">
                                {{ $item->name }}
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                {{ number_format($item->total_qty) }} Pcs
                            </div>
                            <div class="text-xs text-gray-500">
                                Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-4 text-center text-sm text-gray-500">Belum ada transaksi pada periode terpilih.</div>
                @endforelse
            </div>
        </div>

        <!-- Card 2: Produk Paling Lambat Laku / Dead Stock -->
        <div class="p-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400 rounded-lg">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">🐢 Produk Paling Lambat Laku (Slow Movers)</h3>
                    <p class="text-xs text-gray-500">Penjualan tersedikit / tertahan pada periode terpilih</p>
                </div>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($slowProducts as $index => $item)
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate max-w-[200px]" title="{{ $item->name }}">
                                {{ $item->name }}
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-rose-600 dark:text-rose-400">
                                {{ number_format($item->total_qty) }} Pcs Terjual
                            </div>
                            <div class="text-xs text-gray-500">
                                Sisa Stok: {{ number_format($item->stock) }} Unit
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-4 text-center text-sm text-gray-500">Seluruh produk mengalami pergerakan stok aktif.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
