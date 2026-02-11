<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function members()
    {
        return $this->belongsToMany(Member::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function generalLedgerAccounts()
    {
        return $this->hasMany(GeneralLedgerAccount::class);
    }
}
