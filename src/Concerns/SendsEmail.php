<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Concerns;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

trait SendsEmail
{
    public function configureEmailHeaders(Email $message): void
    {
        $headers = $message->getHeaders();
        $headers->addTextHeader('X-Eventable-Type', get_class($this->eventable));
        $headers->addTextHeader('X-Eventable-Id', (string) $this->eventable->getKey());
        $headers->addTextHeader('X-Receivable-Type', get_class($this->receivable));
        $headers->addTextHeader('X-Receivable-Id', (string) $this->receivable->getKey());
    }

    public static function sendSampleEmailTo(string $email)
    {
        return Mail::to($email)->send(static::sampleEmail());
    }

    /**
     * for filament panel previews
     */
    public static function renderEmailForPreview(): string
    {
        return static::sampleEmail()->render();
    }
}
