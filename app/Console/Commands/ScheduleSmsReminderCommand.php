<?php

namespace App\Console\Commands;

use App\Utils\SMS;
use Illuminate\Console\Command;

class ScheduleSmsReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:schedule-sms-reminder-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = SMS::send('+256786966244', 'Hello, this is a test SMS');
    }
}
