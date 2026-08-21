<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom `payment_channel` ke tabel cashflows dan mengembalikan
     * UNIQUE constraint yang lebih granular: (order_id, type, source, payment_channel).
     *
     * Ini memungkinkan:
     * - E-Commerce: 1 order = 1 cashflow (payment_channel = NULL, dilindungi UNIQUE)
     * - POS Single: 1 order = 1 cashflow (payment_channel = 'cash')
     * - POS Split:  1 order = N cashflow (payment_channel = 'cash', 'qris', dst — beda nilai, UNIQUE tetap terpenuhi)
     */
    public function up(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            // Tambah kolom payment_channel (nullable agar data lama aman)
            if (!Schema::hasColumn('cashflows', 'payment_channel')) {
                $table->string('payment_channel', 50)->nullable()->after('source');
            }
        });

        // Update data POS yang sudah ada: isi payment_channel dari description.
        // Karena description POS mengandung nama metode bayar dalam kurung (misal: "... (CASH)")
        // Menggunakan PHP loop agar kompatibel dengan SQLite (testing) dan MySQL (production).
        DB::table('cashflows')
            ->where('source', 'pos')
            ->whereNull('payment_channel')
            ->orderBy('id')
            ->each(function ($row) {
                if (preg_match('/\(([^)]+)\)\s*$/', $row->description, $matches)) {
                    DB::table('cashflows')
                        ->where('id', $row->id)
                        ->update(['payment_channel' => strtolower($matches[1])]);
                }
            });

        // Tambahkan UNIQUE constraint baru yang lebih granular
        // (order_id, type, source, payment_channel)
        // MySQL memperlakukan NULL sebagai unik tersendiri, sehingga:
        // - Baris e-commerce dengan payment_channel = NULL tetap dilindungi dari duplikasi
        // - Baris POS dengan payment_channel berbeda tetap bisa ada bersamaan
        Schema::table('cashflows', function (Blueprint $table) {
            $table->unique(
                ['order_id', 'type', 'source', 'payment_channel'],
                'uniq_cashflow_per_channel'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropUnique('uniq_cashflow_per_channel');
        });

        Schema::table('cashflows', function (Blueprint $table) {
            if (Schema::hasColumn('cashflows', 'payment_channel')) {
                $table->dropColumn('payment_channel');
            }
        });
    }
};
