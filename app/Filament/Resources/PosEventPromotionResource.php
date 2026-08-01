<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosEventPromotionResource\Pages;
use App\Models\PosEventPromotion;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PosEventPromotionResource extends Resource
{
    protected static ?string $model = PosEventPromotion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static \UnitEnum|string|null $navigationGroup = 'Point of Sale (POS)';

    protected static ?string $navigationLabel = 'Promo Event POS';

    protected static ?string $modelLabel = 'Promo Event POS';

    protected static ?string $pluralModelLabel = 'Promo Event POS';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Promo Event')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Promo Event')
                            ->required()
                            ->placeholder('misal: Bazaar Akhir Pekan 2026'),

                        TextInput::make('code')
                            ->label('Kode Promo (Opsional)')
                            ->placeholder('BAZAAR20'),

                        Grid::make(2)
                            ->schema([
                                Select::make('discount_type')
                                    ->label('Tipe Diskon')
                                    ->options([
                                        'percent' => 'Persentase (%)',
                                        'fixed' => 'Nominal Tetap (Rp)',
                                    ])
                                    ->required()
                                    ->default('percent'),

                                TextInput::make('discount_amount')
                                    ->label('Nilai Diskon')
                                    ->numeric()
                                    ->required()
                                    ->default(0),
                            ]),

                        Select::make('applies_to')
                            ->label('Cakupan Diskon (Bermaksud Ke Produk Mana?)')
                            ->options([
                                'all_items' => 'Semua Produk di POS (All Items)',
                                'specific_products' => 'Khusus Produk Terpilih (Inklusi Produk)',
                                'specific_categories' => 'Khusus Kategori Terpilih (Inklusi Kategori)',
                            ])
                            ->required()
                            ->default('all_items')
                            ->reactive(),
                    ]),

                Section::make('Aturan Inklusi & Eksklusi Produk / Kategori')
                    ->description('Tentukan produk/kategori mana yang berhak diskon dan mana yang dilarang diskon (eksklusi).')
                    ->schema([
                        Select::make('included_product_ids')
                            ->label('Daftar Produk yang BERHAK Diskon (Inklusi)')
                            ->multiple()
                            ->options(\App\Models\Product::pluck('name', 'id'))
                            ->visible(fn ($get) => $get('applies_to') === 'specific_products')
                            ->searchable(),

                        Select::make('included_category_ids')
                            ->label('Daftar Kategori yang BERHAK Diskon (Inklusi)')
                            ->multiple()
                            ->options(\App\Models\Category::pluck('name', 'id'))
                            ->visible(fn ($get) => $get('applies_to') === 'specific_categories')
                            ->searchable(),

                        Select::make('excluded_product_ids')
                            ->label('Daftar Produk yang DIKECUALIKAN / DILARANG Diskon (Eksklusi)')
                            ->multiple()
                            ->options(\App\Models\Product::pluck('name', 'id'))
                            ->searchable(),

                        Select::make('excluded_category_ids')
                            ->label('Daftar Kategori yang DIKECUALIKAN / DILARANG Diskon (Eksklusi)')
                            ->multiple()
                            ->options(\App\Models\Category::pluck('name', 'id'))
                            ->searchable(),
                    ]),

                Section::make('Periode & Status Aktif')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('Tanggal & Jam Mulai'),

                                DateTimePicker::make('expires_at')
                                    ->label('Tanggal & Jam Berakhir'),

                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Promo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('discount_amount')
                    ->label('Besar Diskon')
                    ->formatStateUsing(fn ($record) => $record->discount_type === 'percent'
                        ? round($record->discount_amount) . '%'
                        : 'Rp ' . number_format($record->discount_amount, 0, ',', '.'))
                    ->badge()
                    ->color('success'),

                TextColumn::make('applies_to')
                    ->label('Cakupan')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'all_items' => 'Semua Produk',
                        'specific_products' => 'Khusus Produk',
                        'specific_categories' => 'Khusus Kategori',
                        default => $state,
                    })
                    ->badge(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('starts_at')
                    ->label('Tanggal Mulai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),

                TextColumn::make('expires_at')
                    ->label('Tanggal Berakhir')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosEventPromotions::route('/'),
            'create' => Pages\CreatePosEventPromotion::route('/create'),
            'edit' => Pages\EditPosEventPromotion::route('/{record}/edit'),
        ];
    }
}
