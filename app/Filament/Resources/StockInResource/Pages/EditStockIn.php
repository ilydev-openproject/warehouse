<?php

namespace App\Filament\Resources\StockInResource\Pages;

use Filament\Actions;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\StockInResource;

class EditStockIn extends EditRecord
{
    protected static string $resource = StockInResource::class;

    protected function beforeSave()
    {
        $originalQty = $this->record->quantity;
        $newQty = $this->data['quantity'];
        $diff = $newQty - $originalQty;

        DB::transaction(function () use ($diff) {
            // Update warehouse stock
            WarehouseStock::where([
                'id_product' => $this->record->id_product,
                'id_gudang' => $this->record->id_gudang
            ])->increment('quantity', $diff);
        });
    }
}
