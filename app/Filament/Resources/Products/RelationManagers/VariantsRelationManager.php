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
                    ->maxLength(255),
                \Filament\Forms\Components\Select::make('attributeOptions')
                    ->relationship('attributeOptions', 'value', fn ($query) => $query->with('attribute'))
                    ->getOptionLabelFromRecordUsing(fn (\App\Models\AttributeOption $record) => "{$record->attribute->name}: {$record->value}")
                    ->multiple()
                    ->preload()
                    ->label('Kaitan Opsi Atribut (Warna/Ukuran)')
                    ->helperText('Pilih opsi atribut yang sesuai agar varian muncul di frontend.')
                    ->createOptionForm([
                        \Filament\Forms\Components\Select::make('attribute_id')
                            ->label('Induk Atribut')
                            ->options(fn () => \App\Models\Attribute::pluck('name', 'id'))
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('value')
                            ->label('Nilai (Misal: Petite, Navy, dll)')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('meta')
                            ->label('Meta/Kode Hex (Opsional)')
                            ->placeholder('#000000')
                            ->helperText('Isi dengan kode hex jika atribut berupa warna.'),
                    ])
                    ->createOptionUsing(function (array $data) {
                        $option = \App\Models\AttributeOption::create([
                            'attribute_id' => $data['attribute_id'],
                            'value' => $data['value'],
                            'slug' => \Illuminate\Support\Str::slug($data['value']),
                            'meta' => $data['meta'] ?? null,
                        ]);
                        return $option->id;
                    }),
                \Awcodes\Curator\Components\Forms\CuratorPicker::make('media_id')
                    ->label('Gambar Varian')
                    ->relationship('media', 'id')
                    ->buttonLabel('Pilih/Upload Gambar Varian')
                    ->helperText('Jika warna ini sudah ada gambarnya di varian ukuran lain, pilih gambar yang sama agar tidak duplikat.')
                    ->color('primary'),
                TextInput::make('sku')
                    ->label('SKU')
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
