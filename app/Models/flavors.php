<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class flavors extends Model
{
    protected $primaryKey = 'name';

    protected $fillable = [
        'name',
        'description',
    ];

    public function products() {
        return $this->hasMany(products::class, 'flavor_name', 'name');
    }
}
