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
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
