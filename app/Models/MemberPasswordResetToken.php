<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPasswordResetToken extends Model
{
    protected $fillable = [
        'email',
        'token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
