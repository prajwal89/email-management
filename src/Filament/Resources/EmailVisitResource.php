<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource\Pages;
use Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource\Pages\ListEmailVisits;
use Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource\Widgets\EmailVisitsTrendWidget;
use Prajwal89\EmailManagement\Models\EmailVisit;
use Prajwal89\EmailManagement\Models\SentEmail;

class EmailVisitResource extends Resource
{
    protected static ?string $model = EmailVisit::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Emails';

    protected static ?string $navigationLabel = 'Visits';

    protected static ?int $navigationSort = 3;

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
            ->columns([

                TextColumn::make('path')
                    ->limit(20)
                    ->searchable(),
                TextColumn::make('sentEmail.subject')
                    ->label('From Email')
                    ->searchable()
                    ->openUrlInNewTab()
                    ->url(function ($record): string {
                        return EmailLogResource::getUrl('preview-email', [
                            'record' => $record->sentEmail->id,
                        ]);
                    }),

                TextColumn::make('ip')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at'),

                SelectFilter::make('eventable')
                    ->label('Eventable')
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'no_eventable') {
                            return $query
                                ->whereHas('sentEmail.eventable', function ($query): void {
                                    $query
                                        ->whereNull('eventable_type')
                                        ->whereNull('eventable_id');
                                });
                        }

                        [$eventable_type, $eventable_id] = explode(':', $data['value']);

                        return $query
                            ->whereHas('sentEmail.eventable', function ($query) use ($eventable_type, $eventable_id): void {
                                $query
                                    ->where('eventable_type', $eventable_type)
                                    ->where('eventable_id', $eventable_id);
                            });
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
                            ->mapWithKeys(fn($data) => $data)
                            ->filter();

                        return $result->isEmpty()
                            ? collect(['no_eventable' => 'No Eventable'])
                            : $result->merge(['no_eventable' => 'No Eventable']);
                    }),
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
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            EmailVisitsTrendWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailVisits::route('/'),
            // 'create' => Pages\CreateEmailVisit::route('/create'),
            // 'edit' => Pages\EditEmailVisit::route('/{record}/edit'),
        ];
    }
}
