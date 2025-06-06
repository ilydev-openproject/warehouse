<?php

namespace App\Filament\Pages;

use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Filament\Widgets\OmsetChart;
use App\Filament\Widgets\OrderStats;
use Filament\Forms\Components\Select;
use App\Filament\Widgets\CashOutChart;
use Filament\Forms\Components\Section;
use App\Filament\Widgets\StockOutChart;
use Filament\Tables\Concerns\HasFilters;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StockOutStatsOverview;
use App\Filament\Widgets\TopSellingProductsStockOut;
use App\Filament\Widgets\TopSellingProductsDailyChart;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm as ConcernsHasFiltersForm;

class Dashboard extends BaseDashboard
{

    use ConcernsHasFiltersForm, HasFilters;

    public function getWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
            FilamentInfoWidget::class,
            OrderStats::class,
            OmsetChart::class,
            CashOutChart::class,
            StockOutStatsOverview::class, // Tambahkan widget ringkasan Anda di sini
            StockOutChart::class,
            TopSellingProductsDailyChart::class,
        ];
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Dari Tanggal')
                            // <<< Default ke awal hari ini (00:00:00)
                            ->default(now()->startOfDay()->toDateString())
                            ->maxDate(fn(Get $get) => $get('endDate') ?: now())
                            ->native(false),
                        DatePicker::make('endDate')
                            ->label('Sampai Tanggal')
                            // <<< Default ke waktu sekarang (realtime)
                            ->default(now()->toDateString()) // ToDateString() akan mengambil tanggal hari ini saja.
                            // Untuk waktu realtime, DatePicker biasanya hanya menyimpan tanggal.
                            // Pemfilteran waktu realtime akan ditangani di Widget.
                            ->minDate(fn(Get $get) => $get('startDate') ?: now())
                            // MaxDate adalah 'now()' yang berarti hingga waktu sekarang.
                            ->maxDate(now())
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
