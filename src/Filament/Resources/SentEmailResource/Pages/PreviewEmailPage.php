<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource;
use Prajwal89\EmailManagement\Models\SentEmail;

class PreviewEmailPage extends Page
{
    protected static string $resource = SentEmailResource::class;

    protected static string $view = 'email-management::filament.preview-email-page';

    protected SentEmail $record;

    public string $emailContent;

    public function mount($record): void
    {
        $this->record = SentEmail::query()->findOrFail($record);

        // option to preview with tracking injected
        $this->emailContent = $this->record->email_content;

        // dd($this->emailContent);
        // dd($this->record->slug);
        // dd($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resend')
                ->label('Resend')
                ->action(function ($record): void {
                    //
                    // dd($record);
                }),

        ];
    }

    public function getTitle(): string
    {
        return 'Preview: ' . $this->record->subject;
    }
}
