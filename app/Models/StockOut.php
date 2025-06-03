<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    use HasFactory;

    // Arahkan ke tabel order_items karena query akan mengolah data dari sana
    protected $table = 'order_items';

    // Tidak perlu timestamps jika model ini hanya untuk pembacaan data yang diagregasi
    public $timestamps = false;

    // Relasi untuk memudahkan pengambilan nama dari kolom terkait
    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function order()
    {
        return $this->belongsTo(Orders::class, 'id_order');
    }

}