<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class TopSellingProductsDailyChart extends ChartWidget
{
    use InteractsWithPageFilters;

    // Ubah judul agar lebih sesuai dengan tren harian
    protected static ?string $heading = 'Tren Penjualan Harian 5 Produk Terlaris (Stok Keluar)';
    protected static ?int $sort = 4;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        // Ambil nilai filter dari dashboard
        $startDate = Carbon::parse($this->filters['startDate']);
        $endDate = Carbon::parse($this->filters['endDate']);

        // Logika untuk endDate realtime (jika endDate yang dipilih adalah hari ini)
        if ($endDate->isToday()) {
            $endDate = now();
        } else {
            $endDate = $endDate->endOfDay();
        }

        // 1. Dapatkan 5 ID Produk Terlaris dalam rentang waktu yang difilter
        // Kueri ini akan mencari produk terlaris berdasarkan total penjualan dalam periode yang dipilih
        $topProductIdsQuery = OrderItem::query()
            ->select('order_items.id_product')
            ->join('orders', 'order_items.id_order', '=', 'orders.id')
            ->where('orders.status', 'shipped')
            ->where('order_items.fulfillment_type', 'warehouse')
            ->whereBetween('order_items.created_at', [$startDate, $endDate]);

        $topProductIds = $topProductIdsQuery
            ->groupBy('order_items.id_product')
            ->orderByDesc(DB::raw('SUM(order_items.quantity)'))
            ->limit(5)
            ->pluck('id_product')
            ->toArray();

        // Jika tidak ada produk terlaris dalam rentang filter, tampilkan chart kosong
        if (empty($topProductIds)) {
            $labels = [];
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $labels[] = $date->translatedFormat('d M');
            }
            if (empty($labels))
                $labels = [Carbon::now()->translatedFormat('d M')]; // Fallback

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Tidak Ada Data Penjualan',
                        'data' => array_fill(0, count($labels), 0),
                        'borderColor' => '#ccc',
                        'fill' => false,
                    ]
                ]
            ];
        }

        // Dapatkan nama-nama produk untuk label legenda
        $topProducts = Product::whereIn('id', $topProductIds)->pluck('name', 'id')->toArray();

        $labels = [];
        $datasets = [];

        $colors = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
        ];

        // --- Perubahan di sini: Looping per hari untuk label sumbu X ---
        $days = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('d M'); // Label: "03 Jun", "04 Jun"
            $days[] = clone $date; // Simpan objek Carbon untuk kueri
        }

        // Fallback jika periode kosong atau tidak ada data
        if (empty($labels)) {
            $labels = [Carbon::now()->translatedFormat('d M')];
            $days = [Carbon::now()];
        }


        // 2. Untuk setiap produk terlaris, kueri data penjualan harian dalam rentang filter
        foreach ($topProductIds as $index => $productId) {
            $productData = [];
            foreach ($days as $day) { // Loop melalui setiap hari
                // Kueri untuk setiap hari dalam rentang filter
                $totalQuantity = OrderItem::query()
                    ->join('orders', 'order_items.id_order', '=', 'orders.id')
                    ->where('orders.status', 'shipped')
                    ->where('order_items.fulfillment_type', 'warehouse')
                    ->where('order_items.id_product', $productId)
                    ->whereDate('order_items.created_at', $day->toDateString()) // <<< Filter per tanggal
                    ->sum('order_items.quantity');

                $productData[] = $totalQuantity;
            }

            $datasets[] = [
                'label' => $topProducts[$productId] ?? 'Produk Tidak Dikenal',
                'data' => $productData,
                'borderColor' => $colors[$index % count($colors)],
                'fill' => false,
                'tension' => 0.4,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    protected function getOptions(): array
    {
        $maxDataValue = 0;
        $data = $this->getData();
        foreach ($data['datasets'] as $dataset) {
            $maxDataValue = max($maxDataValue, ...$dataset['data']);
        }

        // Pastikan Y max minimal 100 (sesuai yang Anda set sebelumnya)
        $yAxisMax = max(20, $maxDataValue);
        if ($maxDataValue > 0) {
            $yAxisMax = max(20, $maxDataValue * 1.1); // Tambah 10% buffer
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
                'x' => [ // Sumbu X adalah Tanggal
                    'title' => [
                        'display' => true,
                        'text' => 'Tanggal' // Ubah judul sumbu X
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
            'maintainAspectRatio' => true, // Penting agar chart bisa menyesuaikan ukuran
        ];
    }
}