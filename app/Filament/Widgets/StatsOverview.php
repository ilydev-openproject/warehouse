<?php

namespace App\Filament\Widgets;

use App\Models\Orders;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Order', Orders::count()),
            Stat::make('Order Hari Ini', Orders::whereDate('created_at', today())->count()),
            Stat::make('Selesai', Orders::where('status', 'completed')->count()),
        ];
    }
}
