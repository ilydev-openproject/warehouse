<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StockOutStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Ringkasan Stok Keluar';

    protected function getStats(): array
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

        // Query dasar
        $baseQuery = OrderItem::query()
            ->join('orders', 'order_items.id_order', '=', 'orders.id')
            ->join('products', 'order_items.id_product', '=', 'products.id')
            ->where('order_items.fulfillment_type', 'warehouse')
            ->whereBetween('order_items.created_at', [$startDate, $endDate]);

        // Hitung total kuantitas dan HPP
        $totalQuantityFiltered = (clone $baseQuery)->sum('order_items.quantity');
        $totalHppFiltered = (clone $baseQuery)->selectRaw('SUM(order_items.quantity * products.hpp) as total_hpp')->value('total_hpp');

        // --- STAT BARU DI SINI ---

        // 1. Jumlah Produk Unik Keluar
        $uniqueProductsCount = (clone $baseQuery)->distinct('id_product')->count('id_product');

        // 2. Jumlah Pesanan Keluar (Shp/Dikirim)
        // Kita perlu menghitung jumlah order unik yang terkait dengan order_items yang sudah difilter
        $shippedOrdersCount = (clone $baseQuery)
            ->where('orders.status', 'shipped')
            ->distinct('id_order')
            ->count('id_order');

        // Tentukan label stat berdasarkan periode filter
        $statLabel = '';
        if ($startDate->isSameDay($endDate)) {
            $statLabel = 'Hari Ini';
        } elseif ($startDate->isSameMonth($endDate) && $startDate->isSameYear($endDate) && $startDate->startOfMonth()->eq($startDate) && $endDate->endOfMonth()->eq($endDate->endOfDay())) {
            $statLabel = 'Bulan Ini';
        } else {
            $statLabel = 'Periode Terpilih';
        }

        return [
            Stat::make('Jumlah Keluar ' . $statLabel, number_format($totalQuantityFiltered, 0, ',', '.'))
                ->description('Total unit produk yang keluar')
                ->color('info')
                ->icon('heroicon-o-truck'),

            Stat::make('HPP Keluar ' . $statLabel, 'Rp ' . number_format($totalHppFiltered, 0, ',', '.'))
                ->description('Total HPP produk keluar')
                ->color('info')
                ->icon('heroicon-o-currency-dollar'),

            // --- TAMBAHAN STAT BARU ---
            Stat::make('Produk Unik Keluar', number_format($uniqueProductsCount, 0, ',', '.'))
                ->description('Jumlah jenis produk berbeda')
                ->color('warning')
                ->icon('heroicon-o-tag'), // Ikon yang relevan

            Stat::make('Jumlah Pesanan Dikirim', number_format($shippedOrdersCount, 0, ',', '.'))
                ->description('Total pesanan berstatus dikirim')
                ->color('success')
                ->icon('heroicon-o-archive-box'), // Ikon yang relevan
        ];
    }
}