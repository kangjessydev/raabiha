<?php

namespace App\Filament\Resources\Cashflows\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CashflowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->schema([
                        DatePicker::make('transaction_date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        Select::make('type')
                            ->label('Jenis Arus Kas')
                            ->options([
                                'in'  => 'Uang Masuk (Cash In)',
                                'out' => 'Uang Keluar (Cash Out)',
                            ])
                            ->required()
                            ->live(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options(function (Get $get) {
                                $type = $get('type');
                                if ($type === 'in') {
                                    return [
                                        'Sales'    => 'Penjualan / Pendapatan Pesanan',
                                        'Deposit'  => 'Deposit / Top Up',
                                        'Other_In' => 'Pemasukan Lainnya',
                                    ];
                                }
                                return [
                                    'Operational' => 'Operasional (Listrik, Internet, dll)',
                                    'Marketing'   => 'Marketing & Iklan (Ads)',
                                    'Packaging'   => 'Packaging (Kardus, Lakban, dll)',
                                    'Salary'      => 'Gaji Pegawai',
                                    'Shipping'    => 'Biaya Kurir / Ekspedisi',
                                    'Other_Out'   => 'Pengeluaran Lainnya',
                                ];
                            })
                            ->required(),
                        TextInput::make('amount')
                            ->label('Nominal (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Select::make('order_id')
                            ->label('Terkait Pesanan (Opsional)')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->nullable(),
                        Textarea::make('description')
                            ->label('Catatan / Keterangan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}

