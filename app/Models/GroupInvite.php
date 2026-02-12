<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GroupInvite extends Model
{
    /**
     * Token: used for mobile deep/universal links (e.g. cashround://invite/{token})
     * so opening a shared link can open the app and resolve the invite.
     */
    protected $fillable = [
        'group_id',
        'inviter_id',
        'email',
        'member_id',
        'token',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (GroupInvite $invite) {
            if (empty($invite->token)) {
                $invite->token = Str::random(64);
            }
        });
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function inviter()
    {
        return $this->belongsTo(Member::class, 'inviter_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
