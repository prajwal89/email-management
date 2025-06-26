<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource;
use Prajwal89\EmailManagement\Models\EmailLog;

class PreviewEmailPage extends Page
{
    protected static string $resource = EmailLogResource::class;

    protected static string $view = 'em::filament.preview-email-log-page';

    public $record;

    public function mount($record): void
    {
        $this->record = EmailLog::query()->findOrFail($record);

        $this->record->load([
            'followUpEmailLogs' => function ($query) {
                $query->orderBy('sent_at', 'desc');
            },
            'followUpEmailLogs.sendable',
            'recipients'
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resend')
                ->label('Resend')
                ->action(function ($record): void {
                    // todo: implement this
                    // dd($record);
                }),

        ];
    }

    public function getTitle(): string
    {
        return 'Preview: ' . $this->record->subject;
    }
}
