<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Widgets\JobBatchInfoWidget;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Services\EmailCampaignService;

class EditEmailCampaign extends EditRecord
{
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Email Preview')
                ->icon('heroicon-o-eye')
                ->outlined()
                ->url(function ($record): string {
                    return EmailCampaignResource::getUrl('preview-email', ['record' => $record->id]);
                })
                ->openUrlInNewTab(),

            Action::make('delete')
                ->label('Delete')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->disabled(fn (): bool => !app()->isLocal())
                ->tooltip('Can Be deleted from local Environment only')
                ->modalDescription('This action will delate seeder file, handler class, email class and file, and all associated DB records')
                ->modalSubmitActionLabel('Delete')
                ->action(function (EmailCampaign $record) {
                    if (app()->environment('local')) {
                        EmailCampaignService::destroy($record);

                        Notification::make()
                            ->title('Deleted Successfully')
                            ->success()
                            ->send();

                        return redirect(EmailCampaignResource::getUrl('index'));
                    }
                    Notification::make()
                        ->title('Deletion allowed only in local environment')
                        ->danger()
                        ->send();
                }),

            Action::make('start')
                ->label(function ($record): string {
                    if (is_null($record->started_on)) {
                        return 'Start';
                    }

                    return 'Already Done';
                })
                ->icon('heroicon-o-play')
                ->disabled(fn ($record): bool => !is_null($record->started_on))
                ->url(function ($record): string {
                    return EmailCampaignResource::getUrl('start-campaign', ['record' => $record]);
                }),
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            JobBatchInfoWidget::make(['record' => $this->record]),
        ];
    }
}
