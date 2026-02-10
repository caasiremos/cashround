<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionAuth extends Model
{
    protected $fillable = [
        'group_id',
        'has_chairperson_approved',
        'has_treasurer_approved',
        'has_secretary_approved',
        'has_admin_approved',
        'amount',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
