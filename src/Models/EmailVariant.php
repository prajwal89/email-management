<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Prajwal89\EmailManagement\Enums\EmailContentType;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;

class EmailVariant extends Model
{
    use HasFactory;

    protected $table = 'em_email_variants';

    protected $primaryKey = 'slug';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'content_type',
        'sendable_slug',
        'sendable_type',
        'is_paused',
        'is_winner',
        'exposure_percentage',
    ];

    protected $attributes = [
        'name' => 'Default',
        'slug' => 'default',
        'exposure_percentage' => 50,
        'content_type' => 'markdown',
    ];

    public function casts(): array
    {
        return [
            'is_paused' => 'boolean',
            'is_winner' => 'boolean',
            'exposure_percentage' => 'integer',
            'content_type' => EmailContentType::class,
        ];
    }

    /**
     * Get the parent sendable model (it could be any model).
     */
    public function sendable(): MorphTo
    {
        // Adjust to use 'sendable_slug' as the foreign key name instead of the default 'sendable_id'.
        return $this->morphTo(id: 'sendable_slug');
    }

    /**
     * Get the email logs associated with this variant.
     */
    public function emailLogs()
    {
        // Adjust to use the correct foreign key 'email_variant_slug' and local key 'slug'.
        return $this->hasMany(EmailLog::class, 'email_variant_slug', 'slug');
    }

    /**
     * Get all the email visits for the email variant.
     */
    public function emailVisits()
    {
        return $this->hasManyThrough(
            EmailVisit::class,
            EmailLog::class,
            'email_variant_slug', // Foreign key on em_email_logs table
            'message_id',       // Foreign key on em_email_visits table
            'slug',             // Local key on em_email_variants table (was 'id')
            'message_id'        // Local key on em_email_logs table
        );
    }

    public function getDefaultAttributes(): array
    {
        return $this->attributes;
    }

    public function emailViewName(): string
    {
        if ($this->slug === 'default') {
            return $this->sendable->slug . '-email';
        }

        return $this->sendable->slug . '-' . $this->slug . '-email';
    }

    public function getFullViewName(): string
    {
        $folderName = match (get_class($this->sendable)) {
            EmailEvent::class => 'email-events',
            EmailCampaign::class => 'email-campaigns',
        };

        return "email-management::emails.$folderName.{$this->emailViewName()}";
    }

    public function resolveEmailHandler(): string
    {
        return $this->sendable->resolveEmailHandler();
    }

    public static function getMigrationFilePath(
        string $sendableType,
        string $sendableSlug,
        string $variantSlug,
        string $type = 'seed'
    ): string {
        $sendableType = str($sendableType)->afterLast('\\')->lower();

        $microtime = microtime(true);

        // add extra 10 ms for avoiding same migration file as sendable migration file
        $datetime = DateTime::createFromFormat('U.u', (string) ($microtime + 10));

        $dateTime = $datetime->format('Y_m_d_Hisv'); // 'v' = milliseconds

        $filename = "{$dateTime}_{$type}_{$sendableType}_{$sendableSlug}_{$variantSlug}.php";

        return config('email-management.migrations_dir') . '/' . $filename;
    }

    public static function getEmailViewFileName(
        EmailSendable|string $sendable,
        string $variantSlug,
        ?string $sendableSlug = null
    ): string {
        if ($sendable instanceof Model) {
            if ($variantSlug !== 'default') {
                return $sendable->slug . '-' . $variantSlug . '-email.blade.php';
            }

            return $sendable->slug . '-email.blade.php';
        }

        if ($sendableSlug && $variantSlug !== 'default') {
            return $sendableSlug . '-' . $variantSlug . '-email.blade.php';
        }

        return $sendableSlug . '-email.blade.php';
    }

    public static function getEmailViewFilePath(
        EmailSendable|string $sendable,
        string $variantSlug,
        ?string $sendableSlug = null
    ): string {
        $emailViewFileName = self::getEmailViewFileName($sendable, $variantSlug, $sendableSlug);

        $folderName = match (is_string($sendable) ? $sendable : get_class($sendable)) {
            EmailEvent::class => 'email-events',
            EmailCampaign::class => 'email-campaigns',
        };

        $mailPath = config('email-management.view_dir') . "/emails/$folderName";

        return $mailPath . "/{$emailViewFileName}";
    }
}
