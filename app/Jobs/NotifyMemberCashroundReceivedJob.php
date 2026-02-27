<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\Member;
use App\Notifications\FcmNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyMemberCashroundReceivedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $groupId,
        public int $recipientMemberId
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

        $recipient = Member::find($this->recipientMemberId);
        if (! $recipient) {
            return;
        }

        $recipientName = trim(($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? ''));
        if ($recipientName === '') {
            $recipientName = 'A member';
        }

        foreach ($group->members as $groupMember) {
            if (empty($groupMember->fcm_token)) {
                continue;
            }

            $groupMember->notify(new FcmNotification([
                'title' => 'Member Cashround Payout',
                'body' => $recipientName . ' has received cashround payout for this cycle.',
                'data' => [
                    'type' => 'cashround_received',
                    'group_id' => (string) $group->id,
                    'member_id' => (string) $recipient->id,
                ],
            ]));
        }
    }
}
