<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Jobs;

use DirectoryTree\ImapEngine\MessageInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\BounceParser;
use Prajwal89\EmailManagement\Dtos\BounceDataDto;
use Prajwal89\EmailManagement\Models\EmailLog;

class HandleNewEmailReceivedJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public MessageInterface $message) {}

    public function handle()
    {
        $parser = new BounceParser;

        // we can mock this to test
        $bounceParser = $parser->parse($this->message->__toString());

        // ! remove this
        Log::info('Message', [
            'message' => $this->message->__toString(),
            'bounceResult' => $bounceParser,
        ]);

        // this means email is not bounce notification
        if ($bounceParser) {
            return $this->handleBounce($bounceParser);
        }

        return $this->handleReply();
    }

    public function handleReply()
    {
        if (!$this->message->inReplyTo()) {
            return;
        }

        // got reply for this message_id
        $inReplyToMessageId = $this->message->inReplyTo()->email();

        EmailLog::query()
            ->where('message_id', $inReplyToMessageId)
            ->first()
            ?->update([
                // ? track reply message id
                'reply_message_id' => $this->message->messageId(),
                'replied_at' => $this->message->date() ?? now(),
            ]);
    }

    public function handleBounce(BounceDataDto $bounceDataDto)
    {
        // ! remove this
        Log::info('Email is not bounce notification', [
            'message_id' => $this->message->messageId(),
            'subject' => $this->message->subject(),
        ]);

        $updateData = [];

        if (str($bounceDataDto->statusCode)->startsWith('5')) {
            $updateData['hard_bounced_at'] = now();
        } elseif (str($bounceDataDto->statusCode)->startsWith('4')) {
            $updateData['soft_bounced_at'] = now();
        }

        $originalMessageId = $bounceDataDto->originalMessage['message_id'];

        $emailLog = EmailLog::query()
            ->where('message_id', $originalMessageId)
            ->first();

        if ($emailLog === null) {
            return;
        }

        $emailLog->update([
            'bounce_code' => $bounceDataDto->statusCode,
            'bounce_reason' => $bounceDataDto->reason,
            ...$updateData,
        ]);
    }
}
