<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ExportAction;
use App\Filament\Exports\OrderExporter;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;

class LaporanPenjualan extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = \App\Filament\Clusters\Dashboard\DashboardCluster::class;

    protected static ?string $title = 'Laporan Penjualan';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.laporan-penjualan';

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(OrderExporter::class)
                ->label('Ekspor Data (Excel/CSV)')
                ->icon('heroicon-o-arrow-down-tray')
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest())
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->placeholder(fn ($record) => $record->shipping_address['name'] ?? 'Guest User')
                    ->searchable(),
                TextColumn::make('items.product.name')
                    ->label('Produk')
                    ->badge()
                    ->separator(', '),
                TextColumn::make('grand_total')
                    ->label('Total Belanja')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Status Bayar')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('payment_method')
                    ->label('Metode Bayar')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status Pesanan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        default => 'secondary',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Pesanan')
                    ->options([
                        'pending'    => 'Pending',
                        'processing' => 'Diproses',
                        'shipped'    => 'Dikirim',
                        'completed'  => 'Selesai',
                        'cancelled'  => 'Dibatalkan',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Belum Bayar',
                        'paid'    => 'Lunas',
                        'failed'  => 'Gagal',
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
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
            ]);
    }
}
