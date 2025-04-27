<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Orders;
use App\Models\StockIn;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OrderStats extends BaseWidget
{

    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $query = Orders::query();
        $stock_in = StockIn::query();

        $startDate = !is_null($this->filters['startDate'] ?? null)
            ? Carbon::parse($this->filters['startDate'])
            : null;

        $endDate = !is_null($this->filters['endDate'] ?? null)
            ? Carbon::parse($this->filters['endDate'])
            : now();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
            $stock_in->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
            $stock_in->whereDate('created_at', '<=', $endDate);
        }

        $totalPesanan = $query->count();
        $pendapatan = Orders::query()
            ->where('status', 'shipped')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('gross_amount');
        $pengeluaran = StockIn::query()
            ->where('keterangan', 'Kulakan')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_harga');

        // Hitung periode sebelumnya
        $diffInDays = $startDate ? $startDate->diffInDays($endDate ?? now()) : 30;

        $previousStart = ($startDate ?? now())->copy()->subDays($diffInDays + 1);
        $previousEnd = ($startDate ?? now())->copy()->subDay();

        $previousTotalPesanan = Orders::query()
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();

        $previousPendapatan = Orders::query()
            ->where('status', 'shipped')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('gross_amount');

        $previousPengeluaran = StockIn::query()
            ->where('keterangan', 'Kulakan')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total_harga');

        // Hitung persentase perubahan
        $persentasePesanan = $previousTotalPesanan > 0
            ? (($totalPesanan - $previousTotalPesanan) / $previousTotalPesanan) * 100
            : 0;

        $persentasePendapatan = $previousPendapatan > 0
            ? (($pendapatan - $previousPendapatan) / $previousPendapatan) * 100
            : 0;

        $persentasePengeluaran = $previousPengeluaran > 0
            ? (($pengeluaran - $previousPengeluaran) / $previousPengeluaran) * 100
            : 0;

        $dates = collect(range(6, 0))->map(fn($daysAgo) => now()->subDays($daysAgo)->toDateString());

        $pesananData = $dates->map(
            fn($date) =>
            Orders::whereDate('created_at', $date)->count()
        )->toArray();

        $pendapatanData = $dates->map(
            fn($date) =>
            Orders::where('status', 'shipped')->whereDate('created_at', $date)->sum('gross_amount')
        )->toArray();

        $kulakanData = $dates->map(
            fn($date) =>
            StockIn::where('keterangan', 'Kulakan')->whereDate('created_at', $date)->sum('total_harga')
        )->toArray();

        return [
            Stat::make('Total Pesanan', $query->count())
                ->description(
                    ($persentasePesanan >= 0 ? 'Increase' : 'Decrease') . ' ' . abs(round($persentasePesanan)) . '%'
                )
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->chart($pesananData)
                ->color($persentasePendapatan >= 0 ? 'success' : 'danger'),

            Stat::make('Pendapatan', 'Rp ' . number_format($query->where('status', 'shipped')->sum('gross_amount'), 0, ',', '.'))
                ->description(
                    ($persentasePesanan >= 0 ? 'Increase' : 'Decrease') . ' ' . abs(round($persentasePendapatan)) . '%'
                )
                ->descriptionIcon('heroicon-o-banknotes')
                ->chart($pendapatanData)
                ->color($persentasePendapatan >= 0 ? 'success' : 'danger'),
            Stat::make('Kulakan', 'Rp ' . number_format($stock_in->where('keterangan', 'Kulakan')->sum('total_harga'), 0, ',', '.'))
                ->description(
                    ($persentasePesanan >= 0 ? 'Increase' : 'Decrease') . ' ' . abs(round($persentasePengeluaran)) . '%'
                )
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->chart($kulakanData)
                ->color($persentasePendapatan >= 0 ? 'success' : 'danger'),
        ];
    }
}
