<?php

namespace App\Filament\Resources\Cashflows\Schemas;

use Filament\Schemas\Schema;

class CashflowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        \Filament\Schemas\Components\DatePicker::make('transaction_date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        \Filament\Schemas\Components\Select::make('type')
                            ->label('Jenis Arus Kas')
                            ->options([
                                'in' => 'Uang Masuk (Cash In)',
                                'out' => 'Uang Keluar (Cash Out)',
                            ])
                            ->required()
                            ->live(),
                        \Filament\Schemas\Components\Select::make('category')
                            ->label('Kategori')
                            ->options(function (\Filament\Schemas\Get $get) {
                                $type = $get('type');
                                if ($type === 'in') {
                                    return [
                                        'Sales' => 'Penjualan / Pendapatan Pesanan',
                                        'Deposit' => 'Deposit / Top Up',
                                        'Other_In' => 'Pemasukan Lainnya',
                                    ];
                                }
                                return [
                                    'Operational' => 'Operasional (Listrik, Internet, dll)',
                                    'Marketing' => 'Marketing & Iklan (Ads)',
                                    'Packaging' => 'Packaging (Kardus, Lakban, dll)',
                                    'Salary' => 'Gaji Pegawai',
                                    'Shipping' => 'Biaya Kurir / Ekspedisi',
                                    'Other_Out' => 'Pengeluaran Lainnya',
                                ];
                            })
                            ->required(),
                        \Filament\Schemas\Components\TextInput::make('amount')
                            ->label('Nominal (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        \Filament\Schemas\Components\Select::make('order_id')
                            ->label('Terkait Pesanan (Opsional)')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->nullable(),
                        \Filament\Schemas\Components\Textarea::make('description')
                            ->label('Catatan / Keterangan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
