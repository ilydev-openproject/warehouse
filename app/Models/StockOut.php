<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockOut extends Model
{
    use HasFactory;
    protected $table = 'order_items';

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

    public function scopeSumQuantityByProductGudangDate(Builder $query, $productId, $gudangId, $date)
    {
        return $query->where('id_product', $productId)
            ->where('id_gudang', $gudangId)
            ->whereDate('order_items.created_at', $date)
            ->groupBy('id_product', 'id_gudang', 'created_at')  // Menambahkan group by untuk pengelompokan berdasarkan tanggal
            ->sum('quantity');
    }

}
