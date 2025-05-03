<?php

namespace App\Models;

use App\Models\StockIn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function stockIns()
    {
        return $this->hasMany(StockIn::class, 'id_product');
    }

    public function stockOut()
    {
        return $this->hasMany(StockOut::class, 'id_product');
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class, 'id_product');
    }
}
