<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionAuth extends Model
{
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
}
