<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailEvent extends Model
{
    use SoftDeletes;

    protected $table = 'em_email_events';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function sentEmails(): MorphMany
    {
        return $this->morphMany(SentEmail::class, 'eventable');
    }

    public function emailVisits(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: EmailVisit::class,
            through: SentEmail::class,
            firstKey: 'eventable_id',
            secondKey: 'email_hash',
            localKey: 'id',
            secondLocalKey: 'hash',
        )->where('eventable_type', self::class);
    }

    // this is of no use as we are not using in  command
    public function emailHandlerClassName(): string
    {
        return str($this->slug)->studly() . 'EmailHandler';
    }

    public function resolveEmailHandler(): string
    {
        return 'App\\EmailManagement\\EmailHandlers\\EmailEvents\\' . $this->emailHandlerClassName();
    }
}
