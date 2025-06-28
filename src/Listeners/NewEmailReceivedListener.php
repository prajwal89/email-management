<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use DirectoryTree\ImapEngine\Laravel\Events\MessageReceived;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\EmailLog;
use Carbon\Carbon;
use DirectoryTree\ImapEngine\MessageInterface;
use Prajwal89\EmailManagement\BounceParser;

// todo this should be job as exception here will break the command execution
// and it will be more easy to track jobs

/**
 * Enhanced email listener that handles bounces, replies, and conversation threading
 * 
 * @see https://imapengine.com/docs/laravel/usage
 */
class NewEmailReceivedListener
{
    private const HARD_BOUNCE_CODES = [
        '5.1.1', // User unknown
        '5.1.2', // Host unknown
        '5.1.3', // Bad destination address
        '5.1.10', // Recipient address rejected
        '5.2.1', // Mailbox disabled
        '5.4.1', // No answer from host
        '5.4.4', // Host not found
        '5.7.1', // Delivery not authorized
        '5.7.17', // Mailbox owner has changed
        '5.7.23', // SPF validation failed
    ];

    private const SOFT_BOUNCE_CODES = [
        '4.2.2', // Mailbox full
        '4.3.2', // System not accepting messages
        '4.4.2', // Connection timeout
        '4.4.7', // Message expired
        '4.7.1', // Temporary policy rejection
        '4.7.12', // Message requires authentication
    ];

    public function handle(MessageReceived $event): void
    {
        $message = $event->message;

        // dd($message->__toString());

        $parser = new BounceParser();
        $result = $parser->parse($message->__toString());

        // 4. Dump the result
        // It will be a BounceData object or null.
        dd($result);
    }

    /**
     * Check if email is bounce message
     */
    // private function isBounceMessage(MessageInterface $message): bool
    // {
    //     $headers = $message->headers();
    //     $from = strtolower($message->from()->email());
    //     $subject = strtolower($message->subject());
    //     $contentType = strtolower($headers['Content-Type']);

    //     // Check for delivery status notification content type
    //     if (
    //         str_contains($contentType, 'multipart/report') &&
    //         str_contains($contentType, 'delivery-status')
    //     ) {
    //         return true;
    //     }

    //     // Check auto-submitted header
    //     $autoSubmitted = strtolower($headers['Auto-Submitted'] ?? '');
    //     if (in_array($autoSubmitted, ['auto-replied', 'auto-generated'])) {
    //         return true;
    //     }

    //     // Check common bounce sender patterns
    //     $bounceSenders = [
    //         'mailer-daemon',
    //         'postmaster',
    //         'mail-daemon',
    //         'mailerdaemon',
    //         'noreply',
    //         'no-reply',
    //         'bounce',
    //         'delivery-subsystem'
    //     ];

    //     foreach ($bounceSenders as $sender) {
    //         if (str_contains($from, $sender)) {
    //             return true;
    //         }
    //     }

    //     // Check common bounce subject patterns
    //     $bounceSubjects = [
    //         'undelivered',
    //         'delivery status notification',
    //         'returned mail',
    //         'mail delivery failed',
    //         'delivery failure',
    //         'message not delivered',
    //         'undeliverable',
    //         'permanent failure',
    //         'delivery notification'
    //     ];

    //     foreach ($bounceSubjects as $bounceSubject) {
    //         if (str_contains($subject, strtolower($bounceSubject))) {
    //             return true;
    //         }
    //     }

    //     // Check for specific bounce headers
    //     if (
    //         $headers['X-Failed-Recipients'] ?? null ||
    //         $headers['X-Actual-Recipient'] ?? null ||
    //         $headers['Reporting-MTA'] ?? null
    //     ) {
    //         return true;
    //     }

    //     return false;
    // }

    // private function isAutoReply($message): bool
    // {
    //     $headers = $message->headers();
    //     $subject = strtolower($message->subject());

    //     // Check for auto-reply indicators
    //     $autoSubmitted = strtolower($headers->get('Auto-Submitted', ''));
    //     if ($autoSubmitted === 'auto-replied') {
    //         return true;
    //     }

    //     // Check for precedence header
    //     $precedence = strtolower($headers->get('Precedence', ''));
    //     if ($precedence === 'auto_reply') {
    //         return true;
    //     }

    //     // Check subject patterns for out-of-office
    //     $autoReplyPatterns = [
    //         'out of office',
    //         'automatic reply',
    //         'auto reply',
    //         'away message',
    //         'vacation reply',
    //         'holiday reply',
    //         'absence notification'
    //     ];

    //     foreach ($autoReplyPatterns as $pattern) {
    //         if (str_contains($subject, $pattern)) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    // private function processBounceMessage(MessageInterface $message): void
    // {
    //     try {
    //         $originalMessageId = $this->extractOriginalMessageId($message);

    //         if (!$originalMessageId) {
    //             Log::warning('Could not extract original message ID from bounce', [
    //                 'from' => $message->from()->email(),
    //                 'subject' => $message->subject(),
    //                 'uid' => $message->uid(),
    //             ]);
    //             return;
    //         }

    //         $bounceInfo = $this->analyzeBounceType($message);

    //         $emailLog = EmailLog::where('message_id', $originalMessageId)->first();

    //         if (!$emailLog) {
    //             Log::warning('Email log not found for bounced message', [
    //                 'original_message_id' => $originalMessageId,
    //                 'bounce_from' => $message->from()->email(),
    //             ]);
    //             return;
    //         }

    //         // Update bounce information based on type
    //         $updateData = [
    //             'bounce_code' => $bounceInfo['code'],
    //             'bounce_reason' => $bounceInfo['reason'],
    //         ];

    //         if ($bounceInfo['type'] === 'hard') {
    //             $updateData['hard_bounced_at'] = $message->date() ?? now();
    //         } elseif ($bounceInfo['type'] === 'soft') {
    //             $updateData['soft_bounced_at'] = $message->date() ?? now();
    //         }

    //         $emailLog->update($updateData);

    //         Log::info('Bounce processed successfully', [
    //             'original_message_id' => $originalMessageId,
    //             'bounce_type' => $bounceInfo['type'],
    //             'bounce_code' => $bounceInfo['code'],
    //             'bounce_reason' => $bounceInfo['reason'],
    //             'email_log_id' => $emailLog->id,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Error processing bounce message', [
    //             'error' => $e->getMessage(),
    //             'from' => $message->from()->email(),
    //             'subject' => $message->subject(),
    //             'uid' => $message->uid(),
    //         ]);
    //     }
    // }

    // private function processAutoReply(MessageInterface $message): void
    // {
    //     if (!$message->inReplyTo()) {
    //         return;
    //     }

    //     $inReplyToMessageId = $message->inReplyTo()->email();

    //     EmailLog::where('message_id', $inReplyToMessageId)
    //         ->first()
    //         ?->update([
    //             'in_reply_to' => $inReplyToMessageId,
    //             'replied_at' => $message->date() ?? now(),
    //         ]);

    //     Log::info('Auto-reply processed', [
    //         'original_message_id' => $inReplyToMessageId,
    //         'auto_reply_from' => $message->from()->email(),
    //         'subject' => $message->subject(),
    //     ]);
    // }

    // private function processReply(MessageInterface $message): void
    // {
    //     $inReplyToMessageId = $message->inReplyTo()->email();

    //     $emailLog = EmailLog::where('message_id', $inReplyToMessageId)->first();

    //     if ($emailLog) {
    //         $emailLog->update([
    //             'in_reply_to' => $inReplyToMessageId,
    //             'replied_at' => $message->date() ?? now(),
    //         ]);

    //         Log::info('Reply processed', [
    //             'original_message_id' => $inReplyToMessageId,
    //             'reply_from' => $message->from()->email(),
    //             'subject' => $message->subject(),
    //             'email_log_id' => $emailLog->id,
    //         ]);
    //     } else {
    //         Log::warning('Email log not found for reply', [
    //             'in_reply_to_message_id' => $inReplyToMessageId,
    //             'reply_from' => $message->from()->email(),
    //         ]);
    //     }
    // }

    // private function extractOriginalMessageId(MessageInterface $message): ?string
    // {
    //     $body = $message->body();
    //     $headers = $message->headers();

    //     // Try to get from headers first
    //     $failedRecipients = $headers->get('X-Failed-Recipients');
    //     if ($failedRecipients) {
    //         return trim($failedRecipients, '<>');
    //     }

    //     $actualRecipient = $headers->get('X-Actual-Recipient');
    //     if ($actualRecipient) {
    //         return trim($actualRecipient, '<>');
    //     }

    //     // Try multiple regex patterns to find original message ID in body
    //     $patterns = [
    //         '/Message-ID:\s*<([^>]+)>/mi',
    //         '/Original-Message-ID:\s*<([^>]+)>/mi',
    //         '/X-Failed-Recipients:\s*.*?Message-ID:\s*<([^>]+)>/mis',
    //         '/Original message follows:\s*.*?Message-ID:\s*<([^>]+)>/mis',
    //         '/The following message could not be delivered:\s*.*?Message-ID:\s*<([^>]+)>/mis',
    //         '/<([^@]+@[^>]+)>/m', // Generic email-like pattern in angle brackets
    //     ];

    //     foreach ($patterns as $pattern) {
    //         if (preg_match($pattern, $body, $matches)) {
    //             $messageId = trim($matches[1]);

    //             // Validate that it looks like a message ID
    //             if (str_contains($messageId, '@') && !str_contains($messageId, ' ')) {
    //                 return $messageId;
    //             }
    //         }
    //     }

    //     return null;
    // }

    // private function analyzeBounceType($message): array
    // {
    //     $body = $message->body();
    //     $headers = $message->headers();

    //     // Initialize variables
    //     $statusCode = null;
    //     $enhancedCode = null;

    //     // Look for SMTP status codes in various formats
    //     $patterns = [
    //         '/(\d{3})\s+(\d\.\d\.\d)/m',           // Standard: 550 5.1.1
    //         '/Status:\s*(\d\.\d\.\d)/mi',          // Status: 5.1.1
    //         '/diagnostic-code.*?(\d{3})/mi',        // diagnostic-code field
    //         '/(\d\.\d\.\d).*?(\d{3})/mi',          // Enhanced code first
    //         '/Final-Recipient:.*?\n.*?Status:\s*(\d\.\d\.\d)/mis', // DSN format
    //     ];

    //     foreach ($patterns as $pattern) {
    //         if (preg_match($pattern, $body, $matches)) {
    //             if (count($matches) >= 3) {
    //                 $statusCode = $matches[1];
    //                 $enhancedCode = $matches[2];
    //             } else {
    //                 $enhancedCode = $matches[1];
    //             }
    //             break;
    //         }
    //     }

    //     return $this->classifyBounce($statusCode, $enhancedCode, $body);
    // }

    // private function classifyBounce(?string $statusCode, ?string $enhancedCode, string $body): array
    // {
    //     $type = 'unknown';
    //     $reason = $this->extractBounceReason($body, $enhancedCode);

    //     // Classify by enhanced status code
    //     if ($enhancedCode) {
    //         if (in_array($enhancedCode, self::HARD_BOUNCE_CODES)) {
    //             $type = 'hard';
    //         } elseif (in_array($enhancedCode, self::SOFT_BOUNCE_CODES)) {
    //             $type = 'soft';
    //         } elseif (str_starts_with($enhancedCode, '5.')) {
    //             $type = 'hard';
    //         } elseif (str_starts_with($enhancedCode, '4.')) {
    //             $type = 'soft';
    //         }
    //     }

    //     // Fallback to SMTP status code
    //     if ($type === 'unknown' && $statusCode) {
    //         if (str_starts_with($statusCode, '5')) {
    //             $type = 'hard';
    //         } elseif (str_starts_with($statusCode, '4')) {
    //             $type = 'soft';
    //         }
    //     }

    //     // Content-based classification as last resort
    //     if ($type === 'unknown') {
    //         $type = $this->classifyByContent($body);
    //     }

    //     return [
    //         'type' => $type,
    //         'code' => $enhancedCode ?: $statusCode,
    //         'reason' => $reason
    //     ];
    // }

    // private function extractBounceReason(string $body, ?string $enhancedCode): string
    // {
    //     // Map of enhanced codes to human-readable reasons
    //     $reasonMap = [
    //         '5.1.1' => 'User unknown - The email address does not exist',
    //         '5.1.2' => 'Host unknown - The domain does not exist',
    //         '5.1.3' => 'Bad destination address - Invalid email format',
    //         '5.1.10' => 'Recipient address rejected - Email address rejected by server',
    //         '5.2.1' => 'Mailbox disabled - The recipient mailbox is disabled',
    //         '5.2.2' => 'Mailbox full - The recipient mailbox is full',
    //         '5.4.1' => 'No answer from host - Server not responding',
    //         '5.4.4' => 'Host not found - Unable to resolve domain',
    //         '5.7.1' => 'Delivery not authorized - Message rejected by policy',
    //         '4.2.2' => 'Mailbox full (temporary) - Try again later',
    //         '4.3.2' => 'System not accepting messages - Temporary server issue',
    //         '4.4.2' => 'Connection timeout - Network timeout occurred',
    //         '4.7.1' => 'Temporary policy rejection - Try again later',
    //     ];

    //     if ($enhancedCode && isset($reasonMap[$enhancedCode])) {
    //         return $reasonMap[$enhancedCode];
    //     }

    //     // Extract reason from bounce message content
    //     $reasonPatterns = [
    //         '/reason:\s*([^\n\r]+)/mi',
    //         '/diagnostic-code.*?:\s*([^\n\r]+)/mi',
    //         '/(\d{3}\s+[^\n\r]+)/m',
    //         '/Error:\s*([^\n\r]+)/mi',
    //         '/Failed:\s*([^\n\r]+)/mi',
    //     ];

    //     foreach ($reasonPatterns as $pattern) {
    //         if (preg_match($pattern, $body, $matches)) {
    //             return trim(strip_tags($matches[1]));
    //         }
    //     }

    //     return 'Bounce reason could not be determined';
    // }

    // private function classifyByContent(string $body): string
    // {
    //     $bodyLower = strtolower($body);

    //     $hardBounceKeywords = [
    //         'user unknown',
    //         'invalid recipient',
    //         'no such user',
    //         'user not found',
    //         'mailbox unavailable',
    //         'account disabled',
    //         'does not exist',
    //         'permanent failure',
    //         'recipient rejected',
    //         'invalid address'
    //     ];

    //     $softBounceKeywords = [
    //         'mailbox full',
    //         'quota exceeded',
    //         'temporarily unavailable',
    //         'try again later',
    //         'deferred',
    //         'temporary failure',
    //         'service unavailable',
    //         'connection timeout'
    //     ];

    //     foreach ($hardBounceKeywords as $keyword) {
    //         if (str_contains($bodyLower, $keyword)) {
    //             return 'hard';
    //         }
    //     }

    //     foreach ($softBounceKeywords as $keyword) {
    //         if (str_contains($bodyLower, $keyword)) {
    //             return 'soft';
    //         }
    //     }

    //     return 'unknown';
    // }

    // private function trackReferences($message): void
    // {
    //     $references = $message->headers()->get('References');

    //     if (!$references) {
    //         return;
    //     }

    //     // Extract all message IDs from References header
    //     preg_match_all('/<([^>]+)>/', $references, $matches);
    //     $referencedIds = $matches[1] ?? [];

    //     foreach ($referencedIds as $refId) {
    //         // Find the email log for this reference
    //         $emailLog = EmailLog::where('message_id', $refId)->first();

    //         if ($emailLog) {
    //             Log::debug('Message reference tracked', [
    //                 'current_message_uid' => $message->uid(),
    //                 'references_message_id' => $refId,
    //                 'email_log_id' => $emailLog->id,
    //             ]);

    //             // You could store conversation threading information here
    //             // For example, create a conversations table to track email threads
    //         }
    //     }
    // }
}
