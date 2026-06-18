<?php

namespace App\Filament\Imports;

use App\Models\Voucher;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class VoucherImporter extends Importer
{
    protected static ?string $model = Voucher::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nama')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('code')
                ->label('Kode')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('discount_type')
                ->label('Tipe Diskon')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('discount_amount')
                ->label('Nilai Diskon')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('is_shipping_voucher')
                ->label('Voucher Ongkir?')
                ->boolean()
                ->rules(['boolean', 'nullable']),
            ImportColumn::make('min_purchase')
                ->label('Min. Belanja')
                ->numeric()
                ->rules(['integer', 'nullable']),
            ImportColumn::make('min_items')
                ->label('Min. Item')
                ->numeric()
                ->rules(['integer', 'nullable']),
            ImportColumn::make('max_discount')
                ->label('Maks. Diskon')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('specific_users'),
            ImportColumn::make('exclude_resellers')
                ->label('Kecuali Reseller?')
                ->boolean()
                ->rules(['boolean', 'nullable']),
            ImportColumn::make('is_stackable')
                ->label('Bisa Ditumpuk?')
                ->boolean()
                ->rules(['boolean', 'nullable']),
            ImportColumn::make('free_gift_product_id')
                ->label('ID Produk Hadiah')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('max_uses')
                ->label('Kuota')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('max_uses_per_user')
                ->label('Maks. Pakai per Akun')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('used_count')
                ->label('Sudah Digunakan')
                ->numeric()
                ->rules(['integer', 'nullable']),
            ImportColumn::make('starts_at')
                ->label('Mulai')
                ->rules(['datetime']),
            ImportColumn::make('expires_at')
                ->label('Berakhir')
                ->rules(['datetime']),
            ImportColumn::make('is_active')
                ->label('Aktif?')
                ->boolean()
                ->rules(['boolean', 'nullable']),
        ];
    }

    public function resolveRecord(): Voucher
    {
        return Voucher::firstOrNew([
            'code' => $this->data['code'] ?? null,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your voucher import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
