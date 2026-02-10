<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'transactable_id',
        'transactable_type',
        'transaction_date',
        'transaction_name',
        'amount',
        'is_reversed',
        'reversed_journal_entry_id',
        'reversal_reason',
    ];

    public function transactable()
    {
        return $this->morphTo();
    }

    public function reversedJournalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_journal_entry_id');
    }
    
    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }
}
