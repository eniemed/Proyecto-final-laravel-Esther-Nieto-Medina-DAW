<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class products extends Model
{
    protected $fillable = [
        'name',
        'price',
        'flavor_name',
        'description',
        'weight',
        'image',
        'region'
    ];

    public function flavor() {
        return $this->belongsTo(flavors::class, 'flavor_name', 'name');
    }

    public function suppliers()
    {
        return $this->belongsTo(suppliers::class, 'region', 'region');
    }

    public function orders()
    {
        return $this->hasMany(orders::class, 'product_id', 'id');
    }
}
