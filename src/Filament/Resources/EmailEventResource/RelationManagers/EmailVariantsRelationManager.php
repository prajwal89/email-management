<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Prajwal89\EmailManagement\Filament\SharedActions;

class EmailVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'emailVariants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('exposure_percentage')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100),

                Checkbox::make('is_paused'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('exposure_percentage')
                    ->label('Exposure')
                    ->suffix('%'),
                IconColumn::make('is_paused')->label('Paused'),
                IconColumn::make('is_winner')->label('Winner')
                    ->tooltip('Winner for this AB test'),
                TextColumn::make('email_logs_count')
                    ->label('Sent')
                    ->counts('emailLogs'),
                // TextColumn::make('email_unique_visits_count')
                TextColumn::make('email_visits_count')
                    ->label('Visits')
                    ->counts('emailVisits'),

                TextColumn::make('CTR')
                    ->label('CTR')
                    ->getStateUsing(function ($record) {
                        $sent = $record->email_logs_count ?? 0;
                        $visits = $record->email_visits_count ?? 0;

                        if ($sent === 0) {
                            return '0%';
                        }

                        $ctr = ($visits / $sent) * 100;

                        return number_format($ctr, 2);
                    })
                    ->suffix('%'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                // SharedActions::createEmailVariant(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function ($query) {
                // $query->withCount([
                //     'emailVisits as email_unique_visits_count' => function ($query) {
                //         // $query->unique();
                //     }
                // ]);
            });
    }
}
