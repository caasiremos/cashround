<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'member_id',
        'group_id',
        'account_number',
        'balance',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
