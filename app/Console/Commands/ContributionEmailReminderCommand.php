<?php

namespace App\Console\Commands;

use App\Mail\ContributionReminderMail;
use App\Notifications\FcmNotification;
use App\Services\ContributionDueReminderService;
use App\Utils\SMS;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ContributionEmailReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contribution:email-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to members whose group contributions are due (daily at midday; weekly/monthly on first and last day of period)';

    /**
     * Execute the console command.
     */
    public function handle(ContributionDueReminderService $reminderService): int
    {
        $items = $reminderService->getGroupsAndMembersDueForReminderToday();
        $sent = 0;

        foreach ($items as $item) {
            $group = $item['group'];
            $dueDate = $item['due_date'];
            $isLastDay = $item['is_last_day'];

            foreach ($item['members'] as $member) {
                if (empty($member->email)) {
                    continue;
                }

                // Mail::to($member->email)->send(new ContributionReminderMail(
                //     $group,
                //     $dueDate,
                //     $isLastDay,
                //     $member->first_name ?? '',
                // ));
                SMS::send($member->phone_number, 'Hello '. $member->first_name . ', Your contribution for '.$group->name.' is due today.');

                if (! empty($member->fcm_token)) {
                    $title = $isLastDay
                        ? 'Contribution due today'
                        : 'Contribution period started';
                    $body = $isLastDay
                        ? "Your contribution for {$group->name} is due today."
                        : "Contribution period for {$group->name} has started. Amount due: " . number_format($group->amount) . '.';
                    $member->notify(new FcmNotification([
                        'title' => $title . ' - ' . $group->name,
                        'body' => $body,
                        'data' => ['group_id' => (string) $group->id, 'type' => 'contribution_reminder'],
                    ]));
                }

                $sent++;
            }
        }

        $this->info("Sent {$sent} contribution reminder email(s).");

        return self::SUCCESS;
    }
}
