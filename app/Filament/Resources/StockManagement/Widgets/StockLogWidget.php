<?php

namespace App\Filament\Resources\StockManagement\Widgets;

use App\Models\StockLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StockLogWidget extends BaseWidget
{
    protected static ?string $heading = 'Log Perubahan Stok Terbaru';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockLog::query()
                    ->with(['product', 'variant', 'user'])
                    ->latest()
                    ->limit(20)
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('variant.name')
                    ->label('Varian')
                    ->default('—')
                    ->color('gray'),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in'         => 'Masuk',
                        'out'        => 'Keluar',
                        'adjustment' => 'Koreksi',
                        default      => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in'         => 'success',
                        'out'        => 'danger',
                        'adjustment' => 'warning',
                        default      => 'gray',
                    }),
                TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->numeric()
                    ->alignCenter(),
                TextColumn::make('quantity_change')
                    ->label('Perubahan')
                    ->numeric()
                    ->alignCenter()
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state): string => ($state >= 0 ? '+' : '') . $state),
                TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->numeric()
                    ->alignCenter()
                    ->weight('semibold'),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('user.name')
                    ->label('Oleh')
                    ->default('Sistem')
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
