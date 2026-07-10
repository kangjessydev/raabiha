<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Exports\OrderExporter;
use App\Filament\Imports\OrderImporter;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Tabs\Tab;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge($this->getModel()::count()),
            'pending' => Tab::make('Belum Bayar')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending'))
                ->badge($this->getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),
            'processing' => Tab::make('Diproses & Dikirim')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', ['paid', 'packed', 'sent']))
                ->badge($this->getModel()::whereIn('status', ['paid', 'packed', 'sent'])->count())
                ->badgeColor('info'),
            'completed' => Tab::make('Selesai')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'completed'))
                ->badge($this->getModel()::where('status', 'completed')->count())
                ->badgeColor('success'),
            'cancelled' => Tab::make('Dibatalkan')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'cancelled'))
                ->badge($this->getModel()::where('status', 'cancelled')->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Ekspor Pesanan')
                ->exporter(OrderExporter::class)
                ->modifyQueryUsing(fn (Builder $query, array $options) => $query
                    ->when(filled($options['status'] ?? null), fn ($q) => $q->where('status', $options['status']))
                    ->when(filled($options['payment_status'] ?? null), fn ($q) => $q->where('payment_status', $options['payment_status']))
                    ->when(filled($options['date_from'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $options['date_from']))
                    ->when(filled($options['date_until'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $options['date_until']))
                ),

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\OrderResource\Widgets\OrderStatsWidget::class,
        ];
    }
}
