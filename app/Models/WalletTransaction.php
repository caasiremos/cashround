<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'source_wallet_id',
        'destination_wallet_id',
        'member_id',
        'transaction_type',
        'amount',
        'service_fee',
        'status',
        'transaction_id',
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
