<?php

namespace App\Repositories;

use App\Exceptions\ExpectedException;
use App\Models\GeneralLedgerAccount;
use App\Models\Group;
use App\Models\GroupRole;
use App\Models\Member;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupRepository
{
    /**
     * Get the wallet balance of a group
     *
     * @param Group $group
     * @return float
     */
    public function getGroupWalletBalance(Group $group): float
    {
        return $group->wallet->balance;
    }
   
    /**
     * Get the groups of a member
     *
     * @param Member $member
     * @return Collection
     */
    public function getMemberGroups(Member $member): Collection
    {
        return $member->groups;
    }

    /**
     * Get the members of a group
     *
     * @param Group $group
     * @return Collection
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $group->members;
    }

    /**
     * Create a new group
     *
     * @param array $data
     * @return Group
     */
    public function createGroup(array $data): Group
    {
        return DB::transaction(function () use ($data) {
            $group = Group::create([
                'owner_id' => auth()->user()->id,
                'name' => $data['name'],
                'frequency' => $data['frequency'],
                'start_date' => now(),
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'slug' => Str::slug($data['name']) . '-' . Str::random(5),
            ]);
            $this->createGroupWallet($group);
            $group->members()->attach($group->owner_id, ['rotation_position' => 0]);

            return $group;
        });
    }

    /**
     * Create a new wallet for a group
     *
     * @param Group $group
     * @return Wallet
     */
    public function createGroupWallet(Group $group): Wallet
    {
        return $group->wallet()->create();
    }


    /**
     * Get a group by id
     *
     * @param int $id
     * @return Group
     */
    public function getGroupById(int $id): Group
    {
        return Group::find($id);
    }

    /**
     * Set the role of a member in a group.
     * The same role cannot be held by another member in the same group.
     *
     * @param array $data
     * @example ['group_id' => 1, 'member_id' => 1, 'role' => 'chairperson']
     * @return GroupRole
     * @throws ExpectedException when the role is already assigned to another member in the group
     */
    public function setMemberRole(array $data): GroupRole
    {
        $roleName = strtolower($data['role']);
        $groupId = (int) $data['group_id'];
        $memberId = (int) $data['member_id'];

        $existing = GroupRole::where('group_id', $groupId)
            ->where('role', $roleName)
            ->where('member_id', '!=', $memberId)
            ->exists();

        if ($existing) {
            throw new ExpectedException('This role is already assigned to another member in the group.');
        }

        return GroupRole::updateOrCreate(
            [
                'group_id' => $groupId,
                'member_id' => $memberId,
            ],
            ['role' => $roleName]
        )->fresh();
    }

    /**
     * Remove the role of a member in a group
     *
     * @param int $groupId
     * @param int $memberId
     */
    public function removeMemberRole(int $groupId, int $memberId)
    {
        return GroupRole::where('group_id', $groupId)->where('member_id', $memberId)->delete();
    }
}
