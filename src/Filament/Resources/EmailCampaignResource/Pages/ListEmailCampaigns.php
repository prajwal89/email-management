<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Prajwal89\EmailManagement\Commands\CreateEmailCampaignCommand;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Widgets\ReceivableGroupsTableWidget;
use Prajwal89\EmailManagement\Filament\Widgets\SendableOverview;
use Prajwal89\EmailManagement\Helpers\Helper;
use Prajwal89\EmailManagement\Models\EmailCampaign;

class ListEmailCampaigns extends ListRecords
{
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->icon('heroicon-o-plus')
                ->outlined()
                ->label('Email Campaign')
                ->modalHeading('Instructions for Creating an Email Campaign')
                ->modalContent(function (): Htmlable {
                    return new HtmlString(Helper::getCommandSignature(CreateEmailCampaignCommand::class));
                }),
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            ReceivableGroupsTableWidget::class,
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            SendableOverview::make([
                'sendableType' => EmailCampaign::class
            ]),
        ];
    }
}
