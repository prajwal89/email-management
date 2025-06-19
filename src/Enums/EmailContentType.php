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

    public function getLabel(): string
    {
        return match ($this) {
            self::HTML => 'HTML',
            self::MARKDOWN => 'Markdown',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HTML => 'danger',
            self::MARKDOWN => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HTML => 'heroicon-o-code-bracket',
            self::MARKDOWN => 'heroicon-o-document-text',
        };
    }
}
