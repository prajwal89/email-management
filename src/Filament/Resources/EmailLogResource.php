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
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Modules\Auth\Filament\Resources\UserResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers\EmailLogsRelationManager;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Pages\ListSentEmails;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Pages\PreviewEmailPage;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Widgets\SentEmailsTrendWidget;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\NewsletterEmail;

class EmailLogResource extends Resource
{
    protected static ?string $model = EmailLog::class;

    protected static ?string $navigationGroup = 'Emails';

    protected static ?string $navigationLabel = 'Email Logs';

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
                $query->with(['sendable', 'receivable']);
            })
            ->columns([
                ...self::commonColumns(),
            ])
            ->filters([
                // QueryBuilder::make()
                //     ->constraints([
                //         TextConstraint::make('subject'),

                //         // BooleanConstraint::make('is_visible'),
                //         // NumberConstraint::make('stock'),
                //         // SelectConstraint::make('status')
                //         //     ->options([
                //         //         'draft' => 'Draft',
                //         //         'reviewing' => 'Reviewing',
                //         //         'published' => 'Published',
                //         //     ])
                //         //     ->multiple(),
                //         // DateConstraint::make('created_at'),
                //         // RelationshipConstraint::make('categories')
                //         //     ->multiple()
                //         //     ->selectable(
                //         //         IsRelatedToOperator::make()
                //         //             ->titleAttribute('name')
                //         //             ->searchable()
                //         //             ->multiple(),
                //         //     ),
                //         // NumberConstraint::make('reviewsRating')
                //         //     ->relationship('reviews', 'rating')
                //         //     ->integer(),
                //     ]),

                ...self::commonFilters(),
            ], FiltersLayout::AboveContent)
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
            ->modifyQueryUsing(function ($query) {
                $query->with(['to']);
            })
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
            // we can crease side section modal
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
                ->label('subject'),

            TextColumn::make('to.email')
                ->searchable()
                ->label('to'),

            TextColumn::make('message_id')
                ->label('message_id')
                ->toggleable(isToggledHiddenByDefault: true)
                ->searchable(),

            TextColumn::make('status')
                ->label('status')
                ->badge()
                ->getStateUsing(function (EmailLog $record) {
                    return $record->getStatus();
                }),

            TextColumn::make('sendable')
                ->label('sendable')
                ->hidden(
                    fn ($livewire): bool => $livewire instanceof EmailLogsRelationManager
                )
                ->getStateUsing(function ($record) {
                    return $record?->sendable?->name ?? '';
                })
                ->openUrlInNewTab()
                ->url(function ($record) {
                    if (is_null($record?->sendable)) {
                        return '';
                    }

                    if ($record->sendable instanceof EmailEvent) {
                        return EmailEventResource::getUrl('edit', ['record' => $record->sendable->id]);
                    }

                    if ($record->sendable instanceof EmailCampaign) {
                        return EmailCampaignResource::getUrl('edit', ['record' => $record->sendable->id]);
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

            TextColumn::make('emailVariant.name')
                ->label('Variant')
                ->searchable(),

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

            TextColumn::make('last_clicked_at')
                ->label('Last clicked at')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->date('Y-M-d H:i'),

            TextColumn::make('last_opened_at')
                ->label('Last opened at')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->date('Y-M-d H:i'),

            TextColumn::make('created_at')
                ->label('Created at')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->date(),
        ];
    }

    public static function commonFilters(): array
    {
        return [
            SelectFilter::make('sendable')
                ->label('sendable')
                ->searchable()
                ->query(function (Builder $query, array $data): Builder {
                    if (empty($data['value'])) {
                        return $query;
                    }

                    if ($data['value'] === 'no_sendable') {
                        return $query
                            ->whereNull('sendable_type')
                            ->whereNull('sendable_id');
                    }

                    [$sendable_type, $sendable_id] = explode(':', $data['value']);

                    return $query
                        ->where('sendable_type', $sendable_type)
                        ->where('sendable_id', $sendable_id);
                })
                ->options(function () {
                    $result = EmailLog::query()
                        ->select('sendable_type', 'sendable_id')
                        ->with(['sendable'])
                        ->whereNotNull('sendable_id')
                        ->whereNotNull('sendable_type')
                        ->distinct()
                        ->latest()
                        ->get()
                        ->filter()
                        ->map(function (EmailLog $email) {
                            $sendable = $email->sendable;
                            if ($sendable === null) {
                                return null;
                            }

                            return [
                                get_class($sendable) . ':' . $sendable->id => $sendable->name,
                            ];
                        })
                        ->mapWithKeys(fn ($data) => $data)
                        ->filter();

                    if ($result->isEmpty()) {
                        return collect(['no_sendable' => 'No sendable']);
                    }

                    return $result->merge(['no_sendable' => 'No sendable']);
                }),

            DateRangeFilter::make('created_at'),

            Filter::make('sent_at')
                ->label('Sent')
                ->query(fn (Builder $query): Builder => $query->sent()),

            Filter::make('last_opened_at')
                ->label('Opened')
                ->query(fn (Builder $query): Builder => $query->opened()),

            Filter::make('last_clicked_at')
                ->label('Clicked')
                ->query(fn (Builder $query): Builder => $query->clicked()),

            Filter::make('replied_at')
                ->label('Replied')
                ->query(fn (Builder $query): Builder => $query->replied()),

            Filter::make('complained_at')
                ->label('Complained')
                ->query(fn (Builder $query): Builder => $query->complained()),

            Filter::make('soft_bounced')
                ->label('Soft Bounced')
                ->query(fn (Builder $query): Builder => $query->softBounced()),

            Filter::make('hard_bounced')
                ->label('Hard Bounced')
                ->query(fn (Builder $query): Builder => $query->hardBounced()),

            Filter::make('unsubscribed_at')
                ->label('Unsubscribed')
                ->query(fn (Builder $query): Builder => $query->unsubscribed()),
        ];
    }
}
