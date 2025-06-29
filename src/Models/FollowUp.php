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

    protected $fillable = [
        'followup_email_event_id',
        'followupable_id',
        'followupable_type',
        'is_enabled',
        'wait_for_days',
    ];

    /**
     * This followup email will be sent
     */
    public function followupEmailEvent()
    {
        return $this->belongsTo(EmailEvent::class, 'followup_email_event_id');
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
