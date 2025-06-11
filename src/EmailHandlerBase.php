<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\EmailContentModifiers;
use Prajwal89\EmailManagement\Services\EmailVariantSelector;
use Prajwal89\EmailManagement\Services\HeadersManager;
use ReflectionClass;
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

    /**
     * Message-ID header that we are setting manually
     */
    public string $messageId;

    public $finalEmail;

    /**
     * For resolving sendable
     */
    public static string $sendableType;

    /**
     * For resolving sendable
     */
    public static string $sendableSlug;

    /**
     * This sendable triggered the sending of email
     */
    protected EmailSendable $sendable;

    protected EmailReceivable $receivable;

    /**
     * Email variant choosen by email variant selector
     */
    protected EmailVariant $chosenEmailVariant;

    protected ?array $eventContext = null;

    /**
     * Extra context for why we are sending this email for current email event
     * as 1 email event can happen on single user multiple times
     */
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
     *
     * Always implement this logic properly as this will prevent
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
        $this->sendable = self::resolveSendable();

        $this->chosenEmailVariant = (new EmailVariantSelector($this->sendable))->choose();

        $this->messageId = HeadersManager::generateNewMessageId();

        // pass parent constructor args to the email class
        $this->finalEmail = new static::$mail(
            ...$this->parentConstructorArgs(),
            emailVariant: $this->chosenEmailVariant
        );

        $this->finalEmail->withSymfonyMessage([$this, 'configureSymfonyMessage']);

        $emailContentModifiers = new EmailContentModifiers(
            $this->finalEmail,
            $this->messageId
        );

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

    public static function resolveSendable(): EmailSendable
    {
        return (new static::$sendableType)->where('slug', static::$sendableSlug)->first();
    }

    public function configureSymfonyMessage(Email $message)
    {
        $headersManager = new HeadersManager($message);

        $headersManager->configureEmailHeaders(
            sendable: $this->sendable,
            receivable: $this->receivable,
            messageId: $this->messageId,
            eventContext: $this->eventContext,
            chosenEmailVariant: $this->chosenEmailVariant,
        );

        return $message;
    }

    /**
     * Modify the underlying mailable class
     */
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
     */
    public static function buildSampleEmail(): Mailable
    {
        $sampleEmailData = static::sampleEmailData();

        // ! resolve this automatically
        // make sendable slug static so we can access it here
        if (!isset($sampleEmailData['emailVariant'])) {
            $sampleEmailData['emailVariant'] = self::resolveSendable()->defaultEmailVariant;
        }

        // dd($sampleEmailData);

        $sampleBuildEmail = new static::$mail(
            ...$sampleEmailData
        );

        $messageId = HeadersManager::generateNewMessageId();

        $emailContentModifiers = new EmailContentModifiers($sampleBuildEmail, $messageId);

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
        //         sendable: $sampleEmailData['receivable'],
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
            ->where('sendable_id', $this->sendable->id)
            ->where('sendable_type', get_class($this->sendable))
            ->when($context !== null, function ($query) use ($context) {
                return $query->whereJsonContains('context', $context);
            })
            ->exists();
    }

    /**
     * When users gives the parameter to the email handler class
     * it will be automatically passed to the email class
     */
    public function parentConstructorArgs(): array
    {
        $reflection = new ReflectionClass($this);
        $constructor = $reflection->getConstructor();

        $args = [];

        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();

                // Only include if it's promoted to a property
                if ($param->isPromoted()) {
                    $args[$name] = $this->{$name};
                }
            }
        }

        return $args;
    }
}
