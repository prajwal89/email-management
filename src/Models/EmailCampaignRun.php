<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmailCampaignRun extends Model
{
    protected $table = 'em_email_campaign_runs';

    protected $fillable = [
        'batch_id',
        'email_campaign_slug',
        'receivable_groups',
    ];

    public function casts(): array
    {
        return [
            'receivable_groups' => 'array',
        ];
    }

    public function emailCampaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'slug', 'email_campaign_slug');
    }

    /**
     * Campaign execution handled by job batching
     */
    public function jobBatch(): HasOne
    {
        return $this->hasOne(JobBatch::class, 'id', 'batch_id');
    }
}
