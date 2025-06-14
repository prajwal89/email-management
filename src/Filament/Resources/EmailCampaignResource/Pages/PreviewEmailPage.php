<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource;
use Prajwal89\EmailManagement\Models\EmailCampaign;

class PreviewEmailPage extends Page
{
    protected static string $resource = EmailCampaignResource::class;

    protected static string $view = 'em::filament.preview-email-page';

    public EmailCampaign $record;

    public string $emailContent;

    public function mount(): void
    {
        $this->emailContent = $this->record->resolveEmailHandler()::renderEmailForPreview();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_sample')
                ->label('Send Sample')
                ->form([
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->default(config('custom.admin_email')),
                ])
                ->action(function (array $data): void {
                    $this->record->resolveEmailHandler()::sendSampleEmailTo($data['email']);

                    Notification::make()
                        ->success()
                        ->title('Email sent successfully')
                        ->send();
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'Preview Email';
    }
}
