<?php

namespace App\Jobs;

use App\Models\Group;
use App\Notifications\FcmNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyPayoutInitiatedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $groupId,
        public int $destinationMemberId
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

        $destinationMember = $group->members->firstWhere('id', $this->destinationMemberId);
        $destinationMemberName = $destinationMember
            ? trim(($destinationMember->first_name ?? '') . ' ' . ($destinationMember->last_name ?? ''))
            : '';
        if ($destinationMemberName === '') {
            $destinationMemberName = 'destination wallet member';
        }

        foreach ($group->members as $groupMember) {
            if (empty($groupMember->fcm_token)) {
                continue;
            }

            $groupMember->notify(new FcmNotification([
                'title' => 'Payout Initiated',
                'body' => 'Payout to ' . $destinationMemberName . ' has been initiated by admin.',
                'data' => [
                    'type' => 'payout_initiated',
                    'group_id' => (string) $group->id,
                    'member_id' => (string) $this->destinationMemberId,
                ],
            ]));
        }
    }
}
