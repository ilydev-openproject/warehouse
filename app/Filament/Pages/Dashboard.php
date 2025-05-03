<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CashOutChart;
use App\Filament\Widgets\OmsetChart;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Filament\Widgets\OrderStats;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm as ConcernsHasFiltersForm;

class Dashboard extends BaseDashboard
{

    use ConcernsHasFiltersForm;

    public function getWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
            FilamentInfoWidget::class,
            OrderStats::class,
            OmsetChart::class,
            CashOutChart::class,
        ];
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->maxDate(fn(Get $get) => $get('endDate') ?: now())
                            ->native(false),
                        DatePicker::make('endDate')
                            ->minDate(fn(Get $get) => $get('startDate') ?: now())
                            ->maxDate(now())
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
