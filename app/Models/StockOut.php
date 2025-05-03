<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockOut extends Model
{
    use HasFactory;
    protected $table = 'order_items'; // pakai tabel order_items

    public function scopeWarehouse($query, $startDate = null, $endDate = null)
    {
        $query->whereHas('order', function ($query) {
            $query->where('fulfillment_type', 'warehouse');
        });

        if ($startDate) {
            $query->whereDate('orders.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('orders.created_at', '<=', $endDate);
        }

        return $query;
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'id_order');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }
}
