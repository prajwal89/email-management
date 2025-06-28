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
}
