<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class Member extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'country',
        'verification_code',
        'email_verified_at',
        'verification_code_expires_at',
    ];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
    ];
    public static function boot()
    {
        parent::boot();
        static::creating(function ($member) {
            $member->password = Hash::make($member->password);
        });
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function momoTransactions()
    {
        return $this->hasMany(MomoTransaction::class);
    }

    public function walletTransferTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class)->withPivot('rotation_position', 'scheduled_cashround_date');
    }

    public function groupInvitesSent()
    {
        return $this->hasMany(GroupInvite::class, 'inviter_id');
    }

    public function groupInvitesReceived()
    {
        return $this->hasMany(GroupInvite::class, 'member_id');
    }

    public function groupRoles()
    {
        return $this->belongsToMany(Group::class, 'group_roles', 'member_id', 'group_id')
            ->withPivot('role')
            ->orderBy('group_roles.role');
    }
}
