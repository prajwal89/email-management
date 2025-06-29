<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource;
use Prajwal89\EmailManagement\Services\FollowUpEmailsSender;
use Prajwal89\EmailManagement\Services\FollowUpService;

class FollowUpsRelationManager extends RelationManager
{
    protected static string $relationship = 'followUps';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $total = $ownerRecord->followUps()->count();

        return $total > 0 ? (string) $total : null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sendable.name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('followupEmailEvent.name')
            ->modifyQueryUsing(function ($query) {
                $query->with(['followupEmailEvent']);
            })
            ->defaultSort('wait_for_days', 'asc')
            ->columns([
                // and when it was sent
                TextColumn::make('followupEmailEvent.name')
                    ->label('Follow Up Email Event')
                    ->openUrlInNewTab()
                    ->url(function ($record) {
                        return EmailEventResource::getUrl('edit', [
                            'record' => $record->followupEmailEvent->slug,
                        ]);
                    }),

                TextColumn::make('wait_for_days'),

                // todo: status of if follow up sent
                // this is not possible as this will be sent to a receivable
                // TextColumn::make('status')
                // ->getStateUsing(function($record){
                //     FollowUpEmailsSender::checkIfFollowUpEmailSent($record)
                // }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([

                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->outlined()
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->disabled(fn (): bool => !app()->isLocal())
                    ->tooltip('Can Be deleted from local Environment only')
                    ->modalDescription('This action will create migration file for deleting the record')
                    ->modalSubmitActionLabel('Delete')
                    ->action(function ($record) {
                        if (!app()->isLocal()) {
                            Notification::make()
                                ->title('Deletion allowed only in local environment')
                                ->danger()
                                ->send();

                            return;
                        }

                        FollowUpService::destroy($record);

                        Notification::make()
                            ->title('Now run `php artisan migrate` to delete the records')
                            ->success()
                            ->send();
                    }),

                Action::make('preview')
                    ->label('Email Preview')
                    ->icon('heroicon-o-eye')
                    ->url(function ($record): string {
                        return EmailEventResource::getUrl('preview-email', [
                            'record' => $record->followupEmailEvent->slug,
                        ]);
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
