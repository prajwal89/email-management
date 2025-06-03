<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use App\Models\JobBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailCampaign extends Model
{
    use SoftDeletes;

    protected $table = 'em_email_campaigns';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'receivable_groups',
        'started_on',
        'ended_on',
        'batch_id',
    ];

    protected function casts(): array
    {
        return [
            'receivable_groups' => 'array',
            'started_on' => 'datetime',
            'ended_on' => 'datetime',
        ];
    }

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'eventable');
    }

    public function emailVisits(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: EmailVisit::class,
            through: EmailLog::class,
            firstKey: 'eventable_id',
            secondKey: 'message_id',
            localKey: 'id',
            secondLocalKey: 'message_id',
        )->where('eventable_type', self::class);
    }

    /**
     * as campaign execution handled by job batching
     */
    public function jobBatch(): HasOne
    {
        return $this->hasOne(JobBatch::class, 'id', 'batch_id');
    }

    // this is of no use as we are not using in  command
    public function emailHandlerClassName(): string
    {
        return str($this->slug)->studly() . 'EmailHandler';
    }

    public function resolveEmailHandler(): string
    {
        return 'Modules\\EmailManagement\\MailHandlers\\EmailCampaigns\\' . $this->emailHandlerClassName();
    }
}
