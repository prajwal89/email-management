<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Mail\Mailable;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\FollowUp;

class FollowUpEmailsSender
{
    public function __construct() {}

    public function send()
    {
        $this->checkIfMailboxAccessible();

        // Get emails logs that may require to send follow up emails
        $emailLogs = EmailLog::query()
            ->select([
                'message_id',
                'sendable_slug',
                'sendable_type',
                'receivable_id',
                'receivable_type',
                'sent_at',
            ])
            ->with(['sendable.followUps'])
            // Filters sendables that have followUps
            ->whereHas('sendable.followUps')
            ->successful()
            ->whereNull('replied_at')
            ->whereNull('unsubscribed_at')
            // date between min and max delay between followups
            ->where('sent_at', '<', now()->subDays(config('email-management.min_delay_for_followup_email')))
            ->where('sent_at', '>', now()->subDays(config('email-management.max_delay_for_followup_email')))
            ->get();

        $followUpsToSend = [];

        // loop through the potential emails that may require follow up email
        foreach ($emailLogs as $emailLog) {
            // Get all follow-ups for this sendable
            $followUps = $emailLog->sendable->followUps->where('is_enabled', 1);

            foreach ($followUps as $followUp) {
                // Calculate when this follow-up should be sent
                $followUpSendDate = $emailLog->sent_at->addDays($followUp->wait_for_days);

                // Check if it's time to send this follow-up
                if (!$followUpSendDate->isPast()) {
                    // not in past so it must be in future so skip the email
                    continue;
                }

                // Check if follow-up hasn't been sent already
                $alreadySent = self::checkIfFollowUpEmailSent(
                    $emailLog,
                    $followUp
                );

                if ($alreadySent) {
                    continue;
                }

                $followUpEvent = EmailEvent::query()
                    ->where('slug', $followUp->followup_email_event_slug)
                    ->first();

                if ($followUpEvent) {
                    $followUpsToSend[] = [
                        'emailLog' => $emailLog,
                        'followUpEvent' => $followUpEvent,
                        'followUp' => $followUp,
                    ];

                    continue;
                }
            }
        }

        // dd($followUpsToSend);

        foreach ($followUpsToSend as $emailData) {
            $this->sendFollowUpEmail(
                emailLog: $emailData['emailLog'],
                followUpEvent: $emailData['followUpEvent']
            );
        }
    }

    protected function hasAlreadySentFollowUp(EmailLog $emailLog, $followUp): bool
    {
        // Check if a follow-up email has already been sent for this email log
        return EmailLog::query()
            ->where('receivable_id', $emailLog->receivable_id)
            ->where('receivable_type', $emailLog->receivable_type)
            ->where('sendable_slug', $followUp->followup_email_event_slug)
            ->where('sendable_type', EmailEvent::class)
            ->where('sent_at', '>=', $emailLog->sent_at)
            ->exists();
    }

    public static function checkIfFollowUpEmailSent(EmailLog $emailLog, FollowUp $followUp): bool
    {
        return EmailLog::query()
            ->where('receivable_id', $emailLog->receivable_id)
            ->where('receivable_type', $emailLog->receivable_type)
            ->where('sendable_slug', $followUp->followup_email_event_slug)
            ->where('sendable_type', EmailEvent::class)
            ->where('sent_at', '>=', $emailLog->sent_at)
            ->exists();
    }

    // ? should i move this to the job
    public function sendFollowUpEmail(
        EmailLog $emailLog,
        EmailEvent $followUpEvent,
    ) {
        // dd($emailLog->message_id);

        dump(
            // $emailLog,
            $followUpEvent->name,
        );

        // todo add subject header as "Re: "
        $handler = $followUpEvent->resolveEmailHandler();

        (new $handler($emailLog->receivable))
            // ! why this is not working
            // ->modifyEmailUsing(function (Mailable $email) use ($emailLog) {
            //     $email->subject("Re: {$emailLog->subject}");
            // })
            // find a better way to pass the message id
            ->buildEmail($emailLog->message_id)
            ->send();
    }

    // check if mailbox is being listened (if not this should not send follow up email)
    // as user cannot listen to the emails
    // throws error
    public function checkIfMailboxAccessible()
    {
        //
    }
}
