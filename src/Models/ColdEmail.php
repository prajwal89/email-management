<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Prajwal89\EmailManagement\Traits\HasEmailLogs;

class ColdEmail extends Model implements EmailReceivable
{
    use HasEmailLogs, HasFactory;

    protected $table = 'em_cold_emails';

    protected $primaryKey = 'email';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'email',
        'collection_reason',
        'collected_from',
        'unsubscribed_at',
        'data', // extra context
    ];

    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'datetime',
            'data' => 'array',
        ];
    }

    public function getName(): string
    {
        return str($this->email)->before('@')->toString();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    #[Scope]
    public function subscribedToEmails(Builder $query): Builder
    {
        return $query->whereNull('unsubscribed_at');
    }

    public function isSubscribedToEmails(): bool
    {
        return $this->unsubscribed_at === null;
    }

    public function unsubscribeFromEmails(): bool
    {
        return $this->update(['unsubscribed_at' => now()]);
    }
}
