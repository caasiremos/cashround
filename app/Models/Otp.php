<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Otp extends Model
{
    protected $fillable = [
        'member_id',
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


    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
