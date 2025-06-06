<?php

declare(strict_types=1);

return [
    'receivable_groups_path' => app_path('/EmailManagement/ReceivableGroups'),
    'mail_classes_path' => app_path('/EmailManagement/Emails'),
    'email_handlers_dir' => app_path('/EmailManagement/EmailHandlers'),
    'seeders_dir' => database_path('/seeders/EmailManagement'),
    'view_dir' => resource_path('/views/email-management'),

    'mailer' => 'smtp',
    'mailbox' => 'imap',
];
