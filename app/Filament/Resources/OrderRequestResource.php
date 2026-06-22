<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\ECommerce\ECommerceCluster;
use App\Filament\Resources\OrderRequestResource\Pages;
use App\Models\OrderRequest;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;

class OrderRequestResource extends Resource
{
    protected static ?string $cluster = ECommerceCluster::class;
    protected static ?int $navigationSort = 3;
    protected static \UnitEnum|string|null $navigationGroup = \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::Transaksi;

    protected static ?string $model = OrderRequest::class;
    protected static ?string $modelLabel = 'Permintaan Kasir';
    protected static ?string $pluralModelLabel = 'Permintaan Kasir';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('owner'));
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('owner'));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('ID Pesanan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe Pengajuan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'change' => 'warning',
                        'cancel' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'change' => 'Perubahan',
                        'cancel' => 'Pembatalan',
                        default => $state,
                    }),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'completed' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                        default => $state,
                    }),
                TextColumn::make('actionedBy.name')
                    ->label('Ditinjau Oleh')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('cashier_reason')
                            ->label('Alasan Kasir')
                            ->content(fn ($record) => $record?->reason ?? '-'),
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Catatan (Opsional)')
                            ->rows(3),
                    ])
                    ->action(function (OrderRequest $record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'actioned_by' => auth()->id(),
                            'notes' => $data['notes'] ?? null,
                        ]);

                        // If it's a cancellation request, cancel the order automatically
                        if ($record->type === 'cancel') {
                            $order = $record->order;
                            $order->update(['status' => 'cancelled']);
                            $record->update(['status' => 'completed']);

                            // Auto-create RefundRequest if the order was paid (Lunas)
                            if ($order->payment_status === 'paid') {
                                \App\Models\RefundRequest::create([
                                    'user_id' => $order->user_id ?? auth()->id() ?? 1,
                                    'order_id' => $order->id,
                                    'refund_amount' => $order->grand_total,
                                    'reason' => $record->reason ?? 'Pembatalan pesanan oleh kasir',
                                    'description' => "Pengajuan pembatalan disetujui oleh Owner. Catatan: " . ($record->notes ?? '-'),
                                    'status' => 'pending',
                                ]);
                            }
                        }

                        // If it's a change request, apply the payload directly to the order and order items
                        if ($record->type === 'change' && is_array($record->payload)) {
                            $order = $record->order;

                            // Separate attributes from relations
                            $modelAttributes = array_diff_key($record->payload, array_flip(['items']));
                            $order->fill($modelAttributes);
                            $order->save();

                            // Sync order items
                            if (isset($record->payload['items']) && is_array($record->payload['items'])) {
                                $order->items()->delete();

                                foreach ($record->payload['items'] as $item) {
                                    $order->items()->create([
                                        'product_id' => $item['product_id'] ?? null,
                                        'product_variant_id' => $item['product_variant_id'] ?? null,
                                        'name' => $item['name'] ?? '',
                                        'price' => $item['price'] ?? 0,
                                        'quantity' => $item['quantity'] ?? 1,
                                        'total' => $item['total'] ?? 0,
                                        'purchase_price' => $item['purchase_price'] ?? 0,
                                    ]);
                                }
                            }

                            $record->update(['status' => 'completed']);
                        }

                        // Send database notification to the cashier who submitted the request
                        if ($record->user) {
                            $typeLabel = $record->type === 'change' ? 'Perubahan' : 'Pembatalan';
                            $orderNumber = $record->order->order_number ?? 'Pesanan';
                            
                            \Filament\Notifications\Notification::make()
                                ->icon('heroicon-o-check-circle')
                                ->iconColor('success')
                                ->title("Pengajuan {$typeLabel} Disetujui")
                                ->body("Pengajuan {$typeLabel} untuk pesanan #{$orderNumber} telah disetujui oleh Owner." . ($record->notes ? " Catatan: {$record->notes}" : ""))
                                ->actions([
                                    \Filament\Actions\Action::make('view')
                                        ->label('Lihat Pesanan')
                                        ->button()
                                        ->url(route('filament.admin.e-commerce.resources.orders.index') . '?tableSearch=' . urlencode($orderNumber)),
                                ])
                                ->sendToDatabase($record->user);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Pengajuan berhasil disetujui')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (OrderRequest $record) => $record->status === 'pending'),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('cashier_reason')
                            ->label('Alasan Kasir')
                            ->content(fn ($record) => $record?->reason ?? '-'),
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Catatan/Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (OrderRequest $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'actioned_by' => auth()->id(),
                            'notes' => $data['notes'] ?? null,
                        ]);

                        // Send database notification to the cashier who submitted the request
                        if ($record->user) {
                            $typeLabel = $record->type === 'change' ? 'Perubahan' : 'Pembatalan';
                            $orderNumber = $record->order->order_number ?? 'Pesanan';
                            
                            \Filament\Notifications\Notification::make()
                                ->icon('heroicon-o-x-circle')
                                ->iconColor('danger')
                                ->title("Pengajuan {$typeLabel} Ditolak")
                                ->body("Pengajuan {$typeLabel} untuk pesanan #{$orderNumber} ditolak oleh Owner." . ($record->notes ? " Alasan: {$record->notes}" : ""))
                                ->actions([
                                    \Filament\Actions\Action::make('view')
                                        ->label('Lihat Pesanan')
                                        ->button()
                                        ->url(route('filament.admin.e-commerce.resources.orders.index') . '?tableSearch=' . urlencode($orderNumber)),
                                ])
                                ->sendToDatabase($record->user);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Pengajuan berhasil ditolak')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (OrderRequest $record) => $record->status === 'pending'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOrderRequests::route('/'),
        ];
    }
}
