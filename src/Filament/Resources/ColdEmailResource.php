<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Prajwal89\EmailManagement\Filament\Resources\ColdEmailResource\Pages;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers\SentEmailsRelationManager;
use Prajwal89\EmailManagement\Models\ColdEmail;

class ColdEmailResource extends Resource
{
    protected static ?string $model = ColdEmail::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Emails';

    protected static ?string $navigationLabel = 'Cold Emails';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->email()->required(),
                TextInput::make('collection_reason')
                    ->required(),
                TextInput::make('collected_from')
                    ->required(),
                DateTimePicker::make('unsubscribed_at')
                // ->date()
                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('collection_reason')
                    ->searchable(),
                TextColumn::make('collected_from')
                    ->searchable(),
                TextColumn::make('unsubscribed_at')
                    ->date()
                    ->searchable(),
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
                SelectFilter::make('collection_reason')
                    ->searchable()
                    ->options(function () {
                        return ColdEmail::query()
                            ->distinct()
                            ->pluck('collection_reason')
                            ->filter()
                            ->mapWithKeys(function ($collection_reason) {
                                return [
                                    $collection_reason => $collection_reason,
                                ];
                            });
                    }),
                SelectFilter::make('collected_from')
                    ->searchable()
                    ->options(function () {
                        return ColdEmail::query()
                            ->distinct()
                            ->pluck('collected_from')
                            ->filter()
                            ->mapWithKeys(function ($collected_from) {
                                return [
                                    $collected_from => $collected_from,
                                ];
                            });
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SentEmailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColdEmails::route('/'),
            'create' => Pages\CreateColdEmail::route('/create'),
            'edit' => Pages\EditColdEmail::route('/{record}/edit'),
        ];
    }
}
