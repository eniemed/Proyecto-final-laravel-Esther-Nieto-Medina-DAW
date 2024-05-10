<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class suppliers extends Model
{
    protected $fillable = [
        'region'
    ];

    public function products()
    {
        return $this->hasMany(products::class, 'region', 'region');
    }
}
