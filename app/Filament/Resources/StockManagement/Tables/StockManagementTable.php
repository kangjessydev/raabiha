<?php

namespace App\Filament\Resources\StockManagement\Tables;

use App\Models\StockLog;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockManagementTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Quick Edit Stok Produk')
            ->description('Edit stok secara langsung. Setiap perubahan akan tercatat di Log Stok.')
            ->searchable()
            ->defaultSort('name', 'asc')
            ->poll('15s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->fontFamily('mono')
                    ->color('gray'),
                TextColumn::make('has_variants')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Punya Varian' : 'Tanpa Varian')
                    ->color(fn(bool $state): string => $state ? 'info' : 'gray'),
                TextColumn::make('stock')
                    ->label('Stok Produk')
                    ->numeric()
                    ->sortable()
                    ->color(fn($record): string => match (true) {
                        !$record->has_variants && $record->stock <= 0 => 'danger',
                        !$record->has_variants && $record->stock <= 5 => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->has_variants)
                            return '— (lihat varian)';
                        return $state . ' pcs';
                    }),
                TextColumn::make('variants_count')
                    ->label('Jumlah Varian')
                    ->counts('variants')
                    ->formatStateUsing(fn($state, $record) => $record->has_variants ? $state . ' varian' : '—')
                    ->color('gray'),
            ])
            ->recordActions([
                Action::make('adjust_stock')
                    ->label('Edit Stok')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->visible(fn($record) => !$record->has_variants && auth()->user()->can('Update:Product'))
                    ->form([
                        TextInput::make('current_stock')
                            ->label('Stok Saat Ini')
                            ->disabled()
                            ->default(fn($record) => $record->stock),
                        TextInput::make('new_stock')
                            ->label('Stok Baru')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Select::make('reason')
                            ->label('Alasan Perubahan')
                            ->required()
                            ->options([
                                'Restock' => 'Restock / Barang Masuk',
                                'Retur' => 'Retur dari Pembeli',
                                'Koreksi' => 'Koreksi / Inventarisasi Fisik',
                                'Rusak' => 'Barang Rusak / Cacat',
                                'Lainnya' => 'Lainnya',
                            ]),
                        Textarea::make('notes')
                            ->label('Catatan (Opsional)'),
                    ])
                    ->action(function (array $data, $record) {
                        $before = (int) $record->stock;
                        $after = (int) $data['new_stock'];
                        $change = $after - $before;

                        $record->update(['stock' => $after]);

                        StockLog::create([
                            'product_id' => $record->id,
                            'type' => $change >= 0 ? 'in' : 'out',
                            'quantity_before' => $before,
                            'quantity_change' => $change,
                            'quantity_after' => $after,
                            'reason' => $data['reason'],
                            'notes' => $data['notes'] ?? null,
                            'user_id' => auth()->id(),
                        ]);
                    })
                    ->successNotificationTitle('Stok berhasil diperbarui dan log tersimpan!'),

                Action::make('adjust_variant_stock')
                    ->label('Edit Stok Varian')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->color('info')
                    ->visible(fn($record) => $record->has_variants && auth()->user()->can('Update:Product'))
                    ->form(function ($record) {
                        $schema = [];

                        // Load variants for this product
                        $variants = $record->variants()->get();

                        foreach ($variants as $variant) {
                            $schema[] = \Filament\Schemas\Components\Fieldset::make($variant->name . ' (' . $variant->sku . ')')
                                ->schema([
                                    TextInput::make('variant_' . $variant->id . '_current')
                                        ->label('Stok Saat Ini')
                                        ->disabled()
                                        ->default($variant->stock)
                                        ->columnSpan(1),
                                    TextInput::make('variant_' . $variant->id . '_new')
                                        ->label('Stok Baru')
                                        ->required()
                                        ->numeric()
                                        ->minValue(0)
                                        ->default($variant->stock)
                                        ->columnSpan(1),
                                ])->columns(2);
                        }

                        $schema[] = Select::make('reason')
                            ->label('Alasan Perubahan (Berlaku untuk semua varian yang diubah)')
                            ->required()
                            ->options([
                                'Restock' => 'Restock / Barang Masuk',
                                'Retur' => 'Retur dari Pembeli',
                                'Koreksi' => 'Koreksi / Inventarisasi Fisik',
                                'Rusak' => 'Barang Rusak / Cacat',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->native(false);

                        $schema[] = Textarea::make('notes')
                            ->label('Catatan (Opsional)');

                        return $schema;
                    })
                    ->action(function (array $data, $record) {
                        $variants = $record->variants()->get();
                        $changesCount = 0;

                        foreach ($variants as $variant) {
                            $newStockKey = 'variant_' . $variant->id . '_new';
                            if (!isset($data[$newStockKey]))
                                continue;

                            $before = (int) $variant->stock;
                            $after = (int) $data[$newStockKey];

                            if ($before !== $after) {
                                $change = $after - $before;

                                $variant->update(['stock' => $after]);

                                StockLog::create([
                                    'product_id' => $record->id,
                                    'product_variant_id' => $variant->id,
                                    'type' => $change >= 0 ? 'in' : 'out',
                                    'quantity_before' => $before,
                                    'quantity_change' => $change,
                                    'quantity_after' => $after,
                                    'reason' => $data['reason'],
                                    'notes' => $data['notes'] ?? null,
                                    'user_id' => auth()->id(),
                                ]);

                                $changesCount++;
                            }
                        }

                        if ($changesCount === 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak ada perubahan stok')
                                ->warning()
                                ->send();

                            // Halt execution to prevent default success notification if we handle it
                            throw new \Filament\Support\Exceptions\Halt();
                        }
                    })
                    ->successNotificationTitle('Stok varian berhasil diperbarui dan log tersimpan!'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('has_variants')
                    ->label('Tipe Produk')
                    ->options([
                        '0' => 'Tanpa Varian',
                        '1' => 'Punya Varian',
                    ]),
            ]);
    }
}
