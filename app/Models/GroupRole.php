<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupRole extends Model
{
    const ADMIN = 'admin';
    const CHAIRPERSON = 'chairperson';
    const MEMBER = 'member';
    const TREASURER = 'treasurer';
    const SECRETARY = 'secretary';

    protected $fillable = [
        'group_id',
        'member_id',
        'role',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
