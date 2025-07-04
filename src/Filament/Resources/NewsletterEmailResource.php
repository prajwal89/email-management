<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers\EmailLogsRelationManager;
use Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource\Pages\CreateNewsletterEmail;
use Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource\Pages\EditNewsletterEmail;
use Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource\Pages\ListNewsletterEmails;
use Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource\Widgets\NewsletterEmailTrendChartWidget;
use Prajwal89\EmailManagement\Models\NewsletterEmail;

class NewsletterEmailResource extends Resource
{
    protected static ?string $model = NewsletterEmail::class;

    protected static ?string $navigationGroup = 'Emails';

    protected static ?string $navigationLabel = 'Newsletter Emails';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->required()
                    ->email(),
                DateTimePicker::make('email_verified_at'),
                DateTimePicker::make('unsubscribed_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email'),
                IconColumn::make('is_subscribed_for_emails')
                    ->label('Subscription Status')
                    ->getStateUsing(function ($record): bool {
                        return is_null($record->unsubscribed_at);
                    })
                    ->boolean(),
                TextColumn::make('email_verified_at')->dateTime(),
                TextColumn::make('created_at')
                    ->label('Created at')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date(),
                TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date(),
            ])
            ->filters([
                DateRangeFilter::make('email_verified_at'),
                DateRangeFilter::make('unsubscribed_at'),
                SelectFilter::make('Subscription Status')
                    ->options([
                        'subscribed' => 'Subscribed',
                        'unsubscribed' => 'Unsubscribed',
                        'subscribed_and_verified' => 'Subscribed and Verified',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value'])) {
                            return match ($data['value']) {
                                'subscribed' => $query->whereNull('unsubscribed_at'),
                                'unsubscribed' => $query->whereNotNull('unsubscribed_at'),
                                'subscribed_and_verified' => $query->whereNull('unsubscribed_at')
                                    ->whereNotNull('email_verified_at'),
                                default => $query,
                            };
                        }

                        return $query;
                    })
                    ->label('Subscription Status'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            EmailLogsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            NewsletterEmailTrendChartWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterEmails::route('/'),
            'create' => CreateNewsletterEmail::route('/create'),
            'edit' => EditNewsletterEmail::route('/{record}/edit'),
        ];
    }
}
