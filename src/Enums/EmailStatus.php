<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EmailStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case OPENED = 'opened';
    case CLICKED = 'clicked';
    case REPLIED = 'replied';
    case UNSUBSCRIBED = 'unsubscribed';
    case COMPLAINED = 'complained';
    case SOFT_BOUNCED = 'soft_bounced';
    case HARD_BOUNCED = 'hard_bounced';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Sent',
            self::OPENED => 'Opened',
            self::CLICKED => 'Clicked',
            self::REPLIED => 'Replied',
            self::UNSUBSCRIBED => 'Unsubscribed',
            self::COMPLAINED => 'Complaint',
            self::SOFT_BOUNCED => 'Soft Bounced',
            self::HARD_BOUNCED => 'Hard Bounced',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::SENT => 'primary',
            self::OPENED => 'success',
            self::CLICKED => 'success',
            self::REPLIED => 'success',
            self::UNSUBSCRIBED => 'warning',
            self::COMPLAINED => 'warning',
            self::SOFT_BOUNCED => 'danger',
            self::HARD_BOUNCED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-s-clock',
            self::SENT => 'heroicon-s-paper-airplane',
            self::OPENED => 'heroicon-s-eye',
            self::CLICKED => 'heroicon-s-cursor-arrow-rays',
            self::REPLIED => 'heroicon-s-chat-bubble-left-right',
            self::UNSUBSCRIBED => 'heroicon-s-user-minus',
            self::COMPLAINED => 'heroicon-s-shield-exclamation',
            self::SOFT_BOUNCED => 'heroicon-s-exclamation-triangle',
            self::HARD_BOUNCED => 'heroicon-s-x-circle',
        };
    }

    /**
     * Get the priority order for status determination
     * Lower numbers have higher priority
     */
    public function getPriority(): int
    {
        return match ($this) {
            self::HARD_BOUNCED => 1,
            self::SOFT_BOUNCED => 2,
            self::UNSUBSCRIBED => 3,
            self::COMPLAINED => 4,
            self::REPLIED => 5,
            self::CLICKED => 6,
            self::OPENED => 7,
            self::SENT => 8,
            self::PENDING => 9,
        };
    }

    /**
     * Determine status based on email log timestamps
     */
    public static function fromEmailLog($emailLog): self
    {
        // Check for bounces first (most critical)
        if ($emailLog->hard_bounced_at) {
            return self::HARD_BOUNCED;
        }

        if ($emailLog->soft_bounced_at) {
            return self::SOFT_BOUNCED;
        }

        // Check for unsubscribe
        if ($emailLog->unsubscribed_at) {
            return self::UNSUBSCRIBED;
        }

        // Check for complaints
        if ($emailLog->complained_at) {
            return self::COMPLAINED;
        }

        // Check for engagement (in order of value)
        if ($emailLog->replied_at) {
            return self::REPLIED;
        }

        if ($emailLog->last_clicked_at) {
            return self::CLICKED;
        }

        if ($emailLog->last_opened_at) {
            return self::OPENED;
        }

        // Check if sent
        if ($emailLog->sent_at) {
            return self::SENT;
        }

        // Default status
        return self::PENDING;
    }

    /**
     * Get all statuses ordered by priority
     */
    public static function orderedByPriority(): array
    {
        $cases = self::cases();
        usort($cases, fn ($a, $b) => $a->getPriority() <=> $b->getPriority());

        return $cases;
    }

    /**
     * Get successful engagement statuses
     */
    public static function getEngagementStatuses(): array
    {
        return [
            self::OPENED,
            self::CLICKED,
            self::REPLIED,
        ];
    }

    /**
     * Get problematic statuses
     */
    public static function getProblematicStatuses(): array
    {
        return [
            self::UNSUBSCRIBED,
            self::COMPLAINED,
            self::SOFT_BOUNCED,
            self::HARD_BOUNCED,
        ];
    }

    /**
     * Check if this status indicates successful delivery
     */
    public function isDelivered(): bool
    {
        return !in_array($this, [self::PENDING, self::SOFT_BOUNCED, self::HARD_BOUNCED]);
    }

    /**
     * Check if this status indicates engagement
     */
    public function isEngaged(): bool
    {
        return in_array($this, self::getEngagementStatuses());
    }

    /**
     * Check if this status indicates a problem
     */
    public function isProblematic(): bool
    {
        return in_array($this, self::getProblematicStatuses());
    }
}
