<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\Member;
use App\Notifications\FcmNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyMemberJoinedGroupJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $groupId,
        public int $newMemberId
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

        $newMember = Member::find($this->newMemberId);
        $newMemberName = $newMember
            ? trim(($newMember->first_name ?? '') . ' ' . ($newMember->last_name ?? ''))
            : '';
        if ($newMemberName === '') {
            $newMemberName = 'A new member';
        }

        $groupName = $group->name;
        $notificationData = [
            'group_id' => (string) $this->groupId,
            'type' => 'member_joined',
            'member_id' => (string) $this->newMemberId,
            'member_name' => $newMemberName,
        ];

        foreach ($group->members as $groupMember) {
            if ($groupMember->id === $this->newMemberId) {
                continue;
            }
            if (empty($groupMember->fcm_token)) {
                continue;
            }
            $title = 'New member joined';
            $body = $newMemberName . ' joined ' . $groupName . '.';
            $groupMember->notify(new FcmNotification([
                'title' => $title,
                'body' => $body,
                'data' => $notificationData,
            ]));
        }
    }
}
