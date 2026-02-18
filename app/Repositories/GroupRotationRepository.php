<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\Member;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GroupRotationRepository
{
    /**
     * Add a number of frequency intervals to a date.
     */
    public function addInterval(CarbonImmutable $date, string $frequency, int $steps): CarbonImmutable
    {
        $f = strtolower($frequency);
        return match ($f) {
            'daily' => $date->addDays($steps),
            'weekly' => $date->addWeeks($steps),
            'monthly' => $date->addMonths($steps),
            'yearly' => $date->addYears($steps),
            default => $date->addMonths($steps),
        };
    }

    /**
     * Compute the scheduled cashround date for a slot in a given circle (0-based).
     * Slot index = member's position in rotation for that circle.
     */
    public function getComputedScheduledDateForSlot(Group $group, int $circleIndex, int $slotIndex, int $memberCount): CarbonImmutable
    {
        $start = CarbonImmutable::parse($group->start_date);
        $steps = $circleIndex * $memberCount + $slotIndex;

        return $this->addInterval($start, $group->frequency, $steps);
    }

    /**
     * Get the scheduled cashround date for a member (uses stored override if set, otherwise computed from start_date + frequency).
     */
    public function getScheduledDateForMember(Group $group, Member $member, Collection $membersInOrder): CarbonImmutable
    {
        $index = $membersInOrder->search(fn (Member $m) => (int) $m->id === (int) $member->id);
        if ($index === false) {
            $index = 0;
        }
        $count = $membersInOrder->count();
        $circle = (int) $group->completed_circles;

        $stored = $member->pivot->scheduled_cashround_date ?? null;
        if ($stored !== null) {
            return CarbonImmutable::parse($stored);
        }

        return $this->getComputedScheduledDateForSlot($group, $circle, $index, $count);
    }

    /**
     * Get rotation state plus scheduled cashround date for current and next member, and for all members in order.
     *
     * @return array{current_member: Member|null, next_member: Member|null, completed_circles: int, members_in_order: Collection, current_scheduled_date: CarbonImmutable|null, next_scheduled_date: CarbonImmutable|null, members_with_dates: array}
     */
    public function getRotationStateWithDates(Group $group): array
    {
        $state = $this->getRotationState($group);
        $members = $state['members_in_order'];
        $count = $members->count();

        if ($count === 0) {
            $state['current_scheduled_date'] = null;
            $state['next_scheduled_date'] = null;
            $state['members_with_dates'] = [];

            return $state;
        }

        $current = $state['current_member'];
        $next = $state['next_member'];
        $circle = $state['completed_circles'];
        $currentIndex = $current ? $members->search(fn (Member $m) => (int) $m->id === (int) $current->id) : 0;
        if ($currentIndex === false) {
            $currentIndex = 0;
        }
        $nextIndex = $next ? $members->search(fn (Member $m) => (int) $m->id === (int) $next->id) : 1;
        if ($nextIndex === false || $nextIndex >= $count) {
            $nextIndex = $count > 1 ? 1 : 0;
        }

        $state['current_scheduled_date'] = $current
            ? $this->getScheduledDateForMember($group, $current, $members)->format('Y-m-d')
            : null;
        $state['next_scheduled_date'] = $next
            ? $this->getScheduledDateForMember($group, $next, $members)->format('Y-m-d')
            : null;

        $membersWithDates = [];
        foreach ($members as $m) {
            $membersWithDates[] = [
                'member' => $m,
                'scheduled_cashround_date' => $this->getScheduledDateForMember($group, $m, $members)->format('Y-m-d'),
            ];
        }
        $state['members_with_dates'] = $membersWithDates;

        return $state;
    }

    /**
     * Whether the current recipient's scheduled cashround date has passed (today is after their date).
     */
    public function hasCurrentRecipientDatePassed(Group $group): bool
    {
        $state = $this->getRotationState($group);
        $current = $state['current_member'];
        if ($current === null) {
            return false;
        }
        $members = $state['members_in_order'];
        $date = $this->getScheduledDateForMember($group, $current, $members);

        return $date->endOfDay()->isPast();
    }

    /**
     * When the current recipient's cashround date has passed without payment: set a new date for them
     * (after all members whose rotation is not yet reached), then advance rotation to the next member.
     */
    public function rescheduleCurrentRecipientToEndOfRound(Group $group): Group
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

        $circle = (int) $group->completed_circles;
        $start = CarbonImmutable::parse($group->start_date);
        $steps = ($circle + 1) * $count;
        $newDate = $this->addInterval($start, $group->frequency, $steps);

        DB::table('group_member')
            ->where('group_id', $group->id)
            ->where('member_id', $currentId)
            ->update(['scheduled_cashround_date' => $newDate->format('Y-m-d')]);

        $nextIndex = ($currentIndex + 1) % $count;
        $nextMember = $members->get($nextIndex);
        $completedCircles = $nextIndex === 0 ? $circle + 1 : $circle;

        $group->update([
            'current_recipient_member_id' => $nextMember->id,
            'completed_circles' => $completedCircles,
        ]);

        return $group->fresh();
    }
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
            DB::table('group_member')
                ->where('group_id', $group->id)
                ->where('member_id', $currentId)
                ->update(['scheduled_cashround_date' => null]);
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
