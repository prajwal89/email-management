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
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\EmailVisit;

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

                TextColumn::make('emailLogs.message_id')
                    ->label('Message Id')
                    ->searchable()
                    ->openUrlInNewTab()
                    ->url(function ($record): string {
                        return EmailLogResource::getUrl('preview-email', [
                            'record' => $record->emailLogs->id,
                        ]);
                    }),

                TextColumn::make('emailLogs.subject')
                    ->label('From Email')
                    ->searchable()
                    ->openUrlInNewTab()
                    ->url(function ($record): string {
                        return EmailLogResource::getUrl('preview-email', [
                            'record' => $record->emailLogs->id,
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

                SelectFilter::make('sendable')
                    ->label('sendable')
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'no_sendable') {
                            return $query
                                ->whereHas('emailLog.sendable', function ($query): void {
                                    $query
                                        ->whereNull('sendable_type')
                                        ->whereNull('sendable_id');
                                });
                        }

                        [$sendable_type, $sendable_id] = explode(':', $data['value']);

                        return $query
                            ->whereHas('emailLog.sendable', function ($query) use ($sendable_type, $sendable_id): void {
                                $query
                                    ->where('sendable_type', $sendable_type)
                                    ->where('sendable_id', $sendable_id);
                            });
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

                        return $result->isEmpty()
                            ? collect(['no_sendable' => 'No sendable'])
                            : $result->merge(['no_sendable' => 'No sendable']);
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
