<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\EmailContentModifiers;
use Prajwal89\EmailManagement\Services\HeadersManager;
use Symfony\Component\Mime\Email;

/**
 * These classes are responsible for validating emails
 * and sending
 */
abstract class EmailHandlerBase
{
    /**
     * This mail will be sent
     */
    public static $mail = Mailable::class;

    public $finalEmail;

    /**
     * this event triggered the sending of email
     */
    protected EmailEvent|EmailCampaign $eventable;

    protected EmailReceivable $receivable;

    protected ?array $eventContext = null;

    /**
     * extra context for why we are sending this email for current email event
     * as 1 email event can happen on single user multiple times
     */
    // todo we can add schema validation rules in subclass
    // so we can verify here
    public function setContext(array $eventContext): self
    {
        /**
         * validate schema of context as it is crucial for checking qualifiesForSending()
         */
        $this->eventContext = $eventContext;

        return $this;
    }

    /**
     * Determines if the email should be sent.
     * ! always implement this logic properly as this will prevent
     * email spamming
     */
    protected function qualifiesForSending(): bool
    {
        // Override in subclasses for custom logic
        if (!$this->receivable->isSubscribedToEmails()) {
            return false;
        }

        // todo check for context schema
        // todo check if how many times this event on receivable are allowed

        return true;
    }

    /**
     * Sends the email if it qualifies.
     */
    public function send(): void
    {
        if (!$this->qualifiesForSending()) {
            return;
        }

        if (!$this->finalEmail) {
            $this->buildEmail();
        }

        Mail::to($this->receivable->getEmail())->send($this->finalEmail);
    }

    public function buildEmail()
    {
        $this->finalEmail = new static::$mail($this->receivable);

        $this->finalEmail->withSymfonyMessage([$this, 'configureSymfonyMessage']);

        $emailContentModifiers = new EmailContentModifiers($this->finalEmail);

        if (config('email-management.track_visits')) {
            $emailContentModifiers->injectTrackingUrls();
        }

        if (config('email-management.track_opens')) {
            $emailContentModifiers->injectTrackingPixel();
        }

        if (config('email-management.inject_unsubscribe_link')) {
            $emailContentModifiers->injectUnsubscribeLink();
        }

        return $this;
    }

    public function configureSymfonyMessage(Email $message)
    {
        $headersManager = new HeadersManager($message);

        $headersManager->configureEmailHeaders(
            eventable: $this->eventable,
            receivable: $this->receivable,
            eventContext: $this->eventContext,
        );

        return $message;
    }

    public function modifyEmailUsing(callable $callback): self
    {
        if (!$this->finalEmail) {
            $this->buildEmail();
        }

        $callback($this->finalEmail);

        return $this;
    }

    /**
     * Builds the email instance for preview.
     * Extend this if required
     * todo: this should have all modifications as well
     */
    public static function buildSampleEmail(): Mailable
    {
        $sampleEmailData = static::sampleEmailData();

        $sampleBuildEmail = new static::$mail(...$sampleEmailData);

        $emailContentModifiers = new EmailContentModifiers($sampleBuildEmail);

        if (config('email-management.track_visits')) {
            $emailContentModifiers->injectTrackingUrls();
        }

        if (config('email-management.track_opens')) {
            $emailContentModifiers->injectTrackingPixel();
        }

        if (config('email-management.inject_unsubscribe_link')) {
            $emailContentModifiers->injectUnsubscribeLink();
        }

        // $sampleBuildEmail->withSymfonyMessage(function ($message) use ($sampleEmailData) {
        //     $headersManager = new HeadersManager($message);

        //     $headersManager->configureEmailHeaders(
        //         eventable: $sampleEmailData['receivable'],
        //         receivable: User::query()->inRandomOrder()->first(),
        //         eventContext: [],
        //     );
        // });

        // $symfonyEmail = new Email();

        // foreach ($sampleBuildEmail->callbacks ?? [] as $callback) {
        //     $callback($symfonyEmail);
        // }

        return $sampleBuildEmail;
    }

    public static function sampleEmailData()
    {
        return [
            'receivable' => User::query()->inRandomOrder()->first(),
        ];
    }

    /**
     * Renders a preview of the email.
     */
    public static function renderEmailForPreview(): string
    {
        return static::buildSampleEmail()->render();
    }

    /**
     * Sends a sample email to a specific address.
     */
    public static function sendSampleEmailTo(string $email): void
    {
        Mail::to($email)->send(static::buildSampleEmail());
    }

    /**
     * Useful for checking email events that will happen
     * once per user
     * ! downside is this will not work if we prune records from sent emails table
     * Hint: we can reduce sent_emails table by removing large column (i.e. email_content)
     */
    public function wasEmailAlreadySentOnce(?array $context = null): bool
    {
        return EmailLog::query()
            ->where('receivable_id', $this->receivable->id)
            ->where('receivable_type', get_class($this->receivable))
            ->where('eventable_id', $this->eventable->id)
            ->where('eventable_type', get_class($this->eventable))
            ->when($context !== null, function ($query) use ($context) {
                return $query->whereJsonContains('context', $context);
            })
            ->exists();
    }
}
