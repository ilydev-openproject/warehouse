<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Orders;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\OrderResource\Pages\ListOrders;

class OrderStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        return [
            Stat::make('Total Pesanan', $query->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Nilai Kotor Total', 'Rp ' . number_format($query->where('status', 'shipped')->sum('gross_amount'), 0, ',', '.'))
                ->color('primary'),

            Stat::make('Pesanan Dikirim', $query->where('status', 'shipped')->count())
                ->color('success')
                ->icon('heroicon-o-truck'),
        ];
    }

    protected function getOrderTrendData(): array
    {
        $data = $this->getPageTableQuery()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');

        return [
            'labels' => $data->keys()->toArray(),
            'data' => $data->values()->toArray(),
        ];
    }
}
