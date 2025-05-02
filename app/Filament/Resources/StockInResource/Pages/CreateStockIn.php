<?php

namespace App\Filament\Resources\StockInResource\Pages;

use Filament\Actions;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\StockInResource;

class CreateStockIn extends CreateRecord
{
    protected static string $resource = StockInResource::class;

    protected function afterCreate(): void
    {

        DB::transaction(function () {
            $stockIn = $this->record;

            WarehouseStock::updateOrCreate(
                [
                    'id_product' => $stockIn['id_product'],
                    'id_gudang' => $stockIn['id_gudang']
                ],
                [
                    'quantity' => DB::raw("quantity + {$stockIn['quantity']}")
                ]
            );
        });

        Notification::make()
            ->title('Stok berhasil ditambahkan')
            ->success()
            ->send();
    }
}
