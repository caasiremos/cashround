<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MomoTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'member_id',
        'transaction_type_id',
        'transaction_name',
        'phone_number',
        'amount',
        'transaction_fee',
        'service_fee',
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
