<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function dana_real(): HasMany
    {
        return $this->hasMany(DanaReal::class);
    }
}
