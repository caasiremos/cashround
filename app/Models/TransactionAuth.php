<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionAuth extends Model
{
    public CONST STATUS_PENDING = 'pending';
    public CONST STATUS_SUCCESSFUL = 'successful';

    protected $fillable = [
        'group_id',
        'wallet_transaction_id',
        'has_chairperson_approved',
        'has_treasurer_approved',
        'has_secretary_approved',
        'status',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function walletTransaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
