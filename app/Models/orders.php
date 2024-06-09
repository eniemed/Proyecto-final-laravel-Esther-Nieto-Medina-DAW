<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class orders extends Model
{
    protected $fillable = [
        'username'
    ];

    public $timestamps = false;

    public function users()
    {
        return $this->belongsTo(users::class, 'username', 'username');
    }
}
