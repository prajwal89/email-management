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
        'email_campaign_id',
        'receivable_groups',
        'batch_id',
    ];

    public function casts(): array
    {
        return [
            'receivable_groups' => 'array',
        ];
    }

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
