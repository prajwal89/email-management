<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;

class EmailEvent extends Model implements EmailSendable
{
    // use SoftDeletes;

    protected $table = 'em_email_events';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'sendable');
    }

    /**
     * Successfully sent emails
     */
    public function sentEmails(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'sendable')->sent();
    }

    public function emailVariants(): MorphMany
    {
        return $this->morphMany(EmailVariant::class, 'sendable');
    }

    public function defaultEmailVariant(): MorphOne
    {
        return $this->morphOne(EmailVariant::class, 'sendable')->where('slug', 'default');
    }

    public function emailVisits(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: EmailVisit::class,
            through: EmailLog::class,
            firstKey: 'sendable_id',
            secondKey: 'message_id',
            localKey: 'id',
            secondLocalKey: 'message_id',
        )->where('sendable_type', self::class);
    }

    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    // this is of no use as we are not using in command
    public function emailHandlerClassName(): string
    {
        return str($this->slug)->studly() . 'EmailHandler';
    }

    public function resolveEmailHandler(): string
    {
        return 'App\\EmailManagement\\EmailHandlers\\EmailEvents\\' . $this->emailHandlerClassName();
    }
}
