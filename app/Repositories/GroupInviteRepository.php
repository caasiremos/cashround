<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\GroupInvite;
use App\Services\GroupRotationService;
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
        return GroupInvite::create($data);
    }
    /**
     * Get a group invite by invite code
     *
     * @param string $inviteCode
     * @return Group
     */
    public function confirmInvite(string $inviteCode): Group
    {
         return DB::transaction(function () use ($inviteCode) {
            $groupRotationRepository = new GroupRotationRepository();
            $groupInvite = GroupInvite::where('invite_code', $inviteCode)->first();
            $position = $groupRotationRepository->getNextRotationPosition($groupInvite->group);
            $groupInvite->group->members()->syncWithoutDetaching([auth()->user()->id => ['rotation_position' => $position]]);
            return $groupInvite->group->fresh();
        });
    }
}