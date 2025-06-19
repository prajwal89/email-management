<?php

namespace Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RunsRelationManager extends RelationManager
{
    protected static string $relationship = 'runs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('batch_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->recordTitleAttribute('batch_id')
            ->columns([
                TextColumn::make('batch_id')
                    ->label('Batch ID')
                    ->limit(12)
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('jobBatch.total_jobs')
                    ->label('Total Jobs')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jobBatch.pending_jobs')
                    ->label('Pending Jobs')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'success'),

                TextColumn::make('jobBatch.failed_job_ids')
                    ->label('Failed Jobs')
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn($state) => is_array($state) ? count($state) : 0)
                    ->color(fn($state) => (is_array($state) && count($state) > 0) ? 'danger' : 'success')
                    ->badge()
                    ->tooltip(function ($state) {
                        if (!is_array($state) || empty($state)) {
                            return 'No failed jobs';
                        }

                        return 'Failed Job IDs: ' . implode(', ', array_slice($state, 0, 10)) .
                            (count($state) > 10 ? '... (' . count($state) . ' total)' : '');
                    }),

                TextColumn::make('jobBatch.cancelled_at')
                    ->label('Cancelled At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Not cancelled'),

                TextColumn::make('jobBatch.finished_at')
                    ->label('Finished At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Not finished'),

                TextColumn::make('jobBatch.created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ProgressBar::make('Progress')
                    ->label('Progress')
                    ->width('200px')
                    ->getStateUsing(function ($record): array {
                        $total = $record->jobBatch->total_jobs ?? 100;
                        $progress = $record->jobBatch->pending_jobs ?? 100;

                        return [
                            'total' => $total,
                            'progress' => $total - $progress,
                        ];
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
        ;
    }
}
