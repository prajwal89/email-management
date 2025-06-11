<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Prajwal89\EmailManagement\Interfaces\EmailSendable;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\EmailVariant;

class EmailVariantSelector
{
    public function __construct(public EmailSendable $sendable) {}

    public function choose(): EmailVariant
    {
        // Load relations if not already loaded
        $this->sendable->loadMissing([
            'emailVariants.sendable',
            'defaultEmailVariant.sendable'
        ]);

        $variants = $this->sendable->emailVariants->where('is_paused', false);

        // 1. Always return the winner variant if one exists
        $winner = $variants->firstWhere('is_winner', true);
        if ($winner) {
            return $winner;
        }

        // 2. If there's only 1 variant, use default
        if ($variants->count() <= 1) {
            return $this->sendable->defaultEmailVariant;
        }

        // 3. Weighted random fallback
        $totalExposure = $variants->sum('exposure_percentage');
        if ($totalExposure <= 0) {
            return $this->sendable->defaultEmailVariant;
        }

        $rand = random_int(1, $totalExposure);
        $cumulative = 0;

        foreach ($variants as $variant) {
            $cumulative += $variant->exposure_percentage;
            if ($rand <= $cumulative) {
                return $variant;
            }
        }

        return $this->sendable->defaultEmailVariant;
    }
}
