<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Enums;

enum RecipientType: string
{
    case TO = 'to';
    case CC = 'cc';
    case BCC = 'bcc';
}
