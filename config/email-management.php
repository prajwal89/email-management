<?php

declare(strict_types=1);

// todo add comments explaining all the settings
return [
    'receivable_groups_path' => app_path('/EmailManagement/ReceivableGroups'),
    'mail_classes_path' => app_path('/EmailManagement/Emails'),
    'email_handlers_dir' => app_path('/EmailManagement/EmailHandlers'),
    'seeders_dir' => database_path('/seeders/EmailManagement'),
    'view_dir' => resource_path('/views/email-management'),

    // todo get this from config with no default value
    'return_path' => 'bounces@example.com',
    'reply_to' => 'replyto@example.com',

    'mailer' => 'smtp',

    /**
     * https://imapengine.com/docs/laravel/installation
     */
    'mailbox' => 'imap',

    'track_visits' => true,
    'track_opens' => true,
    'inject_unsubscribe_link' => true,
];
