<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function stock_in()
    {
        return $this->hasMany(StockIn::class);
    }

    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class, 'id_gudang');
    }
}
