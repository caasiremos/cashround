<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'frequency',
        'start_date',
        'end_date',
        'amount',
        'balance',
        'status',
        'owner_id',
        'current_recipient_member_id',
        'completed_circles',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'members',
        'wallet',
        'generalLedgerAccounts',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
        ];
    }

    public function members()
    {
        return $this->belongsToMany(Member::class)->withPivot('rotation_position');
    }

    /**
     * Members ordered by rotation position (for rotation cycle).
     */
    public function membersInRotationOrder()
    {
        return $this->belongsToMany(Member::class)
            ->withPivot('rotation_position')
            ->orderBy('group_member.rotation_position')
            ->orderBy('members.id');
    }

    public function currentRecipientMember()
    {
        return $this->belongsTo(Member::class, 'current_recipient_member_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function generalLedgerAccounts()
    {
        return $this->hasMany(GeneralLedgerAccount::class);
    }

    public function invites()
    {
        return $this->hasMany(GroupInvite::class, 'group_id');
    }

    public function owner()
    {
        return $this->belongsTo(Member::class, 'owner_id');
    }
}
