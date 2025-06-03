<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class StockOutChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Tren Stok Keluar Harian';
    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startDate = Carbon::parse($this->filters['startDate']);
        $endDate = Carbon::parse($this->filters['endDate']);

        if ($endDate->isToday()) {
            $endDate = now();
        } else {
            $endDate = $endDate->endOfDay();
        }

        $labels = [];
        $quantities = [];

        // --- KOREKSI DI SINI ---
        // Pastikan periode mencakup seluruh hari dari tanggal awal hingga akhir
        $period = \Carbon\CarbonPeriod::create($startDate->startOfDay(), $endDate->endOfDay());

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('d M');

            $totalQuantity = OrderItem::query()
                ->join('orders', 'order_items.id_order', '=', 'orders.id')
                // ->where('orders.status', 'shipped')
                ->where('order_items.fulfillment_type', 'warehouse')
                ->whereDate('order_items.created_at', $date->toDateString())
                ->sum('order_items.quantity');

            $quantities[] = $totalQuantity;
        }

        // Fallback jika periode kosong atau tidak ada data
        if (empty($labels) && $startDate && $endDate) {
            $labels = [$startDate->translatedFormat('d M'), $endDate->translatedFormat('d M')];
            $quantities = [0, 0];
        } elseif (empty($labels)) { // Fallback jika tidak ada filter default
            $labels = [Carbon::now()->translatedFormat('d M')];
            $quantities = [0];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Unit Keluar',
                    'data' => $quantities,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                    'fill' => false,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        $maxDataValue = 0;
        $data = $this->getData();
        foreach ($data['datasets'] as $dataset) {
            $maxDataValue = max($maxDataValue, ...$dataset['data']);
        }

        $yAxisMax = max(100, $maxDataValue);
        if ($maxDataValue > 0) {
            $yAxisMax = max(100, $maxDataValue * 1.1);
        }

        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'suggestedMax' => $yAxisMax,
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Unit Keluar'
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Tanggal'
                    ]
                ]
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ]
            ],
            'responsive' => true,
            'maintainAspectRatio' => true,
        ];
    }
}