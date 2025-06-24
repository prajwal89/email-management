<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FollowUp extends Model
{
    protected $fillable = [
        'followup_email_event_id',
        'followupable_id',
        'followupable_type',
        'is_enabled',
        'wait_for_hours',
    ];

    /**
     * This followup email will be sent
     */
    public function followupEmailEvent()
    {
        return $this->hasOne(EmailEvent::class, 'followup_email_event_id');
    }

    /**
     * Follow up email will be sent to this followupable
     */
    public function followupable(): MorphTo
    {
        return $this->morphTo();
    }
}
