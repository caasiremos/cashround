<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralLedgerAccount extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'account_type',
        'wallet_id',
        'member_id',
        'group_id',
    ];

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
