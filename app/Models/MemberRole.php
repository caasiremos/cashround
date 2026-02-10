<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberRole extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
