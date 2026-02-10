<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'member_id',
        'balance',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
