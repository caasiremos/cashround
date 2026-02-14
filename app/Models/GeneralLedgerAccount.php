<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class GeneralLedgerAccount extends Model
{
    const TYPE_ASSET = 'asset';
    const TYPE_LIABILITY = 'liability';
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';
    const TYPE_EQUITY = 'equity';


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

    /**
     * Compute the balance of the general ledger account
     *
     * @return float
     */
    public function computeBalance(): float
    {
        $formula = $this->account_type === self::TYPE_LIABILITY
            ? 'line_items.credit_record - line_items.debit_record'
            : 'line_items.debit_record - line_items.credit_record';

        return (float) DB::table('line_items')
            ->join('journal_entries', 'line_items.journal_entry_id', '=', 'journal_entries.id')
            ->whereNull('journal_entries.deleted_at')
            ->where('journal_entries.is_reversed', 0)
            ->whereNull('journal_entries.reversed_journal_entry_id')
            ->where('line_items.general_ledger_account_id', $this->id)
            ->selectRaw("COALESCE(SUM($formula), 0) AS balance")
            ->value('balance');
    }
}
