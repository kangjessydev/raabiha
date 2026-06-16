<?php

namespace App\Filament\Resources\RefundRequests;

use App\Filament\Resources\RefundRequests\Pages\ManageRefundRequests;
use App\Models\RefundRequest;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class RefundRequestResource extends Resource
{
    protected static ?string $model = RefundRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;
    protected static \UnitEnum|string|null $navigationGroup = 'Pesanan';
    protected static ?string $navigationLabel = 'Refund Pelanggan';
    protected static ?string $modelLabel = 'Pengajuan Refund';
    protected static ?string $pluralModelLabel = 'Pengajuan Refund';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengajuan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Pelanggan')
                                    ->disabled(),
                                Select::make('order_id')
                                    ->relationship('order', 'order_number')
                                    ->label('Pesanan')
                                    ->disabled(),
                            ]),
                        TextInput::make('reason')
                            ->label('Alasan')
                            ->disabled(),
                        Textarea::make('description')
                            ->label('Penjelasan Detail')
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('refund_amount')
                            ->label('Total Nominal Refund')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                    ]),
                
                Section::make('Informasi Bank Pelanggan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Nama Bank')
                                    ->disabled(),
                                TextInput::make('bank_account_number')
                                    ->label('No. Rekening')
                                    ->disabled(),
                                TextInput::make('bank_account_name')
                                    ->label('Atas Nama')
                                    ->disabled(),
                            ])
                    ]),

                Section::make('Penanganan Admin')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Menunggu (Pending)',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'completed' => 'Selesai Ditransfer',
                            ])
                            ->required()
                            ->default('pending'),
                        Textarea::make('admin_notes')
                            ->label('Catatan Admin (Opsional)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('order.order_number')
                    ->label('No. Pesanan')
                    ->searchable(),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->searchable(),
                TextColumn::make('refund_amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => fn ($state) => in_array($state, ['approved', 'completed']),
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                        default => $state,
                    })
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => ManageRefundRequests::route('/'),
        ];
    }
}
