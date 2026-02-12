<?php

namespace App\Repositories;

use App\Models\GeneralLedgerAccount;
use App\Models\Group;
use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberRepository
{
    /**
     * Get all members of a group
     *
     * @param Group $group
     * @return Collection
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $group->members;
    }

    /**
     * Get a member by id
     *
     * @param int $id
     * @return Member|null
     */
    public function getMemberById(int $id): ?Member
    {
        return Member::find($id);
    }

    /**
     * Get a member by email
     *
     * @param string $email
     * @return Member|null
     */
    public function findByEmail(string $email): ?Member
    {
        return Member::query()->where('email', $email)->first();
    }

    /**
     * Create a new member
     *
     * @param array $data
     * @return Member
     */
    public function createMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $member = Member::create($data);
            $member->wallet()->create();
            $this->createGeneralLedgerAccount($member);

            return $member;
        });
    }

    /**
     * Create a new general ledger account for a member
     *
     * @param Member $member
     * @return GeneralLedgerAccount
     */
    public function createGeneralLedgerAccount(Member $member): GeneralLedgerAccount
    {
        return $member->generalLedgerAccounts()->create([
            'name' => 'ACC - ' . $member->first_name . ' ' . $member->last_name,
            'slug' => Str::slug('ACC - ' . $member->first_name . ' ' . $member->last_name) . '-' . $member->id,
            'account_type' => 'liability',
            'wallet_id' => $member->wallet->id,
            'member_id' => $member->id
        ]);
    }
}