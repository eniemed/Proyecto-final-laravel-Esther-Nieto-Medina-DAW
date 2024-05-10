<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class users extends Model
{

    public $timestamps = false;
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'order_id',
        'wishlist'
    ];

    protected $primaryKey = 'username';
    public $incrementing = false;

    public function orders()
    {
        return $this->belongsTo(orders::class, 'order_id', 'id');
    }
}
