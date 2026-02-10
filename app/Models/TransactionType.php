<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function momoTransactions()
    {
        return $this->hasMany(MomoTransaction::class);
    }
    public function walletTransferTransactions()
    {
        return $this->hasMany(WalletTransferTransaction::class);
    }
}
