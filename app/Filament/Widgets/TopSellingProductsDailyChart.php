<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product; // Pastikan model Product di-import
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopSellingProductsDailyChart extends ChartWidget
{
    protected static ?string $heading = '5 Produk Terlaris Hari Ini (Berdasarkan Stok Keluar)';
    protected static ?int $sort = 4; // Pastikan sort unik, misalnya 4

    protected function getType(): string
    {
        return 'line'; // Kita ingin grafik garis
    }

    protected function getData(): array
    {
        $todayStart = Carbon::now()->startOfDay();
        $todayEnd = Carbon::now()->endOfDay();

        // Mengambil 5 produk terlaris untuk hari ini berdasarkan kuantitas stok keluar
        $topProductsData = OrderItem::query()
            ->select(
                DB::raw('SUM(order_items.quantity) as total_quantity_sold'),
                'order_items.id_product',
                'products.name as product_name' // Ambil nama produk langsung
            )
            ->join('orders', 'order_items.id_order', '=', 'orders.id')
            ->join('products', 'order_items.id_product', '=', 'products.id') // Join ke tabel products
            ->where('orders.status', 'shipped')
            ->where('order_items.fulfillment_type', 'warehouse')
            ->whereBetween('order_items.created_at', [$todayStart, $todayEnd])
            ->groupBy('order_items.id_product', 'products.name') // Group by nama produk juga
            ->orderByDesc('total_quantity_sold')
            ->limit(5) // Ambil hanya 5 produk teratas
            ->get();

        // Mempersiapkan data untuk chart
        $labels = []; // Nama produk
        $quantities = []; // Jumlah terjual

        foreach ($topProductsData as $product) {
            $labels[] = $product->product_name;
            $quantities[] = $product->total_quantity_sold;
        }

        // Jika tidak ada data, pastikan chart tidak error
        if (empty($labels)) {
            $labels = ['Tidak ada data'];
            $quantities = [0];
        }


        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Unit Terjual Hari Ini',
                    'data' => $quantities,
                    'backgroundColor' => [ // Berikan warna berbeda untuk setiap bar jika ingin
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                    ],
                    'borderColor' => [ // Border warna
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    'borderWidth' => 1,
                    // Karena ini line chart, kita bisa isi area di bawah garis
                    // 'fill' => true,
                    // 'tension' => 0.4, // Untuk garis yang sedikit melengkung
                ],
            ],
        ];
    }

    // Opsi chart khusus untuk line chart
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Unit Keluar'
                    ]
                ]
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ]
            ]
        ];
    }
}