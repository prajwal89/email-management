<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource;
use Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource\Widgets\NewsletterEmailTrendChartWidget;

class ListNewsletterEmails extends ListRecords
{
    protected static string $resource = NewsletterEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Email')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            NewsletterEmailTrendChartWidget::class,
        ];
    }
}
