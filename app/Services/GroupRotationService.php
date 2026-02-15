<?php

namespace App\Services;

use App\Models\Group;
use App\Repositories\GroupRotationRepository;

class GroupRotationService
{
    public function __construct(
        private GroupRotationRepository $groupRotationRepository,
    ) {
    }

    /**
     * Get rotation state for a group: current recipient, next recipient, completed circles.
     *
     * @return array{current_member: \App\Models\Member|null, next_member: \App\Models\Member|null, completed_circles: int, members_in_order: \Illuminate\Support\Collection}
     */
    public function getRotationState(Group $group): array
    {
        return $this->groupRotationRepository->getRotationState($group);
    }

    /**
     * Advance rotation (called after a successful group-to-member transfer to the current recipient).
     */
    public function advanceRotation(Group $group): Group
    {
        return $this->groupRotationRepository->advanceRotation($group);
    }

    /**
     * Get the next rotation position for a new member.
     */
    public function getNextRotationPosition(Group $group): int
    {
        return $this->groupRotationRepository->getNextRotationPosition($group);
    }

    /**
     * Set the current recipient (e.g. to start or reset rotation).
     */
    public function setCurrentRecipient(Group $group, ?int $memberId): Group
    {
        return $this->groupRotationRepository->setCurrentRecipient($group, $memberId);
    }
}
