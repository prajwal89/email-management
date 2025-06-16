<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Prajwal89\EmailManagement\Commands\CreateEmailEventCommand;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource;
use Prajwal89\EmailManagement\Helpers\Helper;

class ListEmailEvents extends ListRecords
{
    protected static string $resource = EmailEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->icon('heroicon-o-plus')
                ->outlined()
                ->label('Create Email Event')
                ->modalHeading('Instructions for Creating an Email Event')
                ->modalContent(function (): Htmlable {
                    return new HtmlString(Helper::getCommandSignature(CreateEmailEventCommand::class));
                }),
        ];
    }
}
