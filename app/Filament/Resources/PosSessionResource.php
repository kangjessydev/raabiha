<?php

namespace App\Filament\Resources;

use App\Models\PosSession;
use BackedEnum;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PosSessionResource extends Resource
{
    protected static ?string $model = PosSession::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static \UnitEnum|string|null $navigationGroup = 'Kasir & Toko Fisik (POS)';

    protected static ?string $navigationLabel = 'Shift Kasir';
    protected static ?string $modelLabel = 'Shift Kasir';
    protected static ?string $pluralModelLabel = 'Shift Kasir';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('opened_at', 'desc')
            ->columns([
                TextColumn::make('cashier.name')
                    ->label('Nama Kasir')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('Status Shift')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'open'   => 'Shift Aktif',
                        'closed' => 'Selesai',
                        default  => ucwords($state ?? '-'),
                    })
                    ->color(fn ($state) => match ($state) {
                        'open'   => 'success',
                        'closed' => 'gray',
                        default  => 'secondary',
                    }),
                TextColumn::make('opened_at')
                    ->label('Jam Masuk (Buka)')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Jam Keluar (Tutup)')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Durasi Kerja')
                    ->state(function ($record) {
                        $start = $record->opened_at;
                        $end   = $record->closed_at ?: now();
                        if (!$start) return '-';

                        $diffSeconds = $start->diffInSeconds($end);
                        $hours   = floor($diffSeconds / 3600);
                        $minutes = floor(($diffSeconds % 3600) / 60);

                        $timeStr = ($hours > 0 ? "{$hours} Jam " : "") . "{$minutes} Menit";
                        return $record->status === 'open' ? "Berlangsung ({$timeStr})" : $timeStr;
                    })
                    ->badge()
                    ->color(fn ($record) => $record->status === 'open' ? 'warning' : 'info'),
                TextColumn::make('opening_cash')
                    ->label('Kas Awal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('expected_ending_cash')
                    ->label('Sistem (Hitungan)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('actual_ending_cash')
                    ->label('Aktual Kasir')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('difference_cash')
                    ->label('Selisih Kas')
                    ->money('IDR')
                    ->weight('bold')
                    ->color(fn ($state) => match (true) {
                        $state < 0 => 'danger',
                        $state > 0 => 'warning',
                        default    => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Total Transaksi')
                    ->formatStateUsing(fn ($state) => "{$state} Nota")
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Shift')
                    ->native(false)
                    ->options([
                        'open'   => 'Shift Sedang Aktif',
                        'closed' => 'Shift Selesai (Tutup)',
                    ]),
                SelectFilter::make('cashier_id')
                    ->relationship('cashier', 'name')
                    ->label('Kasir')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\Action::make('forceClose')
                    ->label('Tutup Paksa Shift')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Force Close Shift Kasir')
                    ->modalDescription('Apakah Anda yakin ingin menutup sesi kasir ini secara paksa dari Admin Panel? Gunakan fitur ini jika kasir lupa tutup shift atau berhalangan hadir.')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('actual_ending_cash')
                            ->label('Jumlah Kas Setoran Aktual (Rp)')
                            ->helperText('Masukkan nominal uang fisik di laci kasir (Default: Hitungan Sistem)')
                            ->numeric()
                            ->required()
                            ->default(fn ($record) => $record->expected_ending_cash ?? 0),
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Catatan Penutupan Paksa')
                            ->default('Ditutup paksa oleh Admin/Owner dari Admin Panel Filament')
                            ->required(),
                    ])
                    ->visible(fn ($record) => $record->status === 'open')
                    ->action(function ($record, array $data) {
                        $expected = (float) ($record->expected_ending_cash ?? 0);
                        $actual   = (float) ($data['actual_ending_cash'] ?? 0);
                        $diff     = $actual - $expected;

                        $record->update([
                            'status'               => 'closed',
                            'closed_at'            => now(),
                            'actual_ending_cash'   => $actual,
                            'difference_cash'      => $diff,
                            'notes'                => $data['notes'] ?: 'Ditutup paksa oleh Admin/Owner',
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Shift Kasir Berhasil Ditutup Paksa')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Sesi & Durasi Jam Kerja Shift')
                    ->schema([
                        TextEntry::make('cashier.name')->label('Nama Kasir')->weight('bold'),
                        TextEntry::make('status')->label('Status Shift')->badge()->formatStateUsing(fn ($state) => match ($state) {
                            'open'   => 'Shift Sedang Aktif',
                            'closed' => 'Shift Selesai (Tutup)',
                            default  => ucwords($state ?? '-'),
                        }),
                        TextEntry::make('opened_at')->label('Waktu Buka (Jam Masuk)')->dateTime('d M Y H:i:s'),
                        TextEntry::make('closed_at')->label('Waktu Tutup (Jam Keluar)')->dateTime('d M Y H:i:s')->placeholder('Shift Masih Berlangsung'),
                        TextEntry::make('duration')
                            ->label('Total Durasi Jam Kerja Shift')
                            ->state(function ($record) {
                                $start = $record->opened_at;
                                $end   = $record->closed_at ?: now();
                                if (!$start) return '-';

                                $diffSeconds = $start->diffInSeconds($end);
                                $hours   = floor($diffSeconds / 3600);
                                $minutes = floor(($diffSeconds % 3600) / 60);

                                $timeStr = ($hours > 0 ? "{$hours} Jam " : "") . "{$minutes} Menit";
                                return $record->status === 'open' ? "Sedang Berlangsung ({$timeStr})" : $timeStr;
                            })
                            ->weight('bold'),
                        TextEntry::make('orders_count')
                            ->state(fn ($record) => $record->orders()->count() . ' Nota Transaksi'),
                    ])->columns(3),

                Section::make('Rincian Pembukuan Kas Shift')
                    ->schema([
                        TextEntry::make('opening_cash')->label('Modal Kas Awal')->money('IDR'),
                        TextEntry::make('expected_ending_cash')->label('Ekspektasi Kas Sistem')->money('IDR'),
                        TextEntry::make('actual_ending_cash')->label('Aktual Setoran Kasir')->money('IDR'),
                        TextEntry::make('difference_cash')->label('Selisih Kas (Minus / Surplus)')->money('IDR')->weight('bold'),
                        TextEntry::make('notes')->label('Catatan Shift')->columnSpanFull()->default('-'),
                    ])->columns(4),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PosSessionResource\Pages\ListPosSessions::route('/'),
        ];
    }
}
