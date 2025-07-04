<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;
use Prajwal89\EmailManagement\Traits\HasEmailLogs;

class EmailEvent extends Model implements EmailSendable
{
    use HasEmailLogs;

    protected $table = 'em_email_events';

    protected $primaryKey = 'slug';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_enabled',
        'is_followup_email',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_followup_email' => 'boolean',
        ];
    }

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'sendable', 'sendable_type', 'sendable_slug', 'slug');
    }

    /**
     * Successfully sent emails
     */
    public function sentEmails(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'sendable', 'sendable_type', 'sendable_slug', 'slug')->sent();
    }

    public function emailVariants(): MorphMany
    {
        return $this->morphMany(
            EmailVariant::class,
            'sendable',
            'sendable_type',
            'sendable_slug',
            'slug'
        );
    }

    public function defaultEmailVariant(): MorphOne
    {
        return $this->morphOne(
            EmailVariant::class,
            'sendable',
            'sendable_type',
            'sendable_slug',
            'slug'
        )->where('slug', 'default');
    }

    public function emailVisits(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: EmailVisit::class,
            through: EmailLog::class,
            firstKey: 'sendable_slug',
            secondKey: 'message_id',
            localKey: 'slug',
            secondLocalKey: 'message_id',
        )->where('sendable_type', self::class);
    }

    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'followupable', 'followupable_type', 'followupable_slug', 'slug');
    }

    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function totalActiveVariants(): int
    {
        $this->load('emailVariants');

        if ($this->emailVariants->count() === 1) {
            return 1;
        }

        if ($this->emailVariants->where('is_winner', 1)->first()) {
            return 1;
        }

        return $this->emailVariants->where('is_paused', 0)->count();
    }

    public function emailHandlerClassName(): string
    {
        return str($this->slug)->studly() . 'EmailHandler';
    }

    public function resolveEmailHandler(): string
    {
        return 'App\\EmailManagement\\EmailHandlers\\EmailEvents\\' . $this->emailHandlerClassName();
    }

    public static function getMigrationFilePath(string $slug, string $type = 'seed')
    {
        $microtime = microtime(true);

        $datetime = DateTime::createFromFormat('U.u', (string) $microtime);

        $dateTime = $datetime->format('Y_m_d_Hisv'); // 'v' = milliseconds

        $filename = "{$dateTime}_{$type}_emailevent_{$slug}.php";

        return config('email-management.migrations_dir') . '/' . $filename;
    }

    public static function getEmailHandlerFilePath(string $slug)
    {
        $emailHandlerClassName = str($slug)->studly() . 'EmailHandler';

        $handlerPath = config('email-management.email_handlers_dir') . '/EmailEvents';

        return $handlerPath . "/{$emailHandlerClassName}.php";
    }

    public static function getMailableClassName(string $slug)
    {
        return str($slug)->studly() . 'Email';
    }

    public static function getMailableClassPath(string $slug)
    {
        $emailClassName = self::getMailableClassName($slug);

        $mailPath = config('email-management.mail_classes_path') . '/EmailEvents';

        return $mailPath . "/{$emailClassName}.php";
    }
}
