<?php

namespace App\Filament\Resources\Cashflows\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CashflowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Catat Pengeluaran (Cash Out)')
                    ->description('Form ini hanya untuk mencatat pengeluaran operasional. Kas masuk otomatis tercatat dari data pesanan.')
                    ->schema([
                        DatePicker::make('transaction_date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        Select::make('category')
                            ->label('Kategori Pengeluaran')
                            ->required()
                            ->options([
                                'Operational' => 'Operasional (Listrik, Internet, dll)',
                                'Marketing' => 'Marketing & Iklan (Ads)',
                                'Packaging' => 'Packaging (Kardus, Lakban, dll)',
                                'Salary' => 'Gaji Pegawai',
                                'Shipping' => 'Biaya Kurir / Ekspedisi',
                                'Other_Out' => 'Pengeluaran Lainnya',
                            ])
                            ->native(false),
                        TextInput::make('amount')
                            ->label('Nominal (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Textarea::make('description')
                            ->label('Catatan / Keterangan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
