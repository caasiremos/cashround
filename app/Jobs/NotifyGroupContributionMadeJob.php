<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\Member;
use App\Notifications\FcmNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyGroupContributionMadeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $groupId,
        public int $contributorMemberId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $group = Group::with('members')->find($this->groupId);
        if (! $group) {
            return;
        }

        $contributor = Member::find($this->contributorMemberId);
        $contributorName = $contributor
            ? trim(($contributor->first_name ?? '') . ' ' . ($contributor->last_name ?? ''))
            : '';
        if ($contributorName === '') {
            $contributorName = 'A member';
        }

        $groupName = $group->name;
        $notificationData = [
            'group_id' => (string) $this->groupId,
            'type' => 'contribution_made',
        ];

        foreach ($group->members as $groupMember) {
            if (empty($groupMember->fcm_token)) {
                continue;
            }
            $isContributor = (int) $groupMember->id === $this->contributorMemberId;
            $title = 'Contribution made';
            $body = $isContributor
                ? $contributorName . ' made a contribution for ' . $groupName . '.'
                : 'A contribution for ' . $groupName . ' has been made.';
            $groupMember->notify(new FcmNotification([
                'title' => $title,
                'body' => $body,
                'data' => $notificationData,
            ]));
        }
    }
}
