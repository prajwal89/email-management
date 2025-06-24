<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\Pages\EditEmailEvent;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\Pages\ListEmailEvents;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\Pages\PreviewEmailPage;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers\EmailLogsRelationManager;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers\EmailVariantsRelationManager;
use Prajwal89\EmailManagement\Models\EmailEvent;

class EmailEventResource extends Resource
{
    protected static ?string $model = EmailEvent::class;

    protected static ?string $navigationGroup = 'Emails';

    protected static ?string $navigationLabel = 'Events';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('slug')->disabled(),
                Textarea::make('description'),
                Toggle::make('is_enabled'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                IconColumn::make('is_enabled')->searchable(),
                TextColumn::make('sent_emails_count')
                    ->label('Sent')
                    ->counts('sentEmails')
                    ->sortable(),
                TextColumn::make('email_visits_count')
                    ->label('Visits')
                    ->counts('emailVisits')
                    ->sortable(),
                TextColumn::make('email_variants_count')
                    ->label('Variants')
                    ->counts('emailVariants')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                Action::make('preview')
                    ->label('Email Preview')
                    ->icon('heroicon-o-eye')
                    ->url(function ($record): string {
                        return self::getUrl('preview-email', ['record' => $record->slug]);
                    })
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            EmailLogsRelationManager::class,
            EmailVariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailEvents::route('/'),
            'edit' => EditEmailEvent::route('/{record}/edit'),
            'preview-email' => PreviewEmailPage::route('/{record}/preview-email'),
        ];
    }
}
