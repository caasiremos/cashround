<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupInvite extends Model
{
    protected $fillable = [
        'group_id',
        'member_id',
        'invite_code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (GroupInvite $invite) {
            $verificationCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $invite->invite_code = $verificationCode;
            $invite->expires_at = now()->addMinutes(5);
        });
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
