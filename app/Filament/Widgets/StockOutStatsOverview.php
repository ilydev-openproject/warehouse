<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StockOutStatsOverview extends BaseWidget
{
    // Ini harus tetap static (karena Widget::sort itu static)
    protected static ?int $sort = 1;

    // INI YANG HARUS NON-STATIC (tanpa 'static'), SESUAI PESAN ERROR ANDA
    protected ?string $heading = 'Ringkasan Stok Keluar';

    protected function getStats(): array
    {
        $todayStart = Carbon::now()->startOfDay();
        $todayEnd = Carbon::now()->endOfDay();

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();

        $baseQuery = OrderItem::query()
            ->join('orders', 'order_items.id_order', '=', 'orders.id')
            ->join('products', 'order_items.id_product', '=', 'products.id')
            ->where('orders.status', 'shipped')
            ->where('order_items.fulfillment_type', 'warehouse');

        $totalQuantityToday = (clone $baseQuery)
            ->whereBetween('order_items.created_at', [$todayStart, $todayEnd])
            ->sum('order_items.quantity');

        $totalHppToday = (clone $baseQuery)
            ->whereBetween('order_items.created_at', [$todayStart, $todayEnd])
            ->selectRaw('SUM(order_items.quantity * products.hpp) as total_hpp')
            ->value('total_hpp');

        $totalQuantityMonth = (clone $baseQuery)
            ->whereBetween('order_items.created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum('order_items.quantity');

        $totalHppMonth = (clone $baseQuery)
            ->whereBetween('order_items.created_at', [$thisMonthStart, $thisMonthEnd])
            ->selectRaw('SUM(order_items.quantity * products.hpp) as total_hpp')
            ->value('total_hpp');

        return [
            Stat::make('Jumlah Keluar Hari Ini', number_format($totalQuantityToday, 0, ',', '.'))
                ->description('Total unit produk yang keluar hari ini')
                ->color('info')
                ->icon('heroicon-o-truck'),

            Stat::make('HPP Keluar Hari Ini', 'Rp ' . number_format($totalHppToday, 0, ',', '.'))
                ->description('Total HPP produk keluar hari ini')
                ->color('info')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Jumlah Keluar Bulan Ini', number_format($totalQuantityMonth, 0, ',', '.'))
                ->description('Total unit produk keluar bulan ini')
                ->color('success')
                ->icon('heroicon-o-arrow-path'),

            Stat::make('HPP Keluar Bulan Ini', 'Rp ' . number_format($totalHppMonth, 0, ',', '.'))
                ->description('Total HPP produk keluar bulan ini')
                ->color('success')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}