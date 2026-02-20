<?php

namespace App\Jobs;

use App\Mail\ResetPasswordMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendResetPasswordEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $email, public string $token)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void {
        Mail::to($this->email)->send(new ResetPasswordMail($this->token));
    }
}
