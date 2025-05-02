<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\OrderResource;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function beforeSave(): void
    {
        DB::transaction(function () {
            // Ambil item sebelum diupdate dari database
            $originalItems = $this->record->order_items()->get()->keyBy('id');

            foreach ($this->data['order_items'] as $index => $updatedItem) {
                $itemId = $updatedItem['id'] ?? null;
                $productId = $updatedItem['id_product'];
                $gudangId = $updatedItem['id_gudang'];
                $newQty = $updatedItem['quantity'];
                $fulfillmentType = $updatedItem['fulfillment_type'];

                // Jika warehouse, maka perlu update stok
                if ($fulfillmentType === 'warehouse') {
                    $originalQty = 0;

                    if ($itemId && isset($originalItems[$itemId])) {
                        $originalQty = $originalItems[$itemId]->quantity;
                    }

                    $diff = $originalQty - $newQty;

                    WarehouseStock::where([
                        'id_product' => $productId,
                        'id_gudang' => $gudangId,
                    ])->increment('quantity', $diff);
                }
            }
        });

        Notification::make()
            ->title('Order berhasil diperbarui & stok gudang disesuaikan.')
            ->success()
            ->send();
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

