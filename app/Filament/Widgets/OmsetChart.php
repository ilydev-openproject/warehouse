<?php

namespace App\Filament\Widgets;

use App\Models\Orders;
use Carbon\Carbon;
use App\Models\Order; // Asumsikan model Anda adalah App\Models\Order
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OmsetChart extends ChartWidget
{
    protected static ?string $heading = 'Omset Kotor';

    use InteractsWithPageFilters;

    protected function getData(): array
    {
        // Akses filter langsung dari properti $filters
        // Menggunakan null coalescing operator ?? untuk nilai default
        $startDate = Carbon::parse($this->filters['startDate'] ?? now()->subDays(6));
        $endDate = Carbon::parse($this->filters['endDate'] ?? now());

        $labels = [];
        $data = [];

        // Pastikan startDate dan endDate mencakup seluruh hari
        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('d M');

            // Gunakan Model yang benar (misal: Order::class)
            $omset = Orders::whereDate('created_at', $date)
                // ->where('status', 'shipped') // Omset biasanya dihitung dari order yang shipped, bukan process
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

    // Tambahkan juga getOptions() untuk kontrol lebih lanjut jika diperlukan
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Omset'
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