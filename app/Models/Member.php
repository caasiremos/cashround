<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Member extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'country',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
        return $this->hasMany(WalletTransferTransaction::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
