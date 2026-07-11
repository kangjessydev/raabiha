<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Varian Produk';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nama Varian')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan('full'),
                \Filament\Forms\Components\Section::make('Kaitan Atribut (Warna & Ukuran)')
                    ->description(function ($record) {
                        if (! $record instanceof \App\Models\ProductVariant || ! $record->exists) {
                            return 'Belum ada atribut terpilih.';
                        }
                        $record->loadMissing('attributeOptions.attribute');
                        if ($record->attributeOptions->isEmpty()) {
                            return 'Belum ada atribut terpilih.';
                        }
                        return 'Terpilih: ' . $record->attributeOptions->map(fn($opt) => "{$opt->attribute->name}: {$opt->value}")->join(' | ');
                    })
                    ->collapsible()
                    ->collapsed()
                    ->columnSpan('full')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('variantAttributes')
                            ->label('')
                            ->helperText('Pilih induk atribut terlebih dahulu (misal: Ukuran), kemudian pilih opsi nilainya (misal: L).')
                            ->schema([
                                \Filament\Forms\Components\Select::make('attribute_id')
                                    ->label('Atribut')
                                    ->options(fn () => \App\Models\Attribute::pluck('name', 'id'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('attribute_option_id', null)),
                                \Filament\Forms\Components\Select::make('attribute_option_id')
                                    ->label('Opsi Atribut')
                                    ->options(function (callable $get) {
                                        $attributeId = $get('attribute_id');
                                        if (!$attributeId) {
                                            return [];
                                        }
                                        return \App\Models\AttributeOption::where('attribute_id', $attributeId)->pluck('value', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        \Filament\Forms\Components\TextInput::make('value')
                                            ->label('Nilai (Misal: Petite, Navy, dll)')
                                            ->required(),
                                        \Filament\Forms\Components\TextInput::make('meta')
                                            ->label('Meta/Kode Hex (Opsional)')
                                            ->placeholder('#000000')
                                            ->helperText('Isi dengan kode hex jika atribut berupa warna.'),
                                    ])
                                    ->createOptionUsing(function (array $data, callable $get) {
                                        $attributeId = $get('attribute_id');
                                        if (!$attributeId) {
                                            throw new \Exception('Silakan pilih induk atribut terlebih dahulu.');
                                        }
                                        $option = \App\Models\AttributeOption::create([
                                            'attribute_id' => $attributeId,
                                            'value' => $data['value'],
                                            'slug' => \Illuminate\Support\Str::slug($data['value']),
                                            'meta' => $data['meta'] ?? null,
                                        ]);
                                        return $option->id;
                                    })
                            ])
                            ->defaultItems(1)
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record instanceof \App\Models\ProductVariant && $record->exists) {
                                    $data = $record->attributeOptions->map(function ($opt) {
                                        return [
                                            'attribute_id' => $opt->attribute_id,
                                            'attribute_option_id' => $opt->id,
                                        ];
                                    })->toArray();
                                    $component->state($data);
                                }
                            })
                            ->saveRelationshipsUsing(function ($record, $state) {
                                if ($record instanceof \App\Models\ProductVariant) {
                                    $optionIds = collect($state)->pluck('attribute_option_id')->filter()->toArray();
                                    $record->attributeOptions()->sync($optionIds);
                                }
                            }),
                    ]),
                \Filament\Forms\Components\Select::make('media_id')
                    ->label('Gambar Varian')
                    ->columnSpan('full')
                    ->options(function (\Filament\Resources\RelationManagers\RelationManager $livewire) {
                        $product = $livewire->getOwnerRecord();
                        if (empty($product->images) || !is_array($product->images)) return [];
                        
                        $mediaItems = \Awcodes\Curator\Models\Media::whereIn('id', $product->images)->get();
                        $options = [];
                        foreach ($mediaItems as $media) {
                            $url = \Illuminate\Support\Facades\Storage::disk($media->disk)->url($media->path);
                            $options[$media->id] = "<div class='flex items-center gap-3'><img src='{$url}' style='width: 32px; height: 32px; border-radius: 4px; object-fit: cover;' /> <span>{$media->name}</span></div>";
                        }
                        return $options;
                    })
                    ->allowHtml()
                    ->searchable()
                    ->preload()
                    ->helperText('Pilihan gambar di atas otomatis diambil dari Galeri Produk (Induk). Pastikan Anda sudah mengupload gambar warna varian ini di Galeri Utama Produk.'),
                \Filament\Forms\Components\TextInput::make('sku')
                    ->label('SKU Lanjutan (Varian)')
                    ->prefix(fn ($livewire) => $livewire->getOwnerRecord()->sku ? $livewire->getOwnerRecord()->sku . '-' : '')
                    ->formatStateUsing(function ($state, $livewire) {
                        $parentSku = $livewire->getOwnerRecord()->sku;
                        if (!$parentSku || blank($state)) return $state;
                        
                        $prefix = $parentSku . '-';
                        if (str_starts_with($state, $prefix)) {
                            return substr($state, strlen($prefix));
                        }
                        return $state;
                    })
                    ->dehydrateStateUsing(function ($state, $livewire) {
                        if (blank($state)) return null;
                        $parentSku = $livewire->getOwnerRecord()->sku;
                        if (!$parentSku) return $state;
                        
                        if (str_starts_with($state, $parentSku . '-')) {
                            $state = substr($state, strlen($parentSku . '-'));
                        } elseif (str_starts_with($state, $parentSku)) {
                            $state = substr($state, strlen($parentSku));
                        }
                        
                        if (str_starts_with($state, '-')) {
                            $state = substr($state, 1);
                        }
                        
                        return $parentSku . '-' . $state;
                    })
                    ->rule(function ($livewire, $record) {
                        return function (string $attribute, $value, \Closure $fail) use ($livewire, $record) {
                            if (blank($value)) return;
                            $parentSku = $livewire->getOwnerRecord()->sku;
                            $fullSku = $parentSku ? ($parentSku . '-' . $value) : $value;
                            
                            $exists = \App\Models\ProductVariant::where('sku', $fullSku)
                                ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                ->exists();
                                
                            if ($exists) {
                                $fail('SKU Varian ini sudah digunakan.');
                            }
                        };
                    })
                    ->maxLength(255),
                TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->required(),
                TextInput::make('minimum_stock')
                    ->label('Stok Minimum Peringatan')
                    ->numeric()
                    ->placeholder('Batas stok minimum varian (Default: 5)'),
                TextInput::make('price')
                    ->label('Harga Jual (Normal)')
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('Mengikuti produk induk jika kosong'),
                TextInput::make('discount_price')
                    ->label('Harga Promo (Diskon)')
                    ->numeric()
                    ->prefix('Rp')
                    ->helperText('Hanya berlaku untuk varian ini. Jika dikosongkan, varian ini tidak menggunakan harga promo.'),
                TextInput::make('purchase_price')
                    ->label('Harga Modal (HPP)')
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('Mengikuti produk induk jika kosong'),
                TextInput::make('reseller_price')
                    ->label('Harga Reseller Khusus')
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('Mengikuti produk induk jika kosong'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->poll('15s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Varian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->description(fn ($record) => $record->getRawOriginal('price') === null ? 'Menginduk' : 'Kustom', position: 'below'),
                Tables\Columns\TextColumn::make('discount_price')
                    ->label('Harga Promo')
                    ->money('IDR')
                    ->placeholder('Tidak Ada'),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Harga Modal')
                    ->money('IDR')
                    ->description(fn ($record) => $record->getRawOriginal('purchase_price') === null ? 'Menginduk' : 'Kustom', position: 'below'),
                Tables\Columns\TextColumn::make('reseller_price')
                    ->label('Harga Reseller')
                    ->money('IDR')
                    ->description(fn ($record) => $record->getRawOriginal('reseller_price') === null ? 'Menginduk' : 'Kustom', position: 'below'),
            ])
            ->filters([
                //
            ])
            ->headerActions([

                \Filament\Actions\Action::make('generate_variants')
                    ->label('Buat Varian Otomatis')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('attributes')
                            ->label('Atribut yang digunakan')
                            ->schema([
                                \Filament\Forms\Components\Select::make('attribute_id')
                                    ->label('Pilih Atribut (Misal: Warna)')
                                    ->options(\App\Models\Attribute::pluck('name', 'id'))
                                    ->live()
                                    ->required(),
                                \Filament\Forms\Components\Select::make('option_ids')
                                    ->label('Pilih Opsi (Misal: Merah, Biru)')
                                    ->multiple()
                                    ->options(fn (\Filament\Schemas\Components\Utilities\Get $get) => \App\Models\AttributeOption::where('attribute_id', $get('attribute_id'))->pluck('value', 'id'))
                                    ->required()
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => filled($get('attribute_id'))),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Atribut')
                    ])
                    ->action(function (array $data, \Filament\Resources\RelationManagers\RelationManager $livewire) {
                        $product = $livewire->getOwnerRecord();
                        
                        $attributes = collect($data['attributes'])->filter(fn($attr) => !empty($attr['option_ids']));
                        if ($attributes->isEmpty()) return;

                        // Create combinations (Cartesian Product)
                        $matrix = [[]];
                        foreach ($attributes as $attribute) {
                            $append = [];
                            foreach ($matrix as $productVariant) {
                                foreach ($attribute['option_ids'] as $optionId) {
                                    $append[] = array_merge($productVariant, [$optionId]);
                                }
                            }
                            $matrix = $append;
                        }

                        // Generate variants
                        foreach ($matrix as $combination) {
                            // Fetch options to generate name/SKU
                            $options = \App\Models\AttributeOption::whereIn('id', $combination)->get();
                            $nameParts = $options->pluck('value')->join(' - ');
                            
                            $variantName = "{$product->name} - {$nameParts}";
                            
                            // Check if exact variant exists (naive check)
                            // Create variant
                            $variant = $product->variants()->create([
                                'name' => $variantName,
                                'stock' => 0,
                                'price' => null,
                                'is_price_override' => false,
                                'is_active' => true,
                            ]);
                            
                            // Attach options
                            $variant->attributeOptions()->attach($combination);
                        }
                    }),
                \Filament\Actions\CreateAction::make()->label('Tambah Manual'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
