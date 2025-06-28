<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement;

use Prajwal89\EmailManagement\Dtos\BounceDataDto;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\IMessage;
use ZBateson\MailMimeParser\MailMimeParser; // Corrected: This is the main interface for a parsed message.

/**
 * Parses raw email content to identify and extract structured data from bounce messages.
 *
 * This class can handle two common bounce formats:
 * 1. Standard RFC 3464 'multipart/report' bounces.
 * 2. Simpler 'text/plain' bounces, like those from Google, and infers the status.
 */
class BounceParser
{
    private MailMimeParser $parser;

    public function __construct()
    {
        $this->parser = new MailMimeParser;
    }

    /**
     * The main entry point for parsing. It checks if an email is a bounce and, if so,
     * routes it to the correct parsing logic.
     *
     * @param  string  $rawEmail  The raw source of the email.
     * @return BounceDataDto|null A BounceDataDto object if it's a bounce, otherwise null.
     */
    public function parse(string $rawEmail): ?BounceDataDto
    {
        $message = $this->parser->parse($rawEmail, false);

        if (!$this->isBounceEmail($message)) {
            return null;
        }

        $mainContentType = $message->getHeaderValue(HeaderConsts::CONTENT_TYPE, '');

        if (str_starts_with($mainContentType, 'multipart/report')) {
            return $this->parseStandardBounce($message);
        }

        return $this->parseSimpleBounce($message);
    }

    /**
     * Parses a standard RFC 3464 'multipart/report' bounce message.
     */
    private function parseStandardBounce(IMessage $message): BounceDataDto
    {
        $data = [
            'recipient' => null,
            'statusCode' => null,
            'action' => null,
            'reason' => null,
            'humanReadablePart' => null,
            'originalMessage' => null,
        ];

        $partCount = $message->getPartCount();
        for ($i = 0; $i < $partCount; $i++) {
            $part = $message->getPart($i);
            $partContentType = $part->getHeaderValue(HeaderConsts::CONTENT_TYPE, '');

            if (str_starts_with($partContentType, 'text/plain')) {
                $data['humanReadablePart'] = $part->getContent();
            } elseif (str_starts_with($partContentType, 'message/delivery-status')) {
                $reportContent = $part->getContent();
                if (preg_match('/Status: ([\d\.]+)/', $reportContent, $m)) {
                    $data['statusCode'] = $m[1];
                }
                if (preg_match('/Action: (.+)/', $reportContent, $m)) {
                    $data['action'] = trim($m[1]);
                }
                if (preg_match('/Final-Recipient: rfc822;(.+)/', $reportContent, $m)) {
                    $data['recipient'] = trim($m[1]);
                }
                if (preg_match('/Diagnostic-Code: smtp;(.+)/s', $reportContent, $m)) {
                    $data['reason'] = trim(preg_replace('/\s+/', ' ', $m[1]));
                }
            } elseif (str_starts_with($partContentType, 'message/rfc822')) {
                $data['originalMessage'] = $this->parseOriginalMessagePart($part->getContent());
            }
        }

        return new BounceDataDto(...$data);
    }

    /**
     * Parses a non-standard, simple bounce message (e.g., text/plain from Google).
     */
    private function parseSimpleBounce(IMessage $message): BounceDataDto
    {
        $recipient = $message->getHeaderValue('X-Failed-Recipients');
        $fullBody = $message->getTextContent();
        $subject = $message->getHeaderValue('subject');

        $bodyParts = explode('----- Original message -----', $fullBody, 2);
        $reasonText = trim($bodyParts[0]);

        $statusCode = $this->inferStatusCodeFromText($subject, $reasonText);
        $action = str_starts_with($statusCode, '5') ? 'failed' : 'delayed';

        $originalMessage = isset($bodyParts[1]) && !empty(trim($bodyParts[1]))
            ? $this->parseOriginalMessagePart(trim($bodyParts[1]))
            : null;

        return new BounceDataDto(
            recipient: $recipient,
            statusCode: $statusCode,
            action: $action,
            reason: $reasonText,
            humanReadablePart: $reasonText,
            originalMessage: $originalMessage
        );
    }

    /**
     * A helper to parse the nested original message part into a structured array.
     */
    private function parseOriginalMessagePart(string $rawOriginalEmail): ?array
    {
        $messageObject = $this->parser->parse($rawOriginalEmail, false);
        if (!$messageObject) {
            return null;
        }

        return [
            'to' => $messageObject->getHeaderValue(HeaderConsts::TO),
            'from' => $messageObject->getHeaderValue(HeaderConsts::FROM),
            'subject' => $messageObject->getHeaderValue(HeaderConsts::SUBJECT),
            'message_id' => $messageObject->getHeaderValue(HeaderConsts::MESSAGE_ID),
        ];
    }

    /**
     * Checks if an email message is likely a bounce report.
     * This acts as a gatekeeper to the main parsing logic.
     */
    private function isBounceEmail(IMessage $message): bool
    {
        // Check 1: Strongest indicator from automated systems.
        if (str_contains(strtolower($message->getHeaderValue('auto-submitted', '')), 'auto-replied')) {
            return true;
        }

        // Check 2: Sender is often a standard system name.
        if (preg_match('/mailer-daemon|postmaster|mail delivery subsystem/i', $message->getHeaderValue('from', ''))) {
            return true;
        }

        // Check 3: The standard Content-Type for bounces.
        if (str_contains($message->getHeaderValue('content-type', ''), 'delivery-status')) {
            return true;
        }

        // Check 4: Subject lines are a good, but less reliable, clue.
        if (preg_match('/undelivered|delivery status notification|failure notice|mail delivery failed/i', $message->getHeaderValue('subject', ''))) {
            return true;
        }

        return false;
    }

    /**
     * Infers a standard SMTP status code by searching for keywords in bounce text.
     */
    private function inferStatusCodeFromText(?string $subject, ?string $body): string
    {
        $text = strtolower($subject . ' ' . $body);
        $rules = [
            '5.7.1' => ['permission to post', 'not authorized', 'policy reasons', 'blocked for spam'],
            '5.1.1' => ['does not exist', 'no such user', 'user unknown', 'recipient not found'],
            '5.2.2' => ['mailbox full', 'quota exceeded'],
            '5.1.2' => ['domain not found', 'host not found'],
            '4.0.0' => ['delivery temporarily suspended', 'try again later'],
        ];

        foreach ($rules as $code => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $code;
                }
            }
        }

        // Default to a generic permanent failure if "failure" or "failed" is mentioned
        if (str_contains($text, 'failure') || str_contains($text, 'failed') || str_contains($text, 'undeliverable')) {
            return '5.0.0';
        }

        // Fallback for cases where no keywords match, but it's still identified as a bounce.
        return '5.0.0';
    }
}
