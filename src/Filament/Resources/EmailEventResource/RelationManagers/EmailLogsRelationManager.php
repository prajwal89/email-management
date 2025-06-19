<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;

class EmailLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'emailLogs';

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
                $query->with(['sendable', 'receivable', 'emailVariant']);
            })
            ->recordTitleAttribute('subject')
            ->columns([
                ...EmailLogResource::commonColumns(),
            ])
            ->filters([
                ...EmailLogResource::commonFilters(),
                SelectFilter::make('email_variant_id')
                    ->label('Email Variant')
                    ->visible(function () {
                        return $this->getOwnerRecord() instanceof EmailSendable;
                    })
                    ->options(fn () => $this->getOwnerRecord()->emailVariants()->pluck('name', 'id')->toArray()),
            ])
            ->actions([
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
