<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'member_id',
        'balance',
    ];

    public static function boot()
    {
        parent::boot();
         static::creating(function ($wallet) {
            $wallet->account_number = 'CR-' . str_pad(self::max('id') + 1, 5, '0', STR_PAD_LEFT);
            $wallet->balance = 0;
        });
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
