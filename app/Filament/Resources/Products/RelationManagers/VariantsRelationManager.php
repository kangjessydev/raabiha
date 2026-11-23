<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\ProductVariant;
use App\Services\ProductImageResolver;
use Awcodes\Curator\Models\Media;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

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
                Select::make('attributeOptions')
                    ->relationship('attributeOptions', 'value')
                    ->multiple()
                    ->searchable()
                    ->required()
                    ->label('Kaitan Opsi Atribut (Warna/Ukuran)')
                    ->helperText('Pilih opsi atribut yang sesuai agar varian muncul di frontend.')
                    ->getSearchResultsUsing(function (string $search) {
                        $query = AttributeOption::query()
                            ->join('attributes', 'attribute_options.attribute_id', '=', 'attributes.id')
                            ->select('attribute_options.*', 'attributes.name as attribute_name');

                        if (! empty($search)) {
                            $keywords = array_filter(explode(' ', $search));
                            foreach ($keywords as $keyword) {
                                $query->where(function ($q) use ($keyword) {
                                    $q->where('attribute_options.value', 'like', "%{$keyword}%")
                                        ->orWhere('attributes.name', 'like', "%{$keyword}%");
                                });
                            }

                            // Sort: exact value match first, then prefix match, then others
                            $query->orderByRaw('CASE 
                                WHEN attribute_options.value = ? THEN 1
                                WHEN attribute_options.value LIKE ? THEN 2
                                ELSE 3
                            END ASC', [$search, "{$search}%"]);
                        }

                        $query->orderBy('attributes.name', 'asc')
                            ->orderBy('attribute_options.value', 'asc');

                        $results = $query->limit(150)->get();

                        $formatted = [];
                        foreach ($results as $opt) {
                            $formatted[$opt->id] = "{$opt->attribute_name}: {$opt->value}";
                        }

                        return $formatted;
                    })
                    ->getOptionLabelsUsing(function (array $values) {
                        return AttributeOption::query()
                            ->with('attribute')
                            ->whereIn('id', $values)
                            ->get()
                            ->mapWithKeys(fn ($opt) => [$opt->id => "{$opt->attribute->name}: {$opt->value}"])
                            ->toArray();
                    })
                    ->createOptionForm([
                        Select::make('attribute_id')
                            ->label('Induk Atribut')
                            ->options(fn () => Attribute::pluck('name', 'id'))
                            ->required(),
                        TextInput::make('value')
                            ->label('Nilai (Misal: Petite, Navy, dll)')
                            ->required()
                            ->unique(
                                table: 'attribute_options',
                                modifyRuleUsing: function (Unique $rule, Get $get) {
                                    return $rule->where('attribute_id', $get('attribute_id'));
                                }
                            ),
                        TextInput::make('meta')
                            ->label('Meta/Kode Hex (Opsional)')
                            ->placeholder('#000000')
                            ->helperText('Isi dengan kode hex jika atribut berupa warna.'),
                    ])
                    ->createOptionUsing(function (array $data) {
                        $option = AttributeOption::create([
                            'attribute_id' => $data['attribute_id'],
                            'value' => $data['value'],
                            'slug' => Str::slug($data['value']),
                            'meta' => $data['meta'] ?? null,
                        ]);

                        return $option->id;
                    }),
                Select::make('media_id')
                    ->label('Gambar Varian')
                    ->options(function (RelationManager $livewire) {
                        $product = $livewire->getOwnerRecord();
                        if (empty($product->images) || ! is_array($product->images)) {
                            return [];
                        }

                        $numericIds = [];
                        $searchPaths = [];

                        foreach ($product->images as $img) {
                            if (is_numeric($img)) {
                                $numericIds[] = (int) $img;
                            } elseif (is_string($img)) {
                                $pathOnly = parse_url($img, PHP_URL_PATH) ?? $img;
                                $clean = ltrim($pathOnly, '/');
                                $searchPaths[] = $clean;
                                $searchPaths[] = str_replace('storage/', '', $clean);
                                $searchPaths[] = basename($pathOnly);
                            }
                        }

                        $searchPaths = array_values(array_unique(array_filter($searchPaths)));

                        $mediaItems = Media::query()
                            ->when(! empty($numericIds), fn ($q) => $q->whereIn('id', $numericIds))
                            ->when(! empty($searchPaths), function ($q) use ($searchPaths, $numericIds) {
                                if (! empty($numericIds)) {
                                    $q->orWhereIn('path', $searchPaths)->orWhereIn('name', $searchPaths);
                                } else {
                                    $q->whereIn('path', $searchPaths)->orWhereIn('name', $searchPaths);
                                }
                            })
                            ->get();

                        $options = [];
                        foreach ($mediaItems as $media) {
                            $disk = $media->disk ?? 'public';
                            $url = Storage::disk($disk)->url($media->path);
                            $name = $media->pretty_name ?? $media->name ?? 'Gambar Varian';
                            $options[$media->id] = "<div class='flex items-center gap-3'><img src='{$url}' style='width: 32px; height: 32px; border-radius: 4px; object-fit: cover;' /> <span>{$name}</span></div>";
                        }

                        return $options;
                    })
                    ->allowHtml()
                    ->searchable()
                    ->preload()
                    ->helperText('Pilihan gambar di atas otomatis diambil dari Galeri Produk (Induk). Pastikan Anda sudah mengupload gambar warna varian ini di Galeri Utama Produk.'),
                TextInput::make('sku')
                    ->label('SKU Lanjutan (Varian)')
                    ->prefix(fn ($livewire) => $livewire->getOwnerRecord()->sku ? $livewire->getOwnerRecord()->sku.'-' : '')
                    ->formatStateUsing(function ($state, $livewire) {
                        $parentSku = $livewire->getOwnerRecord()->sku;
                        if (! $parentSku || blank($state)) {
                            return $state;
                        }

                        $prefix = $parentSku.'-';
                        if (str_starts_with($state, $prefix)) {
                            return substr($state, strlen($prefix));
                        }

                        return $state;
                    })
                    ->dehydrateStateUsing(function ($state, $livewire) {
                        if (blank($state)) {
                            return null;
                        }
                        $parentSku = $livewire->getOwnerRecord()->sku;
                        if (! $parentSku) {
                            return $state;
                        }

                        if (str_starts_with($state, $parentSku.'-')) {
                            $state = substr($state, strlen($parentSku.'-'));
                        } elseif (str_starts_with($state, $parentSku)) {
                            $state = substr($state, strlen($parentSku));
                        }

                        if (str_starts_with($state, '-')) {
                            $state = substr($state, 1);
                        }

                        return $parentSku.'-'.$state;
                    })
                    ->rule(function ($livewire, $record) {
                        return function (string $attribute, $value, \Closure $fail) use ($livewire, $record) {
                            if (blank($value)) {
                                return;
                            }
                            $parentSku = $livewire->getOwnerRecord()->sku;
                            $fullSku = $parentSku ? ($parentSku.'-'.$value) : $value;

                            $exists = ProductVariant::where('sku', $fullSku)
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
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
                Toggle::make('is_active')
                    ->label('Aktif di Katalog')
                    ->default(true)
                    ->helperText('Nonaktifkan jika varian ini tidak ingin tampil atau dibeli di katalog.'),
            ]);
    }

    public function table(Table $table): Table
    {
        $ownerRecord = $this->getOwnerRecord();
        $preloadedOwnerMedia = ProductImageResolver::preloadMedia([$ownerRecord]);

        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn ($query) => $query->with('media'))
            ->poll('15s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Foto')
                    ->getStateUsing(fn (ProductVariant $record): string => ProductImageResolver::resolveVariantUrl($record, $ownerRecord, $preloadedOwnerMedia))
                    ->circular()
                    ->imageSize(36),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Varian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->headerActions([

                Action::make('generate_variants')
                    ->label('Buat Varian Otomatis')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->schema([
                        Repeater::make('attributes')
                            ->label('Atribut yang digunakan')
                            ->schema([
                                Select::make('attribute_id')
                                    ->label('Pilih Atribut (Misal: Warna)')
                                    ->options(Attribute::pluck('name', 'id'))
                                    ->live()
                                    ->required(),
                                Select::make('option_ids')
                                    ->label('Pilih Opsi (Misal: Merah, Biru)')
                                    ->multiple()
                                    ->options(fn (Get $get) => AttributeOption::where('attribute_id', $get('attribute_id'))->pluck('value', 'id'))
                                    ->required()
                                    ->visible(fn (Get $get) => filled($get('attribute_id'))),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Atribut'),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $product = $livewire->getOwnerRecord();

                        $attributes = collect($data['attributes'])->filter(fn ($attr) => ! empty($attr['option_ids']));
                        if ($attributes->isEmpty()) {
                            return;
                        }

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

                        // Build canonical signatures of existing variant attribute combinations (prevent duplicates)
                        $existingSignatures = [];
                        $existingVariants = $product->variants()->with('attributeOptions:id')->get();
                        foreach ($existingVariants as $existingVariant) {
                            $ids = $existingVariant->attributeOptions->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
                            if (! empty($ids)) {
                                $existingSignatures[implode('-', $ids)] = true;
                            }
                        }

                        // Preload all referenced attribute options in a single query
                        $allOptionIds = collect($matrix)->flatten()->map(fn ($id) => (int) $id)->unique()->values()->all();
                        $optionsById = AttributeOption::whereIn('id', $allOptionIds)->get()->keyBy('id');

                        $createdCount = 0;
                        $skippedCount = 0;

                        // Generate variants
                        foreach ($matrix as $combination) {
                            $sortedIds = collect($combination)->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
                            $signature = implode('-', $sortedIds);

                            if (isset($existingSignatures[$signature])) {
                                $skippedCount++;

                                continue;
                            }

                            $nameParts = collect($combination)
                                ->map(fn ($id) => (int) $id)
                                ->unique()
                                ->map(fn ($id) => $optionsById->get($id)?->value)
                                ->filter()
                                ->join(' - ');

                            $variantName = "{$product->name} - {$nameParts}";

                            $variant = $product->variants()->create([
                                'name' => $variantName,
                                'stock' => 0,
                                'price' => null,
                                'is_price_override' => false,
                                'is_active' => true,
                            ]);

                            // Attach options
                            $variant->attributeOptions()->attach($sortedIds);

                            $existingSignatures[$signature] = true;
                            $createdCount++;
                        }

                        if ($createdCount > 0) {
                            $body = "{$createdCount} varian baru berhasil dibuat.";
                            if ($skippedCount > 0) {
                                $body .= " {$skippedCount} kombinasi duplikat dilewati.";
                            }

                            Notification::make()
                                ->title('Generate Varian Selesai')
                                ->body($body)
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Tidak Ada Varian Baru Dibuat')
                                ->body("Semua {$skippedCount} kombinasi yang dipilih sudah ada sebelumnya.")
                                ->warning()
                                ->send();
                        }
                    }),
                CreateAction::make()->label('Tambah Manual'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
