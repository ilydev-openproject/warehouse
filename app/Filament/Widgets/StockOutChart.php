<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class StockOutChart extends ChartWidget
{
    // Ini harus static
    protected static ?string $heading = 'Tren Stok Keluar Bulanan';
    // Ini harus static
    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $labels = [];
        $quantities = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');

            $totalQuantity = OrderItem::query()
                ->join('orders', 'order_items.id_order', '=', 'orders.id')
                ->where('orders.status', 'shipped')
                ->where('order_items.fulfillment_type', 'warehouse')
                ->whereYear('order_items.created_at', $month->year)
                ->whereMonth('order_items.created_at', $month->month)
                ->sum('order_items.quantity');

            $quantities[] = $totalQuantity;
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
}