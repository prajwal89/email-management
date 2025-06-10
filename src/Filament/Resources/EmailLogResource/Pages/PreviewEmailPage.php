<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\SentEmail;

class PreviewEmailPage extends Page
{
    protected static string $resource = EmailLogResource::class;

    protected static string $view = 'email-management::filament.preview-email-page';

    protected EmailLog $record;

    public string $emailContent;

    public function mount($record): void
    {
        $this->record = EmailLog::query()->findOrFail($record);

        $this->emailContent = $this->record->html;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resend')
                ->label('Resend')
                ->action(function ($record): void {
                    // dd($record);
                }),

        ];
    }

    public function getTitle(): string
    {
        return 'Preview: ' . $this->record->subject;
    }
}
