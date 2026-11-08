<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Awcodes\Curator\Models\Media;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['category', 'variants']))
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('name', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->sortable()
                    ->wrap()
                    ->html()
                    ->formatStateUsing(function (Product $record) {
                        $mediaIds = $record->images ?? [];
                        $firstMediaId = is_array($mediaIds) ? ($mediaIds[0] ?? null) : $mediaIds;

                        $imgSrc = null;
                        if (! empty($firstMediaId)) {
                            if (is_string($firstMediaId) && (str_starts_with($firstMediaId, 'http://') || str_starts_with($firstMediaId, 'https://'))) {
                                $imgSrc = $firstMediaId;
                            } elseif (is_numeric($firstMediaId)) {
                                $media = Media::find($firstMediaId);
                                $imgSrc = $media ? $media->url : null;
                            } elseif (is_string($firstMediaId)) {
                                $imgSrc = asset('storage/'.ltrim($firstMediaId, '/'));
                            }
                        }

                        $catName = e($record->category?->name ?? 'Tanpa Kategori');
                        $name = e($record->name);

                        $imgHtml = $imgSrc
                            ? "<img src='{$imgSrc}' alt='{$name}' style='width:2.5rem; height:2.5rem; border-radius:9999px; object-fit:cover; border: 2px solid #e5e7eb; flex-shrink: 0;' />"
                            : "<div style='width:2.5rem; height:2.5rem; border-radius:9999px; background-color:#f3f4f6; display:flex; align-items:center; justify-content:center; flex-shrink:0;'><svg style='width:1.25rem; height:1.25rem; color:#6b7280;' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' d='M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z' /></svg></div>";

                        return "
                            <div style='display:flex; align-items:center; gap:0.75rem; min-width: 250px;'>
                                {$imgHtml}
                                <div style='display:flex; flex-direction:column; min-width: 0; width: 100%;'>
                                    <span style='font-weight:500; font-size:0.875rem; white-space:normal; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;'>{$name}</span>
                                    <span style='font-size:0.75rem; color:#6b7280; margin-top:0.125rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'>{$catName}</span>
                                </div>
                            </div>
                        ";
                    }),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('reseller_price')
                    ->label('Harga Reseller')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('weight')
                    ->label('Berat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->formatStateUsing(function ($state, Product $record) {
                        if ($record->has_variants) {
                            $totalVariantStock = $record->variants->sum('stock');

                            return $totalVariantStock.' pcs (varian)';
                        }

                        return ($state ?? 0).' pcs';
                    })
                    ->color(function ($state, Product $record) {
                        $stock = $record->has_variants ? $record->variants->sum('stock') : ($state ?? 0);

                        return match (true) {
                            $stock <= 0 => 'danger',
                            $stock <= 5 => 'warning',
                            default => 'success',
                        };
                    }),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Jumlah Varian')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : '-'),
                TextColumn::make('is_custom')
                    ->label('Asal Produk')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Dibuat via POS' : 'Admin Panel')
                    ->color(fn ($state) => $state ? 'warning' : 'success')
                    ->icon(fn ($state) => $state ? 'heroicon-m-shopping-bag' : 'heroicon-m-computer-desktop')
                    ->sortable(),
                TextColumn::make('channel_visibility')
                    ->label('Kanal Penjualan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pos_only' => 'POS Kasir',
                        'online_only' => 'E-Commerce',
                        default => ucwords($state ?? '-'),
                    })
                    ->color(fn ($state) => match ($state) {
                        'pos_only' => 'warning',
                        'online_only' => 'info',
                        default => 'secondary',
                    })
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif?')
                    ->disabled(fn () => ! auth()->user()->can('Update:Product')),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('channel_visibility')
                    ->label('Kanal Penjualan')
                    ->native(false)
                    ->options([
                        'pos_only' => 'POS Kasir Saja (Toko Fisik)',
                        'online_only' => 'E-Commerce Saja (Website Online)',
                    ]),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),
                TernaryFilter::make('has_free_shipping')
                    ->label('Gratis Ongkir')
                    ->placeholder('Semua')
                    ->trueLabel('Ya, Gratis Ongkir')
                    ->falseLabel('Tidak')
                    ->native(false),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->columns(2)
                    ->columnSpan('full')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('Ditambahkan mulai '.Carbon::parse($data['created_from'])->translatedFormat('d M Y'))
                                ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('Ditambahkan sampai '.Carbon::parse($data['created_until'])->translatedFormat('d M Y'))
                                ->removeField('created_until');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersLayout(FiltersLayout::Dropdown)
            ->filtersFormWidth(Width::FourExtraLarge)
            ->filtersFormColumns(3)
            ->recordActions([
                Action::make('view_frontend')
                    ->label('Lihat di Toko')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Product $record): string => url('/product/'.$record->slug))
                    ->openUrlInNewTab()
                    ->color('info')
                    ->iconButton()
                    ->tooltip('Lihat di Toko'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Ubah Produk'),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus Produk'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('change_channel_visibility')
                        ->label('Ubah Visibilitas (POS/Web)')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->form([
                            Select::make('channel_visibility')
                                ->label('Tampil di')
                                ->options([
                                    'both' => 'Web & POS Kasir',
                                    'online_only' => 'Web Toko Online Saja',
                                    'pos_only' => 'POS Kasir Saja',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update(['channel_visibility' => $data['channel_visibility']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
