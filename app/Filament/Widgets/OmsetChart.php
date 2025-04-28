<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Orders;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OmsetChart extends ChartWidget
{
    protected static ?string $heading = 'Omset Kotor';

    use InteractsWithPageFilters;

    protected function getData(): array
    {
        $filters = $this->filters;

        $startDate = Carbon::parse($this->filters['startDate'] ?? now()->subDays(6));
        $endDate = Carbon::parse($this->filters['endDate'] ?? now());


        $labels = [];
        $data = [];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('d M');

            $omset = Orders::whereDate('created_at', $date)
                ->where('status', 'shipped')
                ->sum('gross_amount');

            $data[] = $omset;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Omset',
                    'data' => $data,
                    'borderColor' => '#ca3ba3',
                    'backgroundColor' => '#ca3ba3',
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
