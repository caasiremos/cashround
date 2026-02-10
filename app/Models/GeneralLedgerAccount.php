<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralLedgerAccount extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'account_type',
    ];

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
