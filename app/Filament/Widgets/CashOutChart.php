<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\StockIn; // Pastikan model StockIn di-import dengan benar
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CashOutChart extends ChartWidget
{
    protected static ?string $heading = 'Pengeluaran';

    use InteractsWithPageFilters;

    protected function getData(): array
    {
        // Akses filter langsung dari properti $this->filters
        // Karena filter di dashboard sudah ada defaultnya (hari ini),
        // kita bisa langsung parse tanpa cek null yang kompleks.
        $startDate = Carbon::parse($this->filters['startDate']);
        $endDate = Carbon::parse($this->filters['endDate']);

        $labels = [];
        $data = [];

        // Pastikan rentang waktu mencakup seluruh hari (00:00:00 hingga 23:59:59)
        // atau hingga waktu real-time jika endDate adalah hari ini
        $startDate = $startDate->startOfDay();

        // Logika untuk endDate realtime (jika endDate yang dipilih adalah hari ini)
        if ($endDate->isToday()) {
            $endDate = now(); // Gunakan waktu real-time saat ini
        } else {
            $endDate = $endDate->endOfDay(); // Gunakan akhir hari untuk tanggal di masa lalu
        }

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Label untuk sumbu X (tanggal)
            $labels[] = $date->translatedFormat('d M');

            // Kueri untuk menghitung pengeluaran pada tanggal tersebut
            $pengeluaran = StockIn::whereDate('created_at', $date)
                ->where('keterangan', 'Kulakan') // Filter keterangan 'Kulakan'
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
                    'fill' => false, // Untuk chart garis, agar tidak mengisi area di bawah garis
                    'tension' => 0.4, // Sedikit kelengkungan pada garis
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    // Tambahkan getOptions() untuk kontrol lebih lanjut pada tampilan chart
    protected function getOptions(): array
    {
        // Anda bisa menambahkan logika untuk suggestedMax di sini
        // seperti yang kita lakukan di TopSellingProductsDailyChart
        $maxDataValue = 0;
        $data = $this->getData();
        foreach ($data['datasets'] as $dataset) {
            $maxDataValue = max($maxDataValue, ...$dataset['data']);
        }

        $yAxisMax = max(1000, $maxDataValue); // Misalnya, minimal 1000 atau sesuai data
        if ($maxDataValue > 0) {
            $yAxisMax = max(1000, $maxDataValue * 1.1); // Tambah 10% buffer
        }

        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'suggestedMax' => $yAxisMax, // Terapkan suggestedMax
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Pengeluaran (Rp)'
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