<?php

namespace App\Jobs;

use App\Models\Member;
use App\Notifications\FcmNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifiyMemberTransferMadeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $sourceMemberId,
        public int $destinationMemberId,
        public float $amount
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $members = Member::query()
            ->whereIn('id', [$this->sourceMemberId, $this->destinationMemberId])
            ->get()
            ->keyBy('id');

        $sourceMember = $members->get($this->sourceMemberId);
        $destinationMember = $members->get($this->destinationMemberId);

        if (! $sourceMember || ! $destinationMember) {
            return;
        }

        $sourceName = trim(($sourceMember->first_name ?? '') . ' ' . ($sourceMember->last_name ?? '')) ?: 'A member';
        $destinationName = trim(($destinationMember->first_name ?? '') . ' ' . ($destinationMember->last_name ?? '')) ?: 'A member';
        $formattedAmount = number_format($this->amount);

        if (! empty($sourceMember->fcm_token)) {
            $sourceMember->notify(new FcmNotification([
                'title' => 'Wallet Transfer',
                'body' => 'Your transfer of UGX' . $formattedAmount . ' to ' . $destinationName . ' was successful.',
                'data' => [
                    'type' => 'member_transfer_sent',
                    'counterparty_member_id' => (string) $destinationMember->id,
                    'amount' => (string) $this->amount,
                ],
            ]));
        }

        if (! empty($destinationMember->fcm_token)) {
            $destinationMember->notify(new FcmNotification([
                'title' => 'Transfer received',
                'body' => $sourceName . ' sent you UGX' . $formattedAmount . '.',
                'data' => [
                    'type' => 'member_transfer_received',
                    'counterparty_member_id' => (string) $sourceMember->id,
                    'amount' => (string) $this->amount,
                ],
            ]));
        }
    }
}
