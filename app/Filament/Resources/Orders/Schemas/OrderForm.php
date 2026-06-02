<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        TextInput::make('order_number')
                            ->required()
                            ->default('RBH-' . strtoupper(uniqid())),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'packed' => 'Packed',
                                'sent' => 'Sent',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending'),
                        Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->required()
                            ->default('unpaid'),
                    ])->columns(2),

                Section::make('Daftar Belanja')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('name', $product->name);
                                            $price = $product->price;
                                            $set('price', $price);
                                            $set('total', $price * ($get('quantity') ?: 1));
                                        }
                                    }),
                                Select::make('product_variant_id')
                                    ->label('Varian')
                                    ->options(function (Get $get) {
                                        $productId = $get('product_id');
                                        if (!$productId) return [];
                                        return \App\Models\ProductVariant::where('product_id', $productId)->pluck('name', 'id');
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $variant = \App\Models\ProductVariant::find($state);
                                        if ($variant) {
                                            $set('name', $variant->name);
                                            $product = \App\Models\Product::find($get('product_id'));
                                            $price = $variant->is_price_override ? $variant->price : ($product ? $product->price : 0);
                                            $set('price', $price);
                                            $set('total', $price * ($get('quantity') ?: 1));
                                        }
                                    }),
                                TextInput::make('name')->required(),
                                TextInput::make('price')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => $set('total', $state * ($get('quantity') ?: 1))),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => $set('total', $state * ($get('price') ?: 0))),
                                TextInput::make('total')
                                    ->numeric()
                                    ->required()
                                    ->readOnly(),
                            ])
                            ->columns(2)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $items = $get('items');
                                $subtotal = 0;
                                if (is_array($items)) {
                                    foreach ($items as $item) {
                                        $subtotal += floatval($item['total'] ?? 0);
                                    }
                                }
                                $set('subtotal', $subtotal);
                                $set('grand_total', $subtotal + floatval($get('shipping_cost') ?? 0) - floatval($get('discount_total') ?? 0));
                            })
                    ]),

                Section::make('Kalkulasi Biaya')
                    ->schema([
                        TextInput::make('subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->default(0),
                        TextInput::make('shipping_cost')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) =>
                                $set('grand_total', floatval($get('subtotal') ?? 0) + floatval($get('shipping_cost') ?? 0) - floatval($get('discount_total') ?? 0))
                            ),
                        TextInput::make('discount_total')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) =>
                                $set('grand_total', floatval($get('subtotal') ?? 0) + floatval($get('shipping_cost') ?? 0) - floatval($get('discount_total') ?? 0))
                            ),
                        TextInput::make('grand_total')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->default(0),
                    ])->columns(2),

                Section::make('Informasi Pengiriman')
                    ->schema([
                        TextInput::make('courier'),
                        TextInput::make('awb_number')->label('Resi'),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
