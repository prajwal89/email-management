<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages;

use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\Pages\PreviewEmailPage as PagesPreviewEmailPage;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailVariant;

class PreviewEmailPage extends Page
{
    protected static string $resource = EmailCampaignResource::class;

    protected static string $view = 'em::filament.preview-email-page';

    public EmailCampaign $record;

    public function getTitle(): string|Htmlable
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_sample')
                ->label('Send Sample')
                ->icon('heroicon-o-paper-airplane')
                ->outlined()
                ->form([
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->default(config('custom.admin_email')),
                    Select::make('email_variant_id')
                        ->label('Email Variant')
                        ->options(function () {
                            return $this->record->emailVariants->pluck('name', 'id');
                        })
                        ->default($this->record->emailVariants->first()->id),
                ])
                ->action(function (array $data): void {

                    $emailVariant = EmailVariant::find($data['email_variant_id']);

                    $this
                        ->record
                        ->resolveEmailHandler()::sendSampleEmailTo($data['email'], $emailVariant);

                    Notification::make()
                        ->success()
                        ->title('Email sent successfully')
                        ->send();
                }),
        ];
    }
}
