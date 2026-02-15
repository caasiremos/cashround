<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MomoTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'member_id',
        'transaction_type',
        'phone_number',
        'amount',
        'provider_fee',
        'service_fee',
        'internal_status',
        'internal_id',
        'external_status',
        'external_id',
        'error_message',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }
}
