<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;

class FollowUp extends Model
{
    protected $table = 'em_follow_ups';

    // Use a single primary key for Filament compatibility
    protected $primaryKey = 'followup_email_event_slug';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    // Store composite key components for internal use
    protected $compositeKey = ['followupable_slug', 'followupable_type', 'followup_email_event_slug'];

    // For Filament compatibility
    public function getKeyName()
    {
        return 'followup_email_event_slug';
    }

    // For Filament compatibility
    public function getRouteKeyName()
    {
        return 'followup_email_event_slug';
    }

    // For internal composite key lookups
    protected function setKeysForSaveQuery($query)
    {
        return $query->where([
            'followupable_slug' => $this->getAttribute('followupable_slug'),
            'followupable_type' => $this->getAttribute('followupable_type'),
            'followup_email_event_slug' => $this->getAttribute('followup_email_event_slug'),
        ]);
    }

    protected $fillable = [
        'followup_email_event_slug',
        'followupable_type',
        'followupable_slug',
        'is_enabled',
        'wait_for_days',
    ];

    /**
     * This followup email will be sent
     */
    public function followupEmailEvent()
    {
        return $this->belongsTo(EmailEvent::class, 'followup_email_event_slug');
    }

    /**
     * Follow up email will be sent to this followupable
     */
    public function followupable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function getMigrationFilePath(
        EmailEvent $followupAbleEvent,
        EmailSendable $followupAble,
        string $type = 'seed'
    ) {
        $microtime = microtime(true);

        $datetime = DateTime::createFromFormat('U.u', (string) $microtime);

        $dateTime = $datetime->format('Y_m_d_Hisv'); // 'v' = milliseconds

        $filename = "{$dateTime}_{$type}_followup_{$followupAbleEvent->slug}_sendable_{$followupAble->slug}.php";

        return config('email-management.migrations_dir') . '/' . $filename;
    }
}
