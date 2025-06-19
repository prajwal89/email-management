<?php

namespace Prajwal89\EmailManagement\Models;

use App\Models\JobBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmailCampaignRun extends Model
{
    protected $table = 'em_email_campaign_runs';

    protected $fillable = [
        'email_campaign_id',
        'receivable_groups',
        'batch_id',
        'started_on',
        'ended_on',
    ];

    protected $casts = [
        'receivable_groups' => 'array',
        'started_on' => 'datetime',
        'ended_on' => 'datetime',
    ];

    public function emailCampaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class);
    }

    /**
     * Campaign execution handled by job batching
     */
    public function jobBatch(): HasOne
    {
        return $this->hasOne(JobBatch::class, 'id', 'batch_id');
    }
}
