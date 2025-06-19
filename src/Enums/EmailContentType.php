<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EmailContentType: string implements HasColor, HasIcon, HasLabel
{
    case HTML = 'html';
    case MARKDOWN = 'markdown';
    case TEXT = 'text';

    public function getLabel(): string
    {
        return match ($this) {
            self::HTML => 'HTML',
            self::MARKDOWN => 'Markdown',
            self::TEXT => 'Plain Text',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HTML => 'danger',
            self::MARKDOWN => 'info',
            self::TEXT => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HTML => 'heroicon-o-code-bracket',
            self::MARKDOWN => 'heroicon-o-document-text',
            self::TEXT => 'heroicon-o-document',
        };
    }
}
