<?php

namespace Prajwal89\EmailManagement\Enums;

enum RecipientType: string
{
    case TO = 'To';
    case CC = 'Cc';
    case BCC = 'Bcc';
}
