<?php

namespace App\Jobs;

use App\Models\Otp;
use App\Utils\SMS;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSMSOtpJob implements ShouldQueue
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
        SMS::send($this->otp->phone_number, 'Hello '. $this->otp->member->first_name . ', Your Cashround Approval Code is ' . $this->otp->code);
    }
}
