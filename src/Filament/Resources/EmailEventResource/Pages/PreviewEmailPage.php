<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource;
use Prajwal89\EmailManagement\Models\EmailEvent;

class PreviewEmailPage extends Page
{
    protected static string $resource = EmailEventResource::class;

    protected static string $view = 'email-management::filament.preview-email-page';

    public EmailEvent $record;

    public string $emailContent;

    public function mount(): void
    {
        // dd($this->record->resolveEmailHandler());
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
