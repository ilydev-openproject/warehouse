<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\StockIn;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CashOutChart extends ChartWidget
{
    protected static ?string $heading = 'Pengeluaran';

    use InteractsWithPageFilters;

    protected function getData(): array
    {
        $filters = method_exists($this, 'filters') ? $this->filters : [];

        $startDate = !is_null($filters['startDate'] ?? null)
            ? Carbon::parse($filters['startDate'])
            : now()->subDays(6);

        $endDate = !is_null($filters['endDate'] ?? null)
            ? Carbon::parse($filters['endDate'])
            : now();

        $labels = [];
        $data = [];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('d M');

            $pengeluaran = StockIn::whereDate('created_at', $date)
                ->where('keterangan', 'Kulakan')
                ->sum('total_harga');

            $data[] = $pengeluaran;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran',
                    'data' => $data,
                    'borderColor' => '#f87171',
                    'backgroundColor' => '#f87171',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
