<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Member;
use App\Repositories\GroupRotationRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ContributionDueReminderService
{
    public function __construct(
        private GroupRotationRepository $rotationRepository
    ) {}

    /**
     * Get groups that should receive a contribution reminder today,
     * and for each group return the members to email and reminder context.
     *
     * - Daily: every day at midday → all active daily groups.
     * - Weekly: first day (scheduled date) and last day (scheduled + 6) of current period.
     * - Monthly: first day (scheduled date) and last day of that month.
     *
     * @return array<int, array{group: Group, members: Collection<int, Member>, due_date: string, is_last_day: bool}>
     */
    public function getGroupsAndMembersDueForReminderToday(): array
    {
        $today = CarbonImmutable::today();
        $result = [];

        $groups = Group::query()
            ->where('status', 'active')
            ->whereIn('frequency', ['Daily', 'Weekly', 'Monthly'])
            ->get();

        foreach ($groups as $group) {
            $frequency = strtolower($group->frequency);

            if ($frequency === 'daily') {
                $result[] = [
                    'group' => $group,
                    'members' => $group->members,
                    'due_date' => $today->format('Y-m-d'),
                    'is_last_day' => true,
                ];
                continue;
            }

            $state = $this->rotationRepository->getRotationStateWithDates($group);
            $currentScheduled = $state['current_scheduled_date'] ?? null;

            if ($currentScheduled === null) {
                continue;
            }

            $scheduled = CarbonImmutable::parse($currentScheduled);

            if ($frequency === 'weekly') {
                $firstDay = $scheduled;
                $lastDay = $scheduled->addDays(6);
                $isFirstDay = $today->isSameDay($firstDay);
                $isLastDay = $today->isSameDay($lastDay);
                if ($isFirstDay || $isLastDay) {
                    $result[] = [
                        'group' => $group,
                        'members' => $group->members,
                        'due_date' => $lastDay->format('Y-m-d'),
                        'is_last_day' => $isLastDay,
                    ];
                }
            } elseif ($frequency === 'monthly') {
                $firstDay = $scheduled;
                $lastDay = $scheduled->endOfMonth();
                $isFirstDay = $today->isSameDay($firstDay);
                $isLastDay = $today->isSameDay($lastDay);
                if ($isFirstDay || $isLastDay) {
                    $result[] = [
                        'group' => $group,
                        'members' => $group->members,
                        'due_date' => $lastDay->format('Y-m-d'),
                        'is_last_day' => $isLastDay,
                    ];
                }
            }
        }

        return $result;
    }
}
