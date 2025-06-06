<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Modules\Auth\Filament\Resources\UserResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers\SentEmailsRelationManager;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Pages;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Pages\ListSentEmails;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Pages\PreviewEmailPage;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Widgets\SentEmailsTrendWidget;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\NewsletterEmail;
use Prajwal89\EmailManagement\Models\SentEmail;

class SentEmailResource extends Resource
{
    protected static ?string $model = SentEmail::class;

    protected static ?string $navigationGroup = 'Emails';

    protected static ?string $navigationLabel = 'Sent';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query): void {
                $query->with(['eventable', 'receivable']);
            })
            ->columns([
                ...self::commonColumns(),
            ])
            ->filters([
                ...self::commonFilters(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->openUrlInNewTab()
                    ->url(function ($record): string {
                        return self::getUrl('preview-email', ['record' => $record->id]);
                    }),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSentEmails::route('/'),
            // 'create' => Pages\CreateSentEmail::route('/create'),
            // 'edit' => Pages\EditSentEmail::route('/{record}/edit'),
            'preview-email' => PreviewEmailPage::route('/{record}/preview-email'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            SentEmailsTrendWidget::class,
        ];
    }

    public static function commonColumns(): array
    {
        return [
            TextColumn::make('subject')
                ->label('Subject')
                ->searchable(),

            TextColumn::make('eventable')
                ->label('Eventable')
                ->hidden(
                    fn ($livewire): bool => $livewire instanceof SentEmailsRelationManager
                )
                ->getStateUsing(function ($record) {
                    return $record?->eventable?->name ?? '';
                })
                ->openUrlInNewTab()
                ->url(function ($record) {
                    if (is_null($record?->eventable)) {
                        return '';
                    }

                    if ($record->eventable instanceof EmailEvent) {
                        return EmailEventResource::getUrl('edit', ['record' => $record->eventable->id]);
                    }

                    if ($record->eventable instanceof EmailCampaign) {
                        return EmailCampaignResource::getUrl('edit', ['record' => $record->eventable->id]);
                    }
                }),

            TextColumn::make('receivable')
                ->label('receivable')
                ->getStateUsing(function ($record) {
                    // dd($record);
                    return $record?->receivable?->getName();
                })
                ->openUrlInNewTab()
                ->url(function ($record) {
                    if (is_null($record?->receivable)) {
                        return '';
                    }

                    if ($record->receivable instanceof User) {
                        return UserResource::getUrl('edit', ['record' => $record->receivable->id]);
                    }

                    if ($record->receivable instanceof NewsletterEmail) {
                        return NewsletterEmailResource::getUrl('edit', ['record' => $record->receivable->id]);
                    }
                }),

            TextColumn::make('hash')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('context')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('sender_email')
                ->label('Sender')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('recipient_email')
                ->label('Recipient')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('clicked_at')
                ->label('Clicked at')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->date('Y-M-d H:i'),

            TextColumn::make('opened_at')
                ->label('Opened at')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->date('Y-M-d H:i'),

            TextColumn::make('created_at')
                ->label('Created at')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->date(),

            // TextColumn::make('context')->label('Context'),
        ];
    }

    public static function commonFilters(): array
    {
        return [
            SelectFilter::make('eventable')
                ->label('Eventable')
                ->searchable()
                ->query(function (Builder $query, array $data): Builder {
                    if (empty($data['value'])) {
                        return $query;
                    }

                    if ($data['value'] === 'no_eventable') {
                        return $query
                            ->whereNull('eventable_type')
                            ->whereNull('eventable_id');
                    }

                    [$eventable_type, $eventable_id] = explode(':', $data['value']);

                    return $query
                        ->where('eventable_type', $eventable_type)
                        ->where('eventable_id', $eventable_id);
                })
                ->options(function () {
                    $result = SentEmail::query()
                        ->select('eventable_type', 'eventable_id')
                        ->with(['eventable'])
                        ->whereNotNull('eventable_id')
                        ->whereNotNull('eventable_type')
                        ->distinct()
                        ->latest()
                        ->get()
                        ->filter()
                        ->map(function (SentEmail $email) {
                            $eventable = $email->eventable;
                            if ($eventable === null) {
                                return null;
                            }

                            return [
                                get_class($eventable) . ':' . $eventable->id => $eventable->name,
                            ];
                        })
                        ->mapWithKeys(fn ($data) => $data)
                        ->filter();

                    if ($result->isEmpty()) {
                        return collect(['no_eventable' => 'No Eventable']);
                    }

                    return $result->merge(['no_eventable' => 'No Eventable']);
                }),

            DateRangeFilter::make('created_at'),

            Filter::make('opened_at')
                ->label('Opened')
                ->query(fn (Builder $query): Builder => $query->whereNotNull('opened_at')),

            Filter::make('clicked_at')
                ->label('Clicked')
                ->query(fn (Builder $query): Builder => $query->whereNotNull('clicked_at')),
        ];
    }
}
