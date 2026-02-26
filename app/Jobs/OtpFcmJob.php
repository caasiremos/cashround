<?php

namespace App\Jobs;

use App\Models\Otp;
use App\Notifications\FcmNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class OtpFcmJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Otp $otp)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $member = $this->otp->member;
        if ($member->fcm_token) {
            $notification = [
                'title' => 'Cashround Approval Code',
                'body' => 'Hello '. $member->first_name . ', Your Cashround Approval Code is ' . $this->otp->code,
            ];
            $member->notify(new FcmNotification($notification));
        }
    }
}
