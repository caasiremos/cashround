<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransferTransaction extends Model
{
    protected $fillable = [
        'source_wallet_id',
        'destination_wallet_id',
        'member_id',
        'transaction_type_id',
        'source_general_ledger_account_id',
        'destination_general_ledger_account_id',
        'amount',
    ];

    public function sourceWallet()
    {
        return $this->belongsTo(Wallet::class, 'source_wallet_id');
    }

    public function destinationWallet()
    {
        return $this->belongsTo(Wallet::class, 'destination_wallet_id');
    }
}
