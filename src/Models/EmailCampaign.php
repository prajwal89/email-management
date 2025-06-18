<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use App\Models\JobBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;

class EmailCampaign extends Model implements EmailSendable
{
    // use SoftDeletes;

    protected $table = 'em_email_campaigns';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_enabled',
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
            'is_enabled' => 'boolean',
        ];
    }

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'sendable');
    }

    /**
     * Successfully sent emails
     */
    public function sentEmails(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'sendable')->sent();
    }

    public function emailVisits(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: EmailVisit::class,
            through: EmailLog::class,
            firstKey: 'sendable_id',
            secondKey: 'message_id',
            localKey: 'id',
            secondLocalKey: 'message_id',
        )->where('sendable_type', self::class);
    }

    public function emailVariants(): MorphMany
    {
        return $this->morphMany(EmailVariant::class, 'sendable');
    }

    public function defaultEmailVariant(): MorphOne
    {
        return $this->morphOne(EmailVariant::class, 'sendable')->where('slug', 'default');
    }

    public function isEnabled(): bool
    {
        return $this->is_enabled;
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
        return 'App\\EmailManagement\\EmailHandlers\\EmailCampaigns\\' . $this->emailHandlerClassName();
    }

    public static function getEmailHandlerFilePath(string $slug)
    {
        $emailHandlerClassName = str($slug)->studly() . 'EmailHandler';

        $handlerPath = config('email-management.email_handlers_dir') . '/EmailCampaigns';

        return $handlerPath . "/{$emailHandlerClassName}.php";
    }

    public static function getMailableClassName(string $slug)
    {
        return str($slug)->studly() . 'Email';
    }

    public static function getMailableClassPath(string $slug)
    {
        $emailClassName = self::getMailableClassName($slug);

        $mailPath = config('email-management.mail_classes_path') . '/EmailCampaigns';

        return $mailPath . "/{$emailClassName}.php";
    }

    public static function getSeederFileClassName(string $slug, string $type = 'create')
    {
        if ($type === 'create') {
            $seederClassName = str($slug)->studly() . 'Seeder';
        }

        if ($type === 'delete') {
            $seederClassName = str($slug)->studly() . 'Seeder';
        }

        return $seederClassName;
    }

    public static function getSeederFilePath(string $slug, string $type = 'create')
    {
        $seederClassName = self::getSeederFileClassName($slug, $type);

        $seederFileName = "$seederClassName.php";

        $seederPath = config('email-management.seeders_dir') . '/EmailCampaigns';

        return $seederPath . "/{$seederFileName}";
    }
}
