<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Prajwal89\EmailManagement\Models\EmailLog;

class FollowUpEmailsSender
{
    // todo: efficiently check if mailbox is being listened (if not this should not send follow up email)
    // it should throw an error
    public function __construct()
    {
        //
    }

    public function send()
    {
        $this->checkIfMailboxAccessible();

        $maxDelayForFollowUpsInDays = config('email-management.max_delay_for_followup_email');

        // Get emails logs that are qualified to send the follow up emails
        $emailLogs = EmailLog::query()
            ->with(['sendable.followUps'])
            // Filters sendables that have followUps
            ->whereHas('sendable.followUps')
            ->successful()
            ->whereNull('replied_at')
            ->whereNull('unsubscribed_at')
            ->where('sent_at', '<', now()->subDays($maxDelayForFollowUpsInDays))
            ->get();

        // loop through the potential emails that may require follow up email

        // $followUpsSent = 0;
        // $followUpsSkipped = 0;

        // // loop through the potential emails that may require follow up email
        // foreach ($emailLogs as $emailLog) {
        //     try {
        //         // Get all enabled follow-ups for this sendable
        //         $followUps = $emailLog
        //             ->sendable
        //             ->followUps()
        //             ->where('is_enabled', true)
        //             ->get();

        //         foreach ($followUps as $followUp) {
        //             // Calculate when this follow-up should be sent
        //             $followUpSendDate = $emailLog->sent_at->addDays($followUp->wait_for_days);

        //             // Check if it's time to send this follow-up
        //             if ($followUpSendDate->isPast()) {
        //                 // Check if follow-up hasn't been sent already
        //                 $alreadySent = EmailLog::query()
        //                     ->where('receivable_id', $emailLog->receivable_id)
        //                     ->where('receivable_type', $emailLog->receivable_type)
        //                     ->where('sendable_id', $followUp->followup_email_event_id)
        //                     ->where('sendable_type', 'App\Models\EmailEvent') // Assuming EmailEvent model
        //                     ->where('sent_at', '>=', $emailLog->sent_at)
        //                     ->exists();

        //                 if (!$alreadySent) {
        //                     // Get the follow-up email event
        //                     $followUpEvent = EmailEvent::find($followUp->followup_email_event_id);

        //                     if ($followUpEvent && $followUpEvent->is_enabled) {

        //                         // Send the follow-up email
        //                         $this->sendFollowUpEmail($emailLog, $followUpEvent, $followUp);
        //                         $followUpsSent++;

        //                         // Log the activity
        //                         \Log::info("Follow-up email sent", [
        //                             'original_email_id' => $emailLog->id,
        //                             'follow_up_event_id' => $followUpEvent->id,
        //                             'recipient' => $emailLog->receivable_type . ':' . $emailLog->receivable_id,
        //                             'days_after_original' => $followUp->wait_for_days
        //                         ]);
        //                     }
        //                 } else {
        //                     $followUpsSkipped++;
        //                     \Log::info("Follow-up email skipped - already sent", [
        //                         'original_email_id' => $emailLog->id,
        //                         'follow_up_event_id' => $followUp->followup_email_event_id,
        //                         'recipient' => $emailLog->receivable_type . ':' . $emailLog->receivable_id
        //                     ]);
        //                 }
        //             }
        //         }
        //     } catch (\Exception $e) {
        //         \Log::error("Error processing follow-up for email log {$emailLog->id}: " . $e->getMessage());
        //         continue;
        //     }
        // }

        dd($emailLogs);
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
