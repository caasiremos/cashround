<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'phone_number',
        'code',
        'matched',
        'expires_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'expires_at',
        'matched',
        'id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
