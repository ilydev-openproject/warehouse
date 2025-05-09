<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanaReal extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getSisaSaldoFormattedAttribute()
    {
        $hasil = (int) $this->saldo_awal - (int) $this->saldo_di_tarik;
        return 'Rp ' . number_format($hasil, 2, ',', '.');
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class, 'id_platform');
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'id_toko');
    }
    public function rekening()
    {
        return $this->belongsTo(Rekening::class, 'id_rekening');
    }
}
