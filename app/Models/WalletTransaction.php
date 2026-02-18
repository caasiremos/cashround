<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    public CONST MEMBER_TO_GROUP_FEE = 150;
    public CONST MEMBER_TO_MEMBER_FEE = 1000;
    public CONST STATUS_PENDING = 'pending';
    public CONST STATUS_SUCCESSFUL = 'successful';
    public CONST STATUS_FAILED = 'failed';
    public CONST STATUS_CANCELLED = 'cancelled';

    
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
