<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class orders extends Model
{
    protected $fillable = [
        'product_id',
        'order_date'
    ];

    public function products()
    {
        return $this->belongsTo(products::class, 'product_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(users::class, 'order_id', 'id');
    }
}
