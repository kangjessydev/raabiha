<?php

namespace App\Filament\Resources\Cashflows\Pages;

use App\Filament\Resources\Cashflows\CashflowResource;
use App\Filament\Resources\Cashflows\Widgets\CashflowStatsWidget;
use App\Models\Cashflow;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;

class ListCashflows extends ListRecords
{
    protected static string $resource = CashflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol "Tarik Data Transaksi"
            Action::make('sync_orders')
                ->label('🔄 Tarik Data Transaksi')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Tarik Data dari Pesanan Lunas')
                ->modalDescription('Sistem akan menarik semua pesanan yang sudah lunas dan belum tercatat di Buku Kas. Proses ini aman dan tidak akan membuat duplikasi data.')
                ->modalSubmitActionLabel('Ya, Tarik Sekarang')
                ->action(function () {
                    $count = 0;

                    // Chunk 100 per batch — hemat memory di VPS 2GB
                    Order::where('payment_status', 'paid')
                        ->chunk(100, function ($orders) use (&$count) {
                            foreach ($orders as $order) {
                                $created = Cashflow::updateOrCreate(
                                    [
                                        'order_id' => $order->id,
                                        'type'     => 'in',
                                        'source'   => 'order',
                                    ],
                                    [
                                        'transaction_date' => $order->updated_at->toDateString(),
                                        'category'         => 'Sales',
                                        'amount'           => $order->grand_total,
                                        'description'      => 'Penjualan pesanan #' . $order->order_number,
                                        'is_reversed'      => false,
                                    ]
                                );

                                if ($created->wasRecentlyCreated) {
                                    $count++;
                                }
                            }
                        });

                    // Bersihkan cache widget agar langsung update
                    Cache::forget('cashflow_stats_' . now()->format('Y-m'));

                    Notification::make()
                        ->title('Sinkronisasi Selesai')
                        ->body("{$count} data transaksi baru berhasil ditarik.")
                        ->success()
                        ->send();
                }),

            CreateAction::make()->label('+ Catat Pengeluaran'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CashflowStatsWidget::class,
        ];
    }
}
