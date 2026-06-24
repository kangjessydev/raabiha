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
            ->components(array_merge(
                self::isRequestChangeActive() ? [
                    Section::make('Alasan Pengajuan Perubahan')
                        ->schema([
                            Textarea::make('change_reason')
                                ->label('Alasan Perubahan')
                                ->placeholder('Jelaskan alasan mengapa data pesanan ini perlu diubah...')
                                ->required()
                                ->rows(3),
                        ])
                ] : [],
                [
                    \Filament\Schemas\Components\Group::make()
                    ->schema([
                        Section::make('Daftar Belanja')
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('color_styles')
                                    ->hiddenLabel()
                                    ->content(new \Illuminate\Support\HtmlString('
                                        <style>
                                            .custom-utama > section { background-color: #ecfdf5 !important; border: 1px solid #a7f3d0 !important; }
                                            .custom-pengiriman > section { background-color: #fff7ed !important; border: 1px solid #fed7aa !important; }
                                            .dark .custom-utama > section { background-color: rgba(6, 78, 59, 0.2) !important; border-color: rgba(6, 78, 59, 0.5) !important; }
                                            .dark .custom-pengiriman > section { background-color: rgba(124, 45, 18, 0.2) !important; border-color: rgba(124, 45, 18, 0.5) !important; }
                                        </style>
                                    ')),
                                \Filament\Forms\Components\Repeater::make('items')
                                    ->relationship()
                                    ->collapsible()
                                    ->defaultItems(1)
                                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('items', $operation))
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Product')
                                            ->required()
                                            ->searchable()
                                            ->allowHtml()
                                            ->native(false)
                                            ->getSearchResultsUsing(function (string $search) {
                                                $products = \App\Models\Product::where('name', 'like', "%{$search}%")
                                                    ->orWhere('id', $search)
                                                    ->limit(15) // Limit diperkecil agar sangat ringan di server
                                                    ->get();

                                                // Kumpulkan ID media untuk bulk Eager-Loading (Mencegah N+1)
                                                $mediaIds = $products->map(fn($p) => is_array($p->images) ? ($p->images[0] ?? null) : null)
                                                    ->filter()
                                                    ->unique()
                                                    ->toArray();

                                                $mediaCache = [];
                                                if (!empty($mediaIds)) {
                                                    $mediaCache = \Awcodes\Curator\Models\Media::whereIn('id', $mediaIds)->get()->keyBy('id');
                                                }

                                                return $products->mapWithKeys(function ($product) use ($mediaCache) {
                                                    $imageId = is_array($product->images) ? ($product->images[0] ?? null) : null;
                                                    $imageUrl = '';
                                                    if ($imageId && isset($mediaCache[$imageId])) {
                                                        $imageUrl = $mediaCache[$imageId]->url;
                                                    }
                                                    $imgTag = $imageUrl ? "<img src='{$imageUrl}' style='width: 32px; height: 32px; object-fit: cover; border-radius: 4px; flex-shrink: 0;' />" : "";
                                                    return [$product->id => "<div style='display: flex; align-items: center; gap: 8px; overflow: hidden; min-width: 0;'>{$imgTag} <span style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;'>{$product->name} (ID: {$product->id})</span></div>"];
                                                });
                                            })
                                            ->getOptionLabelUsing(function ($value) {
                                                $product = \App\Models\Product::find($value);
                                                if (!$product)
                                                    return null;
                                                $imageId = is_array($product->images) ? ($product->images[0] ?? null) : null;
                                                $imageUrl = '';
                                                if ($imageId) {
                                                    $media = \Awcodes\Curator\Models\Media::find($imageId);
                                                    if ($media) {
                                                        $imageUrl = $media->url;
                                                    }
                                                }
                                                $imgTag = $imageUrl ? "<img src='{$imageUrl}' style='width: 32px; height: 32px; object-fit: cover; border-radius: 4px; flex-shrink: 0;' />" : "";
                                                return "<div style='display: flex; align-items: center; gap: 8px; overflow: hidden; min-width: 0;'>{$imgTag} <span style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;'>{$product->name} (ID: {$product->id})</span></div>";
                                            })
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $product = \App\Models\Product::find($state);
                                                if ($product) {
                                                    $set('name', $product->name);
                                                    $set('price', $product->selling_price);
                                                    $set('purchase_price', $product->purchase_price);
                                                    $set('total', $product->selling_price * ($get('quantity') ?: 1));
                                                }
                                                self::updateTotals($get, $set, true);
                                            })
                                            ->columnSpanFull(),
                                        Select::make('product_variant_id')
                                            ->label('Varian')
                                            ->native(false)
                                            ->columnSpanFull()
                                            ->options(function (Get $get) {
                                                $productId = $get('product_id');
                                                if (!$productId)
                                                    return [];
                                                return \App\Models\ProductVariant::where('product_id', $productId)->pluck('name', 'id');
                                            })
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $variant = \App\Models\ProductVariant::find($state);
                                                if ($variant) {
                                                    $product = \App\Models\Product::find($get('product_id'));
                                                    if ($product) {
                                                        $set('name', $product->name . ' - ' . $variant->name);
                                                    } else {
                                                        $set('name', $variant->name);
                                                    }
                                                    $price = $variant->selling_price;
                                                    $set('price', $price);
                                                    $purchasePrice = $variant->purchase_price ?? ($product ? $product->purchase_price : null);
                                                    $set('purchase_price', $purchasePrice);
                                                    $set('total', $price * ($get('quantity') ?: 1));
                                                }
                                                self::updateTotals($get, $set, true);
                                            }),
                                        TextInput::make('name')->required(),
                                        TextInput::make('price')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $set('total', floatval($state) * floatval($get('quantity') ?: 1));
                                                self::updateTotals($get, $set, true);
                                            }),
                                        TextInput::make('purchase_price')
                                            ->label('Harga Modal')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->dehydrated(),
                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $set('total', floatval($state) * floatval($get('price') ?: 0));
                                                self::updateTotals($get, $set, true);
                                            }),
                                        TextInput::make('total')
                                            ->numeric()
                                            ->required()
                                            ->readOnly(),
                                    ])
                                    ->columns(2)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        // Fallback if repeater itself is changed (e.g. item deleted)
                                        $items = $get('items');
                                        $subtotal = 0;
                                        if (is_array($items)) {
                                            foreach ($items as $item) {
                                                $subtotal += floatval($item['total'] ?? 0);
                                            }
                                        }
                                        $set('subtotal', $subtotal);
                                        self::updateTotals($get, $set, false);
                                    })
                            ]),

                        Section::make('Kalkulasi Biaya')
                            ->schema([
                                TextInput::make('subtotal')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('subtotal', $operation))
                                    ->dehydrated()
                                    ->default(0),
                                TextInput::make('shipping_cost')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('shipping_cost', $operation))
                                    ->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($get, $set, false)),
                                TextInput::make('discount_total')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('discount_total', $operation))
                                    ->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($get, $set, false)),
                                TextInput::make('grand_total')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('grand_total', $operation))
                                    ->dehydrated()
                                    ->default(0),
                                TextInput::make('amount_received')
                                    ->label('Uang Diterima (Cash)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->dehydrated(false)
                                    ->visible(fn(Get $get) => $get('source') === 'offline')
                                    ->live(onBlur: true)
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('amount_received', $operation))
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $grandTotal = floatval($get('grand_total') ?? 0);
                                        $received = floatval($state ?? 0);
                                        $set('change_amount', max(0, $received - $grandTotal));
                                    }),
                                TextInput::make('change_amount')
                                    ->label('Kembalian')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('change_amount', $operation))
                                    ->dehydrated(false)
                                    ->visible(fn(Get $get) => $get('source') === 'offline')
                                    ->default(0),
                            ])->columns(2),
                    ])->columnSpan(['lg' => 1]),

                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        Section::make('Informasi Utama')
                            ->extraAttributes(['class' => 'custom-utama'])
                            ->schema([
                                Select::make('user_id')
                                    ->label('Pelanggan (Kosongkan jika Guest)')
                                    ->allowHtml()
                                    ->searchable()
                                    ->native(false)
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('user_id', $operation))
                                    ->getSearchResultsUsing(function (string $search) {
                                        return \App\Models\User::where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($user) {
                                                return [$user->id => "{$user->name} ({$user->email})"];
                                            });
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        $user = \App\Models\User::find($value);
                                        if (!$user)
                                            return null;
                                        return "{$user->name} ({$user->email})";
                                    })
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $user = \App\Models\User::find($state);
                                            if ($user) {
                                                $address = $user->addresses()->where('is_primary', true)->first() ?? $user->addresses()->first();
                                                if ($address) {
                                                    $names = explode(' ', $address->recipient_name, 2);
                                                    $set('shipping_address.first_name', $names[0] ?? '');
                                                    $set('shipping_address.last_name', $names[1] ?? '');
                                                    $set('shipping_address.phone', $address->phone);
                                                    $set('shipping_address.address', $address->full_address);
                                                    $set('shipping_address.province', $address->province);
                                                    $set('shipping_address.city', $address->city);
                                                    $set('shipping_address.district', $address->district);
                                                    $set('shipping_address.postal_code', $address->postal_code);
                                                } else {
                                                    $names = explode(' ', $user->name, 2);
                                                    $set('shipping_address.first_name', $names[0] ?? '');
                                                    $set('shipping_address.last_name', $names[1] ?? '');
                                                    $set('shipping_address.email', $user->email);
                                                }
                                            }
                                        }
                                    }),
                                TextInput::make('order_number')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->default('RBH-' . strtoupper(uniqid())),
                                Select::make('source')
                                    ->label('Sumber Pesanan')
                                    ->options([
                                        'ecommerce' => 'Website / E-Commerce',
                                        'offline' => 'Offline / Toko',
                                        'tiktok' => 'TikTok Shop',
                                    ])
                                    ->default('offline')
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('source', $operation))
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state === 'offline') {
                                            $set('status', 'completed');
                                            $set('payment_status', 'paid');
                                            $set('shipping_address.is_pickup', true);
                                        }
                                    }),
                                Select::make('status')
                                    ->label('Status Pesanan')
                                    ->options(function ($record) {
                                        $options = [
                                            'pending' => 'Menunggu Pembayaran',
                                            'paid' => 'Sudah Dibayar',
                                            'packed' => 'Sedang Dikemas',
                                            'sent' => 'Sedang Dikirim',
                                            'completed' => 'Selesai',
                                        ];

                                        if (($record && $record->status === 'cancelled') || !auth()->user()?->hasRole('kasir')) {
                                             $options['cancelled'] = 'Dibatalkan';
                                        }

                                        return $options;
                                    })
                                    ->required()
                                    ->default('completed')
                                    ->native(false)
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('status', $operation)),
                                \Filament\Forms\Components\Radio::make('payment_status')
                                    ->label('Status Pembayaran')
                                    ->options([
                                        'pending' => 'Belum Lunas',
                                        'paid' => 'Lunas',
                                        'failed' => 'Gagal',
                                        'refunded' => 'Dikembalikan',
                                    ])
                                    ->required()
                                    ->inline()
                                    ->default('paid')
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('payment_status', $operation)),
                                Select::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->native(false)
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('payment_method', $operation))
                                    ->options(function (Get $get) {
                                        $source = $get('source');
                                        $query = \App\Models\PaymentMethod::where('is_active', true);

                                        if ($source === 'offline') {
                                            $query->where(function ($q) {
                                                $q->whereNull('config->availability')
                                                    ->orWhere('config->availability', 'both')
                                                    ->orWhere('config->availability', 'offline');
                                            });
                                        } else {
                                            $query->where(function ($q) {
                                                $q->whereNull('config->availability')
                                                    ->orWhere('config->availability', 'both')
                                                    ->orWhere('config->availability', 'online');
                                            });
                                        }

                                        return $query->pluck('name', 'code');
                                    })
                                    ->searchable()
                                    ->nullable(),
                                Select::make('voucher_id')
                                    ->label('Voucher / Diskon')
                                    ->native(false)
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('voucher_id', $operation))
                                    ->options(function () {
                                        return \App\Models\Voucher::where('is_active', true)->get()->mapWithKeys(function ($v) {
                                            $discountStr = $v->discount_type === 'percent'
                                                ? rtrim(rtrim(number_format($v->discount_amount, 2), '0'), '.') . '%'
                                                : 'Rp ' . number_format($v->discount_amount, 0, ',', '.');
                                            return [$v->id => "[{$v->code}] {$v->name} - Diskon {$discountStr}"];
                                        });
                                    })
                                    ->searchable()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $voucher = \App\Models\Voucher::find($state);
                                            if ($voucher) {
                                                $subtotal = floatval($get('subtotal') ?? 0);
                                                $discount = 0;
                                                if ($voucher->discount_type === 'percent') {
                                                    $discount = $subtotal * ($voucher->discount_amount / 100);
                                                    if ($voucher->max_discount && $discount > $voucher->max_discount) {
                                                        $discount = $voucher->max_discount;
                                                    }
                                                } else {
                                                    $discount = $voucher->discount_amount;
                                                }
                                                $set('discount_total', $discount);
                                            }
                                        } else {
                                            $set('discount_total', 0);
                                        }
                                        self::updateTotals($get, $set, false);
                                    }),
                            ])->columns(1),
                    ])->columnSpan(['lg' => 1]),

                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        Section::make('Informasi Pengiriman')
                            ->extraAttributes(['class' => 'custom-pengiriman'])
                            ->schema([
                                \Filament\Schemas\Components\Fieldset::make('Alamat Pengiriman')
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('shipping_address', $operation))
                                    ->schema([
                                        \Filament\Forms\Components\Toggle::make('shipping_address.is_pickup')
                                            ->label('Ambil di Toko? (Tidak perlu pengiriman)')
                                            ->default(true)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state) {
                                                    $set('courier', null);
                                                    $set('shipping_cost', 0);
                                                    self::updateTotals($get, $set, false);
                                                }
                                            })
                                            ->columnSpanFull(),
                                        TextInput::make('shipping_address.first_name')
                                            ->label('Nama Depan')
                                            ->required(fn(Get $get, string $operation) => $operation === 'create' && $get('shipping_address.is_pickup') !== true)
                                            ->hidden(fn(Get $get) => $get('shipping_address.is_pickup') === true),
                                        TextInput::make('shipping_address.last_name')
                                            ->label('Nama Belakang')
                                            ->hidden(fn(Get $get) => $get('shipping_address.is_pickup') === true),
                                        TextInput::make('shipping_address.phone')
                                            ->label('Telepon')
                                            ->required(fn(Get $get, string $operation) => $operation === 'create' && $get('shipping_address.is_pickup') !== true)
                                            ->hidden(fn(Get $get) => $get('shipping_address.is_pickup') === true),
                                        TextInput::make('shipping_address.email')
                                            ->label('Email')
                                            ->email()
                                            ->hidden(fn(Get $get) => $get('shipping_address.is_pickup') === true),
                                        Textarea::make('shipping_address.address')
                                            ->label('Alamat Lengkap (Jalan, RT/RW, Patokan)')
                                            ->columnSpanFull()
                                            ->required(fn(Get $get, string $operation) => $operation === 'create' && $get('shipping_address.is_pickup') !== true)
                                            ->hidden(fn(Get $get) => $get('shipping_address.is_pickup') === true),
                                        \Filament\Forms\Components\Toggle::make('shipping_address.is_manual')
                                            ->label('Ketik Manual? (Gunakan jika pencarian otomatis error)')
                                            ->live()
                                            ->columnSpanFull()
                                            ->hidden(fn(Get $get) => $get('shipping_address.is_pickup') === true),
                                        Select::make('shipping_address.district')
                                            ->label('Kecamatan / Kota Tujuan (RajaOngkir)')
                                            ->searchable()
                                            ->native(false)
                                            ->placeholder('Ketik nama kecamatan...')
                                            ->columnSpanFull()
                                            ->hidden(fn(Get $get) => $get('shipping_address.is_pickup') === true || $get('shipping_address.is_manual') === true)
                                            ->getSearchResultsUsing(function (string $search): array {
                                                if (strlen($search) < 3)
                                                    return [];
                                                $apiKey = \App\Models\SiteSetting::where('key', 'rajaongkir_api_key')->value('value');
                                                if (!$apiKey)
                                                    return [];

                                                $response = \Illuminate\Support\Facades\Http::withHeaders(['key' => $apiKey])
                                                    ->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                                                        'search' => $search
                                                    ]);

                                                if ($response->successful()) {
                                                    $results = [];
                                                    foreach ($response->json('data') ?? [] as $item) {
                                                        if (isset($item['label']) && isset($item['id'])) {
                                                            $results[$item['id'] . '::' . $item['label']] = $item['label'];
                                                        }
                                                    }
                                                    return $results;
                                                }
                                                return [];
                                            })
                                            ->getOptionLabelUsing(fn($value): ?string => $value)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                // Reset kurir ketika kecamatan berubah, untuk trigger RajaOngkir check
                                                $set('courier', null);
                                                $set('shipping_cost', 0);

                                                // Auto-fill field jika perlu (meskipun tidak ditampilkan, baik untuk data raw)
                                                if ($state) {
                                                    $label = explode('::', $state)[1] ?? $state;
                                                    $parts = explode(', ', $label);
                                                    if (count($parts) >= 4) {
                                                        $set('shipping_address.city', $parts[2]);
                                                        $set('shipping_address.province', $parts[3]);
                                                        $set('shipping_address.postal_code', $parts[4] ?? '');
                                                    }
                                                }
                                            }),
                                        TextInput::make('shipping_address.province')
                                            ->label('Provinsi')
                                            ->visible(fn(Get $get) => $get('shipping_address.is_pickup') !== true && $get('shipping_address.is_manual') === true),
                                        TextInput::make('shipping_address.city')
                                            ->label('Kota/Kabupaten')
                                            ->visible(fn(Get $get) => $get('shipping_address.is_pickup') !== true && $get('shipping_address.is_manual') === true),
                                        TextInput::make('shipping_address.district_manual')
                                            ->label('Kecamatan')
                                            ->visible(fn(Get $get) => $get('shipping_address.is_pickup') !== true && $get('shipping_address.is_manual') === true),
                                        TextInput::make('shipping_address.postal_code')
                                            ->label('Kode Pos')
                                            ->visible(fn(Get $get) => $get('shipping_address.is_pickup') !== true && $get('shipping_address.is_manual') === true),
                                    ])->columns(2),
                                \Filament\Schemas\Components\Fieldset::make('Status Kurir')
                                    ->hidden(fn(Get $get) => $get('shipping_address.is_pickup') === true)
                                    ->schema([
                                        Select::make('courier')
                                            ->label('Kurir (Layanan)')
                                            ->native(false)
                                            ->disabled(fn (string $operation) => self::isFieldDisabled('courier', $operation))
                                            ->options(function (Get $get) {
                                                $districtRaw = $get('shipping_address.district');
                                                $currentCourier = $get('courier');
                                                $options = [];

                                                if (!$districtRaw || strpos($districtRaw, '::') === false) {
                                                    if ($currentCourier) {
                                                        $options[$currentCourier] = $currentCourier;
                                                    }
                                                    return $options;
                                                }

                                                $destinationId = explode('::', $districtRaw)[0];
                                                $apiKey = \App\Models\SiteSetting::where('key', 'rajaongkir_api_key')->value('value');
                                                $originCityRaw = \App\Models\SiteSetting::where('key', 'rajaongkir_origin_city')->value('value');
                                                if (!$apiKey || !$originCityRaw) {
                                                    if ($currentCourier) $options[$currentCourier] = $currentCourier;
                                                    return $options;
                                                }

                                                $originCityId = explode('::', $originCityRaw)[0];

                                                $activeCouriers = \App\Models\ShippingMethod::where('is_active', true)->get();
                                                if ($activeCouriers->isEmpty()) {
                                                    if ($currentCourier) $options[$currentCourier] = $currentCourier;
                                                    return $options;
                                                }

                                                $items = $get('items') ?? [];
                                                $totalWeight = 0;
                                                foreach ($items as $item) {
                                                    $product = \App\Models\Product::find($item['product_id']);
                                                    $weight = ($product && $product->weight > 0) ? $product->weight : 1000;
                                                    $totalWeight += $weight * ($item['quantity'] ?? 1);
                                                }
                                                if ($totalWeight == 0)
                                                    $totalWeight = 1000;

                                                foreach ($activeCouriers as $courier) {
                                                    $response = \Illuminate\Support\Facades\Http::withHeaders(['key' => $apiKey])
                                                        ->asForm()
                                                        ->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                                                            'origin' => $originCityId,
                                                            'destination' => $destinationId,
                                                            'weight' => $totalWeight,
                                                            'courier' => $courier->code
                                                        ]);

                                                    if ($response->successful() && !empty($response->json('data'))) {
                                                        $courierConfig = $courier->config ?? [];
                                                        $allowedServices = [];
                                                        if (!empty($courierConfig['allowed_services'])) {
                                                            if (is_array($courierConfig['allowed_services'])) {
                                                                $allowedServices = array_map('strtoupper', array_map('trim', $courierConfig['allowed_services']));
                                                            } else {
                                                                $allowedServices = array_map('trim', explode(',', strtoupper($courierConfig['allowed_services'])));
                                                            }
                                                        }

                                                        foreach ($response->json('data') as $cost) {
                                                            $rawServiceName = strtoupper($cost['service']);

                                                            if (!empty($allowedServices) && !in_array($rawServiceName, $allowedServices)) {
                                                                continue;
                                                            }

                                                            // Filter using dynamic shipping rules (custom or global)
                                                            if (!$courier->shouldShowService($rawServiceName, $totalWeight, $originCityRaw, $districtRaw)) {
                                                                continue;
                                                            }

                                                            $serviceName = $rawServiceName;
                                                            if (!empty($courierConfig['service_aliases']) && is_array($courierConfig['service_aliases'])) {
                                                                foreach ($courierConfig['service_aliases'] as $rawCode => $alias) {
                                                                    if (strtoupper($rawCode) === $rawServiceName) {
                                                                        $serviceName = $alias;
                                                                        break;
                                                                    }
                                                                }
                                                            }

                                                            $price = $cost['cost'] ?? 0;
                                                            $key = $courier->code . '|' . $rawServiceName . '|' . $price;
                                                            $options[$key] = "{$courier->name} - {$serviceName} (Rp " . number_format($price, 0, ',', '.') . ")";
                                                        }
                                                    }
                                                }
                                                
                                                if ($currentCourier && !isset($options[$currentCourier])) {
                                                    $options[$currentCourier] = $currentCourier;
                                                }
                                                
                                                return $options;
                                            })
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $price = 0;
                                                if ($state && strpos($state, '|') !== false) {
                                                    $parts = explode('|', $state);
                                                    $price = floatval(end($parts));
                                                }
                                                $set('shipping_cost', $price);
                                                self::updateTotals($get, $set, false);
                                            }),
                                        TextInput::make('awb_number')
                                            ->label('Nomor Resi')
                                            ->disabled(fn (string $operation) => self::isFieldDisabled('awb_number', $operation)),
                                    ])->columns(1),
                                Textarea::make('notes')
                                    ->label('Catatan Pesanan')
                                    ->columnSpanFull()
                                    ->disabled(fn (string $operation) => self::isFieldDisabled('notes', $operation)),
                            ])->columns(1),
                    ])->columnSpan(['lg' => 1]),
                ]
            ))->columns(3);
    }

    public static function updateTotals(Get $get, Set $set, bool $isFromRepeater = false): void
    {
        $prefix = $isFromRepeater ? '../../' : '';
        $items = $get($prefix . 'items') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += floatval($item['total'] ?? 0);
        }

        $set($prefix . 'subtotal', $subtotal);
        $shipping = floatval($get($prefix . 'shipping_cost') ?? 0);
        $discount = floatval($get($prefix . 'discount_total') ?? 0);

        $grandTotal = $subtotal + $shipping - $discount;
        $set($prefix . 'grand_total', $grandTotal);

        $received = floatval($get($prefix . 'amount_received') ?? 0);
        if ($received > 0) {
            $set($prefix . 'change_amount', max(0, $received - $grandTotal));
        } else {
            $set($prefix . 'change_amount', 0);
        }
    }

    protected static function isFieldDisabled(string $fieldName, string $operation, ?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        $user = auth()->user();
        if (! $user) {
            return true;
        }

        // Super Admin & Admin can edit everything
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return false;
        }

        // In Create mode, anyone who has Create permission can fill all fields
        if ($operation === 'create') {
            return false;
        }

        // Resolve the record from route if not passed
        if (! $record) {
            $routeRecord = request()->route('record');
            if ($routeRecord) {
                if ($routeRecord instanceof \App\Models\Order) {
                    $record = $routeRecord;
                } elseif (is_numeric($routeRecord) || is_string($routeRecord)) {
                    $record = \App\Models\Order::find($routeRecord);
                }
            } else {
                // Fallback to parsing the HTTP referer for Livewire requests
                $referer = request()->headers->get('referer');
                if ($referer) {
                    $path = parse_url($referer, PHP_URL_PATH);
                    if ($path) {
                        // Path pattern: /admin/e-commerce/orders/{id}/edit
                        $segments = explode('/', trim($path, '/'));
                        $editIndex = array_search('edit', $segments);
                        if ($editIndex !== false && $editIndex > 0) {
                            $id = $segments[$editIndex - 1];
                            if (is_numeric($id)) {
                                $record = \App\Models\Order::find($id);
                            }
                        }
                    }
                }
            }
        }

        // In Edit mode, apply role restrictions
        if ($user->hasRole('kasir')) {
            if ($record instanceof \App\Models\Order) {
                if (self::isRequestChangeActive()) {
                    // If there is an active pending request, lock everything (read-only)
                    $hasPendingRequest = $record->orderRequests()
                        ->where('type', 'change')
                        ->where('status', 'pending')
                        ->exists();
                    if ($hasPendingRequest) {
                        return true;
                    }

                    // If there is an approved request, unlock everything so they can save
                    $hasApprovedRequest = $record->orderRequests()
                        ->where('type', 'change')
                        ->where('status', 'approved')
                        ->exists();
                    if ($hasApprovedRequest) {
                        return false;
                    }

                    // If no pending or approved request, unlock everything so they can prepare their change request proposal
                    return false;
                }

                // Normal Edit mode without request_change query parameter:
                // Cashier can only edit: status, payment_status, awb_number
                return ! in_array($fieldName, ['status', 'payment_status', 'awb_number']);
            }
        }

        if ($user->hasRole('logistics')) {
            // Logistics can edit: status, awb_number
            return ! in_array($fieldName, ['status', 'awb_number']);
        }

        if ($user->hasRole('finance')) {
            // Finance can edit: status, payment_status
            return ! in_array($fieldName, ['status', 'payment_status']);
        }

        // Default: if role not recognized but has edit permission, disable everything
        return true;
    }

    protected static function isRequestChangeActive(): bool
    {
        if (request()->query('request_change') === '1') {
            return true;
        }

        $referer = request()->headers->get('referer');
        if ($referer) {
            $query = parse_url($referer, PHP_URL_QUERY);
            if ($query) {
                parse_str($query, $queryParams);
                return isset($queryParams['request_change']) && $queryParams['request_change'] === '1';
            }
        }

        return false;
    }
}
