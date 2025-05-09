<?php

namespace App\Filament\Resources\DanaRealResource\Pages;

use App\Filament\Resources\DanaRealResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDanaReal extends EditRecord
{
    protected static string $resource = DanaRealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
