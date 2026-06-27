<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('order_number')
                    ->label('ID Pesanan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->state(function ($record) {
                        return $record->user_id ? $record->user->name : ($record->shipping_address['name'] ?? 'Guest User');
                    })
                    ->description(function ($record) {
                        $isGuest = !$record->user_id;
                        $label = $isGuest ? 'Guest' : 'Member';
                        $classes = $isGuest
                            ? 'bg-warning-100 text-warning-700 ring-warning-400/30'
                            : 'bg-success-100 text-success-700 ring-success-400/30';
                        return new \Illuminate\Support\HtmlString(
                            "<span class='inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {$classes}'>{$label}</span>"
                        );
                    })
                    ->searchable(['user.name'])
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'info',
                        'packed' => 'primary',
                        'sent' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('grand_total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_member')
                    ->label('Tipe Pelanggan')
                    ->placeholder('Semua Pelanggan')
                    ->trueLabel('Hanya Member')
                    ->falseLabel('Hanya Guest')
                    ->native(false)
                    ->queries(
                        true: fn($query) => $query->whereNotNull('user_id'),
                        false: fn($query) => $query->whereNull('user_id'),
                        blank: fn($query) => $query,
                    ),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->native(false)
                    ->label('Status Pesanan')
                    ->options([
                        'pending' => 'Pending (Menunggu Pembayaran)',
                        'paid' => 'Dibayar',
                        'packed' => 'Dikemas',
                        'sent' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('payment_status')
                    ->native(false)
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Lunas (PAID)',
                        'failed' => 'Gagal',
                        'refunded' => 'Dikembalikan (Refunded)',
                    ]),
                \Filament\Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->columns(2)
                    ->columnSpan('full')
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = \Filament\Tables\Filters\Indicator::make('Pesanan mulai ' . \Carbon\Carbon::parse($data['created_from'])->translatedFormat('d M Y'))
                                ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = \Filament\Tables\Filters\Indicator::make('Pesanan sampai ' . \Carbon\Carbon::parse($data['created_until'])->translatedFormat('d M Y'))
                                ->removeField('created_until');
                        }
                        return $indicators;
                    }),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::Dropdown)
            ->filtersFormWidth(\Filament\Support\Enums\Width::FourExtraLarge)
            ->filtersFormColumns(3)
            ->recordActions([
                ViewAction::make()
                    ->slideOver()
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('Lihat Detail'),
                EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->tooltip('Ubah'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->tooltip('Hapus')
                    ->visible(fn() => !auth()->user()?->hasRole('kasir')),
                \Filament\Actions\Action::make('review_request')
                    ->label('Tinjau Pengajuan')
                    ->icon('heroicon-o-shield-check')
                    ->color('info')
                    ->iconButton()
                    ->tooltip('Tinjau Pengajuan Kasir')
                    ->url(fn($record) => \App\Filament\Resources\OrderRequestResource::getUrl('index') . '?tableSearch=' . urlencode($record->order_number))
                    ->visible(
                        fn($record) =>
                        auth()->user()?->hasRole(['super_admin', 'admin', 'owner']) &&
                        $record->orderRequests()->where('status', 'pending')->exists()
                    ),
                \Filament\Actions\Action::make('request_change')
                    ->label('Ajukan Perubahan')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->iconButton()
                    ->tooltip('Ajukan Perubahan')
                    ->url(fn($record) => \App\Filament\Resources\Orders\OrderResource::getUrl('edit', ['record' => $record]) . '?request_change=1')
                    ->visible(
                        fn($record) =>
                        auth()->user()?->hasRole('kasir') &&
                        !in_array($record->status, ['completed', 'cancelled']) &&
                        !$record->orderRequests()->where('status', 'pending')->where('type', 'change')->exists()
                    ),
                \Filament\Actions\Action::make('request_cancel')
                    ->label('Ajukan Pembatalan')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->iconButton()
                    ->tooltip('Ajukan Pembatalan')
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Alasan Pembatalan/Penghapusan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->orderRequests()->create([
                            'user_id' => auth()->id(),
                            'type' => 'cancel',
                            'reason' => $data['reason'],
                            'status' => 'pending',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Pengajuan pembatalan pesanan berhasil dikirim')
                            ->success()
                            ->send();
                    })
                    ->visible(
                        fn($record) =>
                        auth()->user()?->hasRole('kasir') &&
                        $record->status !== 'cancelled' &&
                        !$record->orderRequests()->where('status', 'pending')->where('type', 'cancel')->exists()
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
