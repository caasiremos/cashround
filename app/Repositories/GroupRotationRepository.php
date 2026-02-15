<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\Member;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GroupRotationRepository
{
    /**
     * Get members in rotation order (by rotation_position pivot).
     *
     * @return Collection<int, Member>
     */
    public function getMembersInRotationOrder(Group $group): Collection
    {
        return $group->membersInRotationOrder()->get();
    }

    /**
     * Get current rotation state: current recipient, next recipient, completed circles.
     *
     * @return array{current_member: Member|null, next_member: Member|null, completed_circles: int, members_in_order: Collection}
     */
    public function getRotationState(Group $group): array
    {
        $members = $this->getMembersInRotationOrder($group);
        $count = $members->count();

        if ($count === 0) {
            return [
                'current_member' => null,
                'next_member' => null,
                'completed_circles' => (int) $group->completed_circles,
                'members_in_order' => $members,
            ];
        }

        $currentMember = null;
        $nextMember = null;
        $currentIndex = null;

        if ($group->current_recipient_member_id) {
            $currentIndex = $members->search(fn (Member $m) => (int) $m->id === (int) $group->current_recipient_member_id);
            if ($currentIndex !== false) {
                $currentMember = $members->get($currentIndex);
                $nextIndex = ($currentIndex + 1) % $count;
                $nextMember = $members->get($nextIndex);
            }
        }

        if ($currentMember === null) {
            $currentMember = $members->first();
            $nextMember = $count > 1 ? $members->get(1) : $members->first();
        }

        return [
            'current_member' => $currentMember,
            'next_member' => $nextMember,
            'completed_circles' => (int) $group->completed_circles,
            'members_in_order' => $members,
        ];
    }

    /**
     * Advance rotation after a successful group-to-member transfer to the current recipient.
     * Sets current_recipient_member_id to the next member; increments completed_circles when wrapping to first.
     */
    public function advanceRotation(Group $group): Group
    {
        $members = $this->getMembersInRotationOrder($group);
        $count = $members->count();

        if ($count === 0) {
            return $group;
        }

        $currentId = $group->current_recipient_member_id;
        $currentIndex = $currentId
            ? $members->search(fn (Member $m) => (int) $m->id === (int) $currentId)
            : 0;

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $nextIndex = ($currentIndex + 1) % $count;
        $nextMember = $members->get($nextIndex);
        $completedCircles = (int) $group->completed_circles;

        if ($nextIndex === 0) {
            $completedCircles++;
        }

        $group->update([
            'current_recipient_member_id' => $nextMember->id,
            'completed_circles' => $completedCircles,
        ]);

        return $group->fresh();
    }

    /**
     * Get the next available rotation position for a new member (max + 1).
     */
    public function getNextRotationPosition(Group $group): int
    {
        $max = DB::table('group_member')->where('group_id', $group->id)->max('rotation_position');

        return $max === null ? 0 : (int) $max + 1;
    }

    /**
     * Set current recipient (e.g. to start or reset rotation). Used when no payment has been made yet.
     */
    public function setCurrentRecipient(Group $group, ?int $memberId): Group
    {
        $group->update(['current_recipient_member_id' => $memberId]);

        return $group->fresh();
    }
}
