<?php

namespace App\Jobs;

use App\Mail\VerificationCodeMail;
use App\Models\Member;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendVerificationCodeEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Member $member
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->member->verification_code) {
            return;
        }

        Mail::to($this->member->email)->send(
            new VerificationCodeMail($this->member->verification_code, $this->member->first_name)
        );
    }
}
