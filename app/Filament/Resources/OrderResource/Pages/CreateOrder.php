<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            foreach ($this->record->order_items as $item) {
                // Cek apakah pengiriman dari gudang
                if ($item->fulfillment_type === 'warehouse') {
                    WarehouseStock::where([
                        'id_product' => $item->id_product,
                        'id_gudang' => $item->id_gudang
                    ])->decrement('quantity', $item->quantity);
                }
            }
        });

        Notification::make()
            ->title('Order berhasil dibuat & stok gudang diperbarui.')
            ->success()
            ->send();
    }
}
