<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Valuasi Modal (HPP)</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">
                    Rp {{ number_format($totalHppValuation, 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-400 mt-1">Total Aset Gudang Fisik</div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nilai Jual Retail</div>
                <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-2">
                    Rp {{ number_format($totalRetailValuation, 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-400 mt-1">Proyeksi Omset Penjualan</div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Stok Kritis (≤ 5 Pcs)</div>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-2">
                    {{ number_format($lowStockCount) }} SKU
                </div>
                <div class="text-xs text-gray-400 mt-1">Perlu Restock Segera</div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Stok Habis (0 Pcs)</div>
                <div class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-2">
                    {{ number_format($outOfStockCount) }} SKU
                </div>
                <div class="text-xs text-gray-400 mt-1">Habis Terjual</div>
            </div>
        </div>

        <!-- Tabel Valuasi & Inventaris -->
        <div>
            <h3 class="text-lg font-bold mb-4 px-1 text-gray-900 dark:text-white">Rincian Valuasi & Sisa Stok Produk</h3>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
