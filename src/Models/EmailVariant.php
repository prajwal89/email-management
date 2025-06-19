<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Prajwal89\EmailManagement\Enums\EmailContentType;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;

class EmailVariant extends Model
{
    use HasFactory;

    protected $table = 'em_email_variants';

    protected $fillable = [
        'name',
        'slug',
        'content_type',
        'sendable_id',
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
        return $this->morphTo();
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    public function emailVisits()
    {
        return $this->hasManyThrough(
            EmailVisit::class,
            EmailLog::class,
            'email_variant_id', // Foreign key on em_email_logs table
            'message_id',       // Foreign key on em_email_visits table
            'id',               // Local key on em_email_variants table
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

    public static function getEmailViewFileName(
        EmailSendable|string $sendable,
        string $variantSlug,
        ?string $sendableSlug = null
    ) {
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
    ) {
        $emailViewFileName = self::getEmailViewFileName($sendable, $variantSlug, $sendableSlug);

        $folderName = match (is_string($sendable) ? $sendable : get_class($sendable)) {
            EmailEvent::class => 'email-events',
            EmailCampaign::class => 'email-campaigns',
        };

        $mailPath = config('email-management.view_dir') . "/emails/$folderName";

        return $mailPath . "/{$emailViewFileName}";
    }
}
