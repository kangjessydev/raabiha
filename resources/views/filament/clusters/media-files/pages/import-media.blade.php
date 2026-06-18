<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Panduan --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-gray-500" />
                    <span>Impor Data dari Excel / CSV</span>
                </div>
            </x-slot>
            <x-slot name="description">
                Gunakan tombol-tombol impor di kanan atas halaman ini untuk mengunggah data secara massal. Pastikan format file sesuai dengan template dari hasil ekspor.
            </x-slot>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-start gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <x-heroicon-o-shopping-bag class="w-6 h-6 text-primary-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Impor Produk</p>
                        <p>Tambahkan banyak produk sekaligus. Kolom wajib: <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">name</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">slug</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">price</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">stock</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">weight</code>.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Impor Pesanan</p>
                        <p>Migrasi atau update data pesanan. Gunakan <strong>Nomor Pesanan</strong> sebagai identifier unik untuk mengupdate pesanan yang sudah ada.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <x-heroicon-o-users class="w-6 h-6 text-info-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Impor Pengguna</p>
                        <p>Tambahkan data akun pelanggan dan reseller. Kolom wajib: <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">name</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">email</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">password</code>, <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">role</code>.</p>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Warning --}}
        <x-filament::section color="warning">
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-warning-600">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    <span>Perhatian Sebelum Impor</span>
                </div>
            </x-slot>
            <ul class="mt-2 list-disc list-inside space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                <li>Gunakan file hasil <strong>Ekspor Data</strong> sebagai template kolom yang tepat agar tidak ada kesalahan format.</li>
                <li>Jika data yang Anda impor <strong>sudah ada</strong> di sistem (berdasarkan ID/Slug/Email), maka sistem akan melakukan <strong>Pembaruan (Update)</strong>, bukan duplikasi.</li>
                <li>Periksa ikon 🔔 lonceng setelah proses selesai untuk melihat laporan baris berhasil dan gagal.</li>
                <li>Baris yang gagal dapat diunduh sebagai file laporan untuk diperbaiki dan diimpor ulang.</li>
                <li>Pastikan queue worker aktif (<code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">php artisan queue:work</code>) agar proses berjalan di background.</li>
            </ul>
        </x-filament::section>

    </div>
</x-filament-panels::page>
