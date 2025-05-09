<?php

namespace App\Models;

use App\Models\TemplateItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Template extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function items()
    {
        return $this->hasMany(TemplateItem::class);
    }
}
