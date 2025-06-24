<?php

declare(strict_types=1);

return [
    'receivable_groups_path' => app_path('/EmailManagement/ReceivableGroups'),
    'mail_classes_path' => app_path('/EmailManagement/Emails'),
    'email_handlers_dir' => app_path('/EmailManagement/EmailHandlers'),
    'view_dir' => resource_path('/views/email-management'),
    'migrations_dir' => database_path('/migrations/email-management'),

    /**
     * The email address where the mail server should report bounced emails.
     * This mailbox should be monitored for bounce notifications.
     */
    'return_path' => 'bounces@example.com',

    /**
     * The email address where user replies should be directed.
     */
    'reply_to' => 'replyto@example.com',

    'mailer' => 'smtp',

    'max_delay_for_followup_email' => 21,

    /**
     * We use directorytree/imapengine-laravel for monitoring mailboxes.
     * You must configure the mailbox in the config/imap.php file and
     * provide the name of the mailbox here.
     * 
     * To use the default mailbox, leave this value as 'default'.
     * 
     * Docs: https://imapengine.com/docs/laravel/installation
     */
    'mailbox' => 'default',

    /**
     * Record visits originating from the email (e.g. link clicks).
     */
    'track_visits' => true,

    /**
     * Record when a user opens the email.
     */
    'track_opens' => true,

    /**
     * Automatically append an unsubscribe link to the email footer.
     */
    'inject_unsubscribe_link' => true,
];
