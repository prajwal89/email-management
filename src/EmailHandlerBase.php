<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Prajwal89\EmailManagement\Models\SentEmail;

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

    /**
     * this event triggered the sending of email
     */
    protected Model $eventable;

    protected ?array $eventContext = null;

    /**
     * accept all parameter required for building email
     * in subclass with context
     */
    public function __construct(public Model $receivable)
    {
        //
    }

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
    public function qualifiesForSending(): bool
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
    public function sendEmail(): void
    {
        if (!$this->qualifiesForSending()) {
            return;
        }

        Mail::to($this->receivable->getEmail())
            ->send($this->buildEmail());
    }

    /**
     * Builds the email instance.
     * Extend this if required
     */
    public function buildEmail(): Mailable
    {
        return (new static::$mail($this->receivable))
            ->withSymfonyMessage([$this, 'configureEmailHeaders']);
    }

    /**
     * Builds the email instance for preview.
     * Extend this if required
     */
    public static function buildSampleEmail(): Mailable
    {
        return new static::$mail(User::inRandomOrder()->first());
    }

    /**
     * Configures email headers dynamically.
     */
    public function configureEmailHeaders($message): void
    {
        $headers = $message->getHeaders();
        $headers->addTextHeader('X-Eventable-Type', (string) get_class($this->eventable));
        $headers->addTextHeader('X-Eventable-Id', (string) $this->eventable->getKey());
        $headers->addTextHeader('X-Receivable-Type', (string) get_class($this->receivable));
        $headers->addTextHeader('X-Receivable-Id', (string) $this->receivable->getKey());

        // todo add context json so we can record in sent_emails
        if ($this->eventContext !== null) {
            $headers->addTextHeader('X-Event-Context', json_encode($this->eventContext));
        }
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
     * useful for checking email events that will happen
     * once per user
     * ! downside is this will not work if we prune records from sent emails table
     * * hint we can reduce sent_emails table by removing large column (i.e. email_content)
     */
    public function wasEmailAlreadySentOnce(?array $context = null): bool
    {
        return SentEmail::query()
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
