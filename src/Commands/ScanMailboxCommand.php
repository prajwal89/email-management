<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use DirectoryTree\ImapEngine\Enums\ImapFetchIdentifier;
use DirectoryTree\ImapEngine\Mailbox;
use DirectoryTree\ImapEngine\Message;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailLog;

use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

/**
 * Scan Emails for 
 *  - Check bounced emails
 *  - Check if email is a reply for our previous emails
 * 
 * @see https://imapengine.com/docs/usage/messages  
 */
// php artisan email-management:scan-mailbox
class ScanMailboxCommand extends Command
{
    protected $signature = 'email-management:scan-mailbox';

    protected $description = 'Command description';

    public function handle(): void
    {
        $mailboxConfig = config('mail.mailboxes.' . config('email-management.mailbox'));

        // dd($mailboxConfig);

        $mailbox = new Mailbox($mailboxConfig);

        $inbox = $mailbox->folders()->find('INBOX');

        $messages = $inbox
            ->messages()
            // ->messages(ImapFetchIdentifier::Uid)
            ->withHeaders()
            ->withBody()
            // ->sortDesc()
            ->limit(20)
            ->get();

        $messages->map(function (Message $message) {
            $messageId = $message->messageId();

            // dd($message->inReplyTo());
            if ($message->inReplyTo()) {
                $inReplyToMessageId = $message->inReplyTo()->email();
                EmailLog::query()
                    ->where('message_id', $inReplyToMessageId)
                    ->first()?->update([
                        'in_reply_to' => $inReplyToMessageId,
                        'replied_at' => $message->date(),
                    ]);
            }
            // dd($message);
        });

        // dd($messages);
    }
}
