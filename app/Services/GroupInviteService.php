<?php

namespace App\Services;

use App\Exceptions\ExpectedException;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Repositories\GroupInviteRepository;

class GroupInviteService
{
    public function __construct(private GroupInviteRepository $groupInviteRepository) { }

    /**
     * Send an invite to join a group
     *
     * @param array $data
     * @return GroupInvite
     */
    public function sendInvite(array $data): GroupInvite
    {
        $group = Group::find($data['group_id']);
        if (!$group) {
            throw new ExpectedException('Group not found.');
        }
        if($group->owner_id !== auth()->user()->id) {
            throw new ExpectedException('Only the group owner can send invites.');
        }

        return $this->groupInviteRepository->createGroupInvite($data);
    }

    /**
     * Accept an invite by invite code
     *
     * @param string $inviteCode
     * @return Group
     */
    public function acceptInvite(string $inviteCode): Group
    {
        $invite = GroupInvite::query()->where('invite_code', $inviteCode)->first();
        if (!$invite) {
            throw new ExpectedException('Invite not found.');
        }
        if($invite->expires_at < now()) {
            throw new ExpectedException('Invite has expired.');
        }
        return $this->groupInviteRepository->acceptInvite($invite);
    }
}
