<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Clusters\ECommerce\ECommerceCluster;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $cluster = ECommerceCluster::class;
    protected static ?int $navigationSort = 11;

    protected static \UnitEnum|string|null $navigationGroup = \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::Transaksi;
    protected static ?string $model = Order::class;

    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $pluralModelLabel = 'Pesanan';


    
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\OrderResource\Widgets\OrderStatsWidget::class,
        ];
    }

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->columns(1)
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Pesanan')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('order_number')->label('ID Pesanan'),
                        \Filament\Infolists\Components\TextEntry::make('created_at')->label('Dibuat Pada')->dateTime('d M Y H:i'),
                        \Filament\Infolists\Components\TextEntry::make('status')->badge()->color(fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'paid' => 'info',
                            'packed' => 'primary',
                            'sent' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            default => 'secondary',
                        }),
                        \Filament\Infolists\Components\TextEntry::make('payment_status')->label('Status Bayar')->badge(),
                        \Filament\Infolists\Components\TextEntry::make('payment_method')->label('Metode Bayar'),
                        \Filament\Infolists\Components\TextEntry::make('source')->label('Sumber Pesanan')->badge(),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('Informasi Pelanggan & Pengiriman')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('customer_info')
                            ->label('Nama & Email Pelanggan')
                            ->state(function ($record) {
                                $name = $record->user_id ? $record->user->name : ($record->shipping_address['name'] ?? 'Guest User');
                                $email = $record->user_id ? $record->user->email : ($record->shipping_address['email'] ?? '-');
                                return $name . ' (' . $email . ')';
                            }),
                        \Filament\Infolists\Components\TextEntry::make('phone')
                            ->label('Nomor HP')
                            ->state(fn ($record) => $record->shipping_address['phone'] ?? '-'),
                        \Filament\Infolists\Components\TextEntry::make('full_address')
                            ->label('Alamat Lengkap')
                            ->state(function ($record) {
                                $addr = $record->shipping_address;
                                if (!$addr) return '-';
                                return collect([
                                    $addr['address'] ?? '',
                                    $addr['district'] ?? '',
                                    $addr['city'] ?? '',
                                    $addr['province'] ?? '',
                                    $addr['postal_code'] ?? '',
                                ])->filter()->implode(', ');
                            })
                            ->columnSpanFull(),
                        \Filament\Infolists\Components\TextEntry::make('shipping_method')->label('Metode Pengiriman'),
                        \Filament\Infolists\Components\TextEntry::make('notes')->label('Catatan Pembeli')->columnSpanFull(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Item Pesanan')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('product.name')->label('Produk'),
                                \Filament\Infolists\Components\TextEntry::make('color')->label('Warna')
                                    ->state(fn ($record) => $record->variant?->color_name ?? '-'),
                                \Filament\Infolists\Components\TextEntry::make('size')->label('Ukuran')
                                    ->state(fn ($record) => $record->variant?->size_name ?? '-'),
                                \Filament\Infolists\Components\TextEntry::make('quantity')->label('Qty'),
                                \Filament\Infolists\Components\TextEntry::make('price')->label('Harga Satuan')->money('IDR'),
                                \Filament\Infolists\Components\TextEntry::make('total')->label('Subtotal')->money('IDR'),
                            ])
                            ->columns(6),
                    ]),

                \Filament\Schemas\Components\Section::make('Rincian Pembayaran')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                        \Filament\Infolists\Components\TextEntry::make('discount')->label('Diskon')->money('IDR'),
                        \Filament\Infolists\Components\TextEntry::make('shipping_cost')->label('Ongkos Kirim')->money('IDR'),
                        \Filament\Infolists\Components\TextEntry::make('grand_total')->label('Total Akhir')->money('IDR')->weight('bold'),
                    ])->columns(4),
            ]);
    }
}
