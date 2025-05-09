<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orders extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function order_items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_order');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function stockOut()
    {
        return $this->hasMany(StockOut::class, 'id');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class, 'id_platform');
    }
}
