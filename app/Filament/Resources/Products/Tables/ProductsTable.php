<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search): \Illuminate\Database\Eloquent\Builder {
                        return $query->where('name', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->sortable()
                    ->wrap()
                    ->html()
                    ->formatStateUsing(function (\App\Models\Product $record) {
                        $mediaIds = $record->images ?? [];
                        $firstMediaId = is_array($mediaIds) ? ($mediaIds[0] ?? null) : $mediaIds;
                        $media = $firstMediaId ? \Awcodes\Curator\Models\Media::find($firstMediaId) : null;
                        
                        $imgSrc = $media ? $media->url : null;
                        
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
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Jumlah Varian')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : '-'),
                \Filament\Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif?'),
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
                \Filament\Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('view_frontend')
                    ->label('Lihat di Toko')
                    ->icon('heroicon-o-eye')
                    ->url(fn (\App\Models\Product $record): string => url('/product/' . $record->slug))
                    ->openUrlInNewTab()
                    ->color('info')
                    ->iconButton()
                    ->tooltip('Lihat di Toko'),
                \Filament\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Ubah Produk'),
                \Filament\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus Produk'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
