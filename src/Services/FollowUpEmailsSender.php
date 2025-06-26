<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\FollowUp;

class FollowUpEmailsSender
{
    // it should throw an error
    public function __construct()
    {
        //
    }

    public function send()
    {
        // check if mailbox is being listened (if not this should not send follow up email)
        // as user cannot listen to the emails
        $this->checkIfMailboxAccessible();

        // Get emails logs that may require to send follow up emails
        $emailLogs = EmailLog::query()
            ->select([
                'id',
                'message_id',
                'sendable_id',
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

        // check all followups for a sendable (are they sent ) if not are they qualified to send
        // if yes sent it

        // loop through the potential emails that may require follow up email
        foreach ($emailLogs as $emailLog) {

            // Get all follow-ups for this sendable
            $followUps = $emailLog
                ->sendable
                ->followUps;

            // dd($followUps->toArray());

            // ch
            foreach ($followUps as $followUp) {

                // Calculate when this follow-up should be sent
                $followUpSendDate = $emailLog->sent_at->addDays($followUp->wait_for_days);

                // dump($followUp);
                dump($followUpSendDate->toDateString());

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

                // dd('ds');

                // Get the follow-up email event
                $followUpEvent = EmailEvent::find($followUp->followup_email_event_id);

                if ($followUpEvent) {
                    // Send the follow-up email
                    $this->sendFollowUpEmail($emailLog, $followUpEvent, $followUp);

                    //
                    return;
                }
            }
        }
    }

    public static function checkIfFollowUpEmailSent(
        EmailLog $emailLog,
        FollowUp $followUp
    ) {
        return EmailLog::query()
            ->where('receivable_id', $emailLog->receivable_id)
            ->where('receivable_type', $emailLog->receivable_type)
            ->where('sendable_id', $followUp->followup_email_event_id)
            ->where('sendable_type', EmailEvent::class)
            ->where('sent_at', '>=', $emailLog->sent_at)
            ->exists();
    }

    public function sendFollowUpEmail(
        EmailLog $emailLog,
        EmailEvent $followUpEvent,
        FollowUp $followUp
    ) {
        // dd($emailLog->message_id);

        dump(
            'Follow up email will be sent',
            $emailLog,
            $followUpEvent,
            $followUp
        );

        // todo add subject header as "Re: "
        $handler = $followUpEvent->resolveEmailHandler();

        // (new $handler($emailLog->receivable))
        //     // hind a better way to pass the message id
        //     ->buildEmail($emailLog->message_id)
        //     ->send();

        // Log the activity
        Log::info('Follow-up email sent', [
            'original_email_id' => $emailLog->id,
            'follow_up_event_id' => $followUpEvent->id,
            'recipient' => $emailLog->receivable_type . ':' . $emailLog->receivable_id,
            'days_after_original' => $followUp->wait_for_days,
        ]);
    }

    public function checkIfMailboxAccessible()
    {
        //
    }

    public function schedule()
    {
        //
    }
}
