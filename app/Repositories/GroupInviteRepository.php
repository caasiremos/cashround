<?php

namespace App\Repositories;

use App\Exceptions\ExpectedException;
use App\Models\Group;
use App\Models\GroupInvite;
use Illuminate\Support\Facades\DB;

class GroupInviteRepository
{
    /**
     * Create a new group invite
     *
     * @param array $data
     * @return GroupInvite
     */
    public function createGroupInvite(array $data): GroupInvite
    {
        $groupInvite = GroupInvite::updateOrCreate(
            [
                'group_id' => $data['group_id'],
                'member_id' => auth()->user()->id
            ],
            ['invite_code' => $data['invite_code'], 'expires_at' => now()->addYears(5)]
        );
        return $groupInvite;
    }

    /**
     * Get a group invite by invite code
     *
     * @param GroupInvite $groupInvite
     * @return Group
     */
    public function acceptInvite(GroupInvite $groupInvite): Group
    {
        $group = $groupInvite->group;

        if ((new GroupRotationRepository())->isRotationOrderUpdateBlocked($group)) {
            throw new ExpectedException(
                'You cannot join the group until the current group circle is done.'
            );
        }

        return DB::transaction(function () use ($groupInvite) {
            $memberId = auth()->user()->id;

            if ($groupInvite->group->members()->where('members.id', $memberId)->exists()) {
                return $groupInvite->group->fresh();
            }

            $groupRotationRepository = new GroupRotationRepository();
            $position = $groupRotationRepository->getNextRotationPosition($groupInvite->group);
            $groupInvite->group->members()->syncWithoutDetaching([$memberId => ['rotation_position' => $position]]);

            return $groupInvite->group->fresh();
        });
    }
}
