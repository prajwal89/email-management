<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Enums;

enum ContentType: string
{
    case HTML = 'html';
    case MARKDOWN = 'markdown';
    case TEXT = 'text';
}
