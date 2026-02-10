<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    protected $fillable = [
        'general_ledger_account_id',
        'journal_entry_id',
        'credit_record',
        'debit_record',
        'balance_before',
        'balance_after',
        'is_reversal',
    ];

    public function generalLedgerAccount()
    {
        return $this->belongsTo(GeneralLedgerAccount::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
