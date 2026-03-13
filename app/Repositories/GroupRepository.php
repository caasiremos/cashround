<?php

namespace App\Repositories;

use App\Enums\TransactionTypeEnum;
use App\Exceptions\ExpectedException;
use App\Models\Group;
use App\Models\GroupRole;
use App\Models\Member;
use App\Models\TransactionAuth;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupRepository
{
    public function __construct() {}

    /**
     * Get the wallet balance of a group
     */
    public function getGroupWalletBalance(Group $group): array
    {
        return [
            'balance' => $group->wallet->balance,
            'account_number' => $group->wallet->account_number,
        ];
    }

    /**
     * Get the groups of a member
     */
    public function getMemberGroups(Member $member): Collection
    {
        return $member->groups;
    }

    /**
     * Get the members of a group
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $group->members;
    }

    /**
     * Create a new group
     */
    public function createGroup(array $data): Group
    {
        return DB::transaction(function () use ($data) {
            $group = Group::create([
                'owner_id' => auth()->user()->id,
                'name' => $data['name'],
                'frequency' => $data['frequency'],
                'start_date' => $data['start_date'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'slug' => Str::slug($data['name']) . '-' . Str::random(5),
            ]);
            $group->members()->attach(auth()->user()->id, ['rotation_position' => 0]);
            $this->createGroupWallet($group);

            return $group;
        });
    }

    /**
     * Update group details. Editing is only allowed when the current circle has ended,
     * because amount and frequency are tied to a circle. Start date cannot be changed
     * after the first circle has ended.
     *
     * @throws ExpectedException when the current circle is not yet complete, or when changing start date after the first circle
     */
    public function editGroup(Group $group, array $data): Group
    {
        $groupRotationRepository = new GroupRotationRepository;

        $hasContributions = WalletTransaction::where('group_id', $group->id)
            ->where('transaction_type', TransactionTypeEnum::MEMBER_TO_GROUP->value)
            ->where('status', WalletTransaction::STATUS_SUCCESSFUL)
            ->exists();

        $hasGroupToMemberPayouts = WalletTransaction::where('group_id', $group->id)
            ->where('transaction_type', TransactionTypeEnum::GROUP_TO_MEMBER->value)
            ->where('status', WalletTransaction::STATUS_SUCCESSFUL)
            ->exists();

        $isMidCycle = $groupRotationRepository->isRotationOrderUpdateBlocked($group);

        if (!$hasGroupToMemberPayouts && $hasContributions && $isMidCycle) {
            throw new ExpectedException(
                'Group cannot be edited until the current circle ends.'
            );
        }

        $allowed = ['name', 'description', 'frequency', 'start_date', 'amount'];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if ($group->completed_circles > 0 && array_key_exists('start_date', $filtered)) {
            throw new ExpectedException(
                'Start date cannot be edited after the first circle has ended.'
            );
        }

        $group->name = data_get($filtered, 'name');
        $group->description = data_get($filtered, 'description');
        $group->frequency = data_get($filtered, 'frequency');
        $group->start_date = $group->completed_circles > 0
            ? $group->start_date
            : data_get($filtered, 'start_date');
        $group->amount = intval(data_get($filtered, 'amount'));
        $group->save();

        return $group->fresh();
    }

    /**
     * Create a new wallet for a group
     */
    public function createGroupWallet(Group $group): Wallet
    {
        return $group->wallet()->create([
            'group_id' => $group->id,
            'account_number' => 'CRG' . str_pad(Wallet::max('id') + 1, 5, '0', STR_PAD_LEFT),
            'balance' => 0,
        ]);
    }

    /**
     * Get a group by id
     */
    public function getGroupById(int $id): Group
    {
        return Group::find($id);
    }

    /**
     * Set the role of a member in a group.
     * The same role cannot be held by another member in the same group.
     *
     * @example ['group_id' => 1, 'member_id' => 1, 'role' => 'chairperson']
     *
     * @throws ExpectedException when the role is already assigned to another member in the group
     */
    public function setMemberRole(array $data): GroupRole
    {
        $roleName = strtolower($data['role']);
        $groupId = (int) $data['group_id'];
        $memberId = (int) $data['member_id'];

        $memberExistingRole = GroupRole::where('group_id', $groupId)
            ->where('member_id', $memberId)
            ->first();

        if ($memberExistingRole) {
            if ($memberExistingRole->role === $roleName) {
                return $memberExistingRole->fresh();
            }
            throw new ExpectedException('Member cannot have two roles in the same group.');
        }

        $roleTakenByAnother = GroupRole::where('group_id', $groupId)
            ->where('role', $roleName)
            ->exists();

        if ($roleTakenByAnother) {
            throw new ExpectedException('This role is already assigned to another member in the group.');
        }

        return GroupRole::create([
            'group_id' => $groupId,
            'member_id' => $memberId,
            'role' => $roleName,
        ])->fresh();
    }

    /**
     * Remove the role of a member in a group
     */
    public function removeMemberRole(int $groupId, int $memberId)
    {
        return GroupRole::where('group_id', $groupId)->where('member_id', $memberId)->delete();
    }

    /**
     * Get all transaction auths for a group
     */
    public function getGroupTransactionAuth(int $groupId): ?TransactionAuth
    {
        return TransactionAuth::where('group_id', $groupId)
            ->where('status', TransactionAuth::STATUS_PENDING)
            ->latest()
            ->first();
    }

    /**
     * Close a group
     *
     * @return Group
     *
     * @throws ExpectedException when a contribution has already been made (cycle is in progress)
     */
    public function closeGroup(Group $group)
    {
        $hasContributions = WalletTransaction::where('group_id', $group->id)
            ->where('transaction_type', TransactionTypeEnum::MEMBER_TO_GROUP->value)
            ->where('status', WalletTransaction::STATUS_SUCCESSFUL)
            ->exists();

        $hasGroupToMemberPayouts = WalletTransaction::where('group_id', $group->id)
            ->where('transaction_type', TransactionTypeEnum::GROUP_TO_MEMBER->value)
            ->where('status', WalletTransaction::STATUS_SUCCESSFUL)
            ->exists();

        $isMidCycle = (new GroupRotationRepository)->isRotationOrderUpdateBlocked($group);

        if (!$hasGroupToMemberPayouts && $hasContributions && $isMidCycle) {
            throw new ExpectedException('Group cannot be closed because a rotation cycle is in progress.');
        }

        return DB::transaction(function () use ($group) {
            $group->status = Group::STATUS_CLOSED;
            $group->end_date = now();
            $group->save();

            return $group;
        });
    }

    /**
     * Leave a group
     *
     * @return Group
     */
    public function removeMemberFromGroup(Group $group, Member $member)
    {
        if ((new GroupRotationRepository)->isRotationOrderUpdateBlocked($group)) {
            throw new ExpectedException('Member cannot be removed or leave the group until the current circle ends.');
        }

        return DB::transaction(function () use ($group, $member) {
            $group->members()->detach($member->id);
            $group->groupRoles()->where('member_id', $member->id)->delete();

            return $group->fresh();
        });
    }
}
