<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Prajwal89\EmailManagement\Commands\CreateReceivableGroupCommand;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource;
use Prajwal89\EmailManagement\Helpers\Helper;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Services\CampaignRunner;

class StartCampaignPage extends Page
{
    protected static string $resource = EmailCampaignResource::class;

    protected static string $view = 'em::filament.start-campaign-page';

    public EmailCampaign $record;

    public $allReceivableGroups;

    public int $delayBetweenJobs = 0;

    public array $selectedGroups = [];

    public int $totalReceivablesWithoutOverlapping = 0;

    public string $createGroupCommand = '';

    protected $campaignRunner;

    public function mount(): void
    {
        $this->campaignRunner = $this->getCampaignRunner();

        $this->allReceivableGroups = $this->campaignRunner->allGroupsData()->toArray();

        $this->createGroupCommand = Helper::getCommandSignature(CreateReceivableGroupCommand::class);
    }

    public function updatedSelectedGroups(): void
    {
        $this->campaignRunner = $this->getCampaignRunner();

        $this->totalReceivablesWithoutOverlapping = $this->campaignRunner->allReceivablesWithUniqueEmail()->count();
    }

    public function startProcess(): mixed
    {
        return Action::make('startCampaign')
            ->label('Start Campaign')
            ->color('primary')
            ->icon('heroicon-o-paper-airplane')
            ->modalHeading('Start Email Campaign')
            ->modalDescription('Review and confirm campaign details before sending.')
            ->modalWidth('xl')
            // ->form([
            //     TextInput::make('test')
            // ])
            ->action(function (array $data): void {
                if ($this->selectedGroups === []) {
                    Notification::make()
                        ->danger()
                        ->title('No Recipients Selected')
                        ->body('Please select at least one recipient group.')
                        ->send();

                    return;
                }

                // Perform additional validations
                $totalRecipients = $this->totalReceivablesWithoutOverlapping;

                if ($totalRecipients === 0) {
                    Notification::make()
                        ->warning()
                        ->title('No Valid Recipients')
                        ->body('The selected groups do not contain any valid recipients.')
                        ->send();

                    return;
                }

                (new CampaignRunner(
                    $this->record,
                    $this->selectedGroups,
                    $this->delayBetweenJobs
                ))->run();

                // Notify success
                Notification::make()
                    ->success()
                    ->title('Campaign Started')
                    ->body("Campaign initiated for {$totalRecipients} recipients.")
                    ->send();
            })
            ->requiresConfirmation()
            ->call();
    }

    public function getCampaignRunner()
    {
        return new CampaignRunner($this->record, $this->selectedGroups);
    }
}
