<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers;

use Exception;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\Filament\SharedActions;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\EmailVariantService;

class EmailVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'emailVariants';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $total = $ownerRecord->totalActiveVariants();

        return $total === 1 ? null : (string) $total;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('exposure_percentage')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100),

                Checkbox::make('is_paused'),
            ]);
    }

    // todo: get unique page views
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(function ($query) {
                // $query->withCount([
                //     'emailVisits as email_unique_visits_count' => function ($query) {
                //         // $query->unique();
                //     }
                // ]);
            })
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('content_type'),
                TextColumn::make('exposure_percentage')
                    ->label('Exposure')
                    ->suffix('%'),
                IconColumn::make('is_paused')
                    ->label('Paused'),
                IconColumn::make('is_winner')
                    ->label('Winner')
                    ->tooltip('Winner for this AB test'),
                TextColumn::make('email_logs_count')
                    ->label('Sent')
                    ->counts('emailLogs'),
                TextColumn::make('email_visits_count')
                    ->label('Visits')
                    ->counts('emailVisits'),
                TextColumn::make('CTR')
                    ->label('CTR')
                    ->getStateUsing(function ($record) {
                        $sent = $record->email_logs_count ?? 0;
                        $visits = $record->email_visits_count ?? 0;

                        if ($sent === 0) {
                            return number_format(0.00, 2);
                        }

                        $ctr = ($visits / $sent) * 100;

                        return number_format($ctr, 2);
                    })
                    ->suffix('%'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                ActionsAction::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->disabled(fn(): bool => !app()->isLocal())
                    ->tooltip('Can Be deleted from local Environment only')
                    ->modalDescription('This action will email file, and all associated DB records and will create seeder file for deleting the record')
                    ->modalSubmitActionLabel('Delete')
                    ->action(function (EmailVariant $record) {
                        if (!app()->isLocal()) {
                            Notification::make()
                                ->title('Deletion allowed only in local environment')
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($record->slug === 'default') {
                            throw new Exception('Cannot Delete Default Variant');
                        }

                        EmailVariantService::destroy($record);

                        Notification::make()
                            ->title('Now run `php artisan migrate` to delete the records')
                            ->success()
                            ->send();
                    }),
            ])
        ;
    }
}
