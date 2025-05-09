<?php

namespace App\Filament\Resources\DanaRealResource\Pages;

use App\Filament\Resources\DanaRealResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDanaReals extends ListRecords
{
    protected static string $resource = DanaRealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
