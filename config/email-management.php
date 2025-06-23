<?php

declare(strict_types=1);

// todo add comments explaining all the settings
return [
    'receivable_groups_path' => app_path('/EmailManagement/ReceivableGroups'),
    'mail_classes_path' => app_path('/EmailManagement/Emails'),
    'email_handlers_dir' => app_path('/EmailManagement/EmailHandlers'),
    'seeders_dir' => database_path('/seeders/EmailManagement'),
    'view_dir' => resource_path('/views/email-management'),
    'migrations_dir' => database_path('/migrations/email-management'),

    // todo get this from config with no default value
    /**
     * where the mail server should report the bounced emails
     * This Email address should be watched for bounce emails
     */
    'return_path' => 'bounces@example.com',

    /**
     * This is where users reply will be diverted
     */
    'reply_to' => 'replyto@example.com',

    'mailer' => 'smtp',

    /**
     * https://imapengine.com/docs/laravel/installation
     */
    'mailbox' => 'imap',

    /**
     * Record visits from the emails
     */
    'track_visits' => true,

    /**
     * Record if user have opened the email
     */
    'track_opens' => true,

    /**
     * adds the unsubscribe link to the footer
     */
    'inject_unsubscribe_link' => true,
];
