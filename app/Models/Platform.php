<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function order()
    {
        return $this->hasMany(Orders::class, 'id_platform');
    }

    public function dana_real()
    {
        return $this->hasMany(DanaReal::class, 'id_platform');
    }
}
