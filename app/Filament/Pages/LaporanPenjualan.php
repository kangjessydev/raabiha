<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ExportAction;
use App\Filament\Exports\OrderExporter;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid as FormGrid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class LaporanPenjualan extends Page implements HasTable, HasForms
{
    use HasPageShield;
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static \UnitEnum|string|null $navigationGroup = 'Laporan & Keuangan';
    protected static ?string $title = 'Laporan Penjualan & Performa Produk';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.laporan-penjualan';

    public ?array $filters = [
        'preset' => '30_days',
        'channel' => 'all',
        'created_from' => null,
        'created_until' => null,
    ];

    public function mount(): void
    {
        $this->filters['created_from'] = now()->subDays(29)->toDateString();
        $this->filters['created_until'] = now()->toDateString();
        $this->form->fill($this->filters);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Segmentasi Penjualan')
                    ->schema([
                        FormGrid::make(4)
                            ->schema([
                                Select::make('channel')
                                    ->label('Kanal Penjualan (Channel)')
                                    ->options([
                                        'all' => 'Semua Channel (Omnichannel)',
                                        'online' => '🛒 E-Commerce Website',
                                        'pos' => '🏪 POS Toko Fisik',
                                    ])
                                    ->default('all')
                                    ->reactive()
                                    ->afterStateUpdated(fn () => $this->applyFilters()),
                                Select::make('preset')
                                    ->label('Rentang Waktu')
                                    ->options([
                                        'today' => 'Hari Ini',
                                        'yesterday' => 'Kemarin',
                                        '7_days' => '7 Hari Terakhir',
                                        '30_days' => '30 Hari Terakhir',
                                        'this_month' => 'Bulan Ini',
                                        'this_year' => 'Tahun Ini',
                                        'custom' => 'Kustom Tanggal',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        switch ($state) {
                                            case 'today':
                                                $set('created_from', now()->toDateString());
                                                $set('created_until', now()->toDateString());
                                                break;
                                            case 'yesterday':
                                                $set('created_from', now()->subDay()->toDateString());
                                                $set('created_until', now()->subDay()->toDateString());
                                                break;
                                            case '7_days':
                                                $set('created_from', now()->subDays(6)->toDateString());
                                                $set('created_until', now()->toDateString());
                                                break;
                                            case '30_days':
                                                $set('created_from', now()->subDays(29)->toDateString());
                                                $set('created_until', now()->toDateString());
                                                break;
                                            case 'this_month':
                                                $set('created_from', now()->startOfMonth()->toDateString());
                                                $set('created_until', now()->endOfMonth()->toDateString());
                                                break;
                                            case 'this_year':
                                                $set('created_from', now()->startOfYear()->toDateString());
                                                $set('created_until', now()->endOfYear()->toDateString());
                                                break;
                                        }
                                        $this->applyFilters();
                                    }),
                                DatePicker::make('created_from')
                                    ->label('Dari Tanggal')
                                    ->reactive()
                                    ->disabled(fn (callable $get) => $get('preset') !== 'custom')
                                    ->afterStateUpdated(fn () => $this->applyFilters()),
                                DatePicker::make('created_until')
                                    ->label('Sampai Tanggal')
                                    ->reactive()
                                    ->disabled(fn (callable $get) => $get('preset') !== 'custom')
                                    ->afterStateUpdated(fn () => $this->applyFilters()),
                            ]),
                    ]),
            ])
            ->statePath('filters');
    }

    public function applyFilters(): void
    {
        $this->dispatch('filtersUpdated', filters: $this->filters);
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(OrderExporter::class)
                ->label('Ekspor Penjualan (Excel/CSV)')
                ->icon('heroicon-o-arrow-down-tray')
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->when($this->filters['channel'] === 'online', fn (Builder $q) => $q->whereNull('pos_session_id'))
                    ->when($this->filters['channel'] === 'pos', fn (Builder $q) => $q->whereNotNull('pos_session_id'))
                    ->when($this->filters['created_from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                    ->when($this->filters['created_until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
                    ->latest()
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Waktu WIB')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('pos_session_id')
                    ->label('Channel')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? '🏪 POS Kasir' : '🛒 E-Commerce')
                    ->color(fn ($state) => $state ? 'success' : 'info'),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->placeholder(fn ($record) => $record->user?->name ?? 'Tamu Kasir')
                    ->searchable(),
                TextColumn::make('grand_total')
                    ->label('Total Pembayaran')
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
            ]);
    }
}
