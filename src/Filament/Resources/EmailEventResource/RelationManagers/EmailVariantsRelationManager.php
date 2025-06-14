<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('exposure_percentage')
                    ->label('Exposure')
                    ->suffix('%'),
                Tables\Columns\IconColumn::make('is_paused'),
                Tables\Columns\IconColumn::make('is_winner'),
                TextColumn::make('email_logs_count')
                    ->counts('emailLogs')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
