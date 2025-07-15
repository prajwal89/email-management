<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Dtos;

/**
 * A Data Transfer Object to hold the structured results of a parsed bounce email.
 */
final readonly class BounceDataDto
{
    public function __construct(
        public ?string $recipient,
        public ?string $statusCode,
        public ?string $action,
        public ?string $reason,
        public ?string $humanReadablePart,
        public ?array $originalMessage,
    ) {}

    public function isHardBounced(): bool
    {
        // Hard bounces usually have status codes starting with 5.x.x
        return $this->statusCode !== null && str_starts_with($this->statusCode, '5');
    }

    public function isSoftBounced(): bool
    {
        // Soft bounces usually have status codes starting with 4.x.x
        return $this->statusCode !== null && str_starts_with($this->statusCode, '4');
    }
}
