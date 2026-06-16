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
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class RefundRequestResource extends Resource
{
    protected static ?string $model = RefundRequest::class;
    protected static ?string $cluster = \App\Filament\Clusters\ECommerce\ECommerceCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;
    protected static \UnitEnum|string|null $navigationGroup = \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::Transaksi;
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
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pelanggan')
                            ->disabled(),
                        Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->label('Pesanan')
                            ->disabled(),
                        TextInput::make('reason')
                            ->label('Alasan')
                            ->disabled(),
                        TextInput::make('refund_amount')
                            ->label('Total Nominal Refund')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Textarea::make('description')
                            ->label('Penjelasan Detail')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Bank Pelanggan')
                    ->columns(3)
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
                        'success' => fn($state) => in_array($state, ['approved', 'completed']),
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
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
                \Filament\Actions\Action::make('notify_wa')
                    ->label('Kirim Notif WA')
                    ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                    ->color('success')
                    ->url(function ($record) {
                        $phone = $record->order->shipping_address['phone'] ?? null;
                        if (!$phone && $record->user) {
                            $phone = $record->user->phone ?? null;
                        }
                        if (!$phone)
                            return '#';

                        if (str_starts_with($phone, '0')) {
                            $phone = '62' . substr($phone, 1);
                        }

                        $customerName = $record->user ? $record->user->name : 'Pelanggan';
                        $orderNo = $record->order ? $record->order->order_number : '-';
                        $amount = number_format($record->refund_amount, 0, ',', '.');

                        $status = $record->status;
                        if ($status === 'approved') {
                            $msg = "Halo {$customerName}, pengajuan refund untuk pesanan #{$orderNo} senilai Rp{$amount} telah *DISETUJUI*. Tim Finance kami akan segera memproses transfer ke rekening Anda.";
                        } elseif ($status === 'rejected') {
                            $msg = "Halo {$customerName}, mohon maaf pengajuan refund untuk pesanan #{$orderNo} senilai Rp{$amount} *DITOLAK*.\n\nCatatan: " . ($record->admin_notes ?? 'Tidak memenuhi syarat pengembalian.');
                        } elseif ($status === 'completed') {
                            $msg = "Halo {$customerName}, dana refund untuk pesanan #{$orderNo} senilai Rp{$amount} telah *SELESAI DITRANSFER* ke rekening {$record->bank_name} Anda. Silakan cek mutasi rekening Anda.";
                        } else {
                            $msg = "Halo {$customerName}, kami sedang meninjau pengajuan refund untuk pesanan #{$orderNo}.";
                        }

                        return "https://wa.me/{$phone}?text=" . urlencode($msg);
                    })
                    ->openUrlInNewTab(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
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
