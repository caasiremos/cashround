<?php

namespace App\Jobs;

use App\Models\Group;
use App\Notifications\FcmNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyPayoutApprovalByRoleJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $groupId,
        public string $role
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

        $roleLabel = ucwords(str_replace('_', ' ', strtolower($this->role)));

        foreach ($group->members as $groupMember) {
            if (empty($groupMember->fcm_token)) {
                continue;
            }

            $groupMember->notify(new FcmNotification([
                'title' => 'Payout Approval',
                'body' => $roleLabel . ' has approved payout for cashround.',
                'data' => [
                    'type' => 'payout_approval',
                    'group_id' => (string) $group->id,
                    'role' => strtolower($this->role),
                ],
            ]));
        }
    }
}
