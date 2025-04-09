<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Widgets\SentEmailsTrendWidget;
use Prajwal89\EmailManagement\Services\EmailEventService;

class EditEmailEvent extends EditRecord
{
    protected static string $resource = EmailEventResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('preview')
                ->label('Email Preview')
                ->icon('heroicon-o-eye')
                ->outlined()
                ->url(function ($record): string {
                    return EmailEventResource::getUrl('preview-email', ['record' => $record->id]);
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
                ->action(function ($record) {
                    if (app()->environment('local')) {
                        EmailEventService::destroy($record);

                        Notification::make()
                            ->title('Deleted Successfully')
                            ->success()
                            ->send();

                        return redirect(EmailEventResource::getUrl('index'));
                    }
                    Notification::make()
                        ->title('Deletion allowed only in local environment')
                        ->danger()
                        ->send();
                }),

        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            SentEmailsTrendWidget::make([
                'record' => $this->record,
            ]),
        ];
    }
}
