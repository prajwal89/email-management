<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource;

class SentEmailsRelationManager extends RelationManager
{
    protected static string $relationship = 'sentEmails';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query): void {
                $query->with(['eventable', 'receivable']);
            })
            ->recordTitleAttribute('subject')
            ->columns([
                ...EmailLogResource::commonColumns(),
                // Tables\Columns\TextColumn::make('test')
                //     ->getStateUsing(function ($record) {
                //         dd($record);
                //     }),
            ])
            ->filters([
                ...EmailLogResource::commonFilters(),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),

                Action::make('preview')
                    ->label('Email Preview')
                    ->icon('heroicon-o-eye')
                    ->openUrlInNewTab()
                    ->url(function ($record): string {
                        return EmailLogResource::getUrl('preview-email', ['record' => $record->id]);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
