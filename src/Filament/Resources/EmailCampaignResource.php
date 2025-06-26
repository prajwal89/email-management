<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages\EditEmailCampaign;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages\ListEmailCampaigns;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages\PreviewEmailPage;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages\StartCampaignPage;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\RelationManagers\RunsRelationManager;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Widgets\JobBatchInfoWidget;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Widgets\ReceivableGroupsTableWidget;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers\EmailLogsRelationManager;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers\EmailVariantsRelationManager;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\RelationManagers\FollowUpsRelationManager;
use Prajwal89\EmailManagement\Models\EmailCampaign;

// todo to calculate conversion rate we need to calculate only unique visits
class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    protected static ?string $navigationGroup = 'Emails';

    protected static ?string $navigationLabel = 'Campaigns';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('slug')->disabled(),
                Textarea::make('description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
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
                TextColumn::make('runs_count')
                    ->label('Runs')
                    ->counts('runs')
                    ->sortable(),
                ProgressBar::make('Progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record): array {
                        // dd($record->jobBatch);
                        $total = $record->runs()->latest()->first()->jobBatch->total_jobs ?? 100;
                        $progress = $record->runs()->latest()->first()->jobBatch->pending_jobs ?? 100;

                        return [
                            'total' => $total,
                            'progress' => $total - $progress,
                        ];
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                Action::make('preview')
                    ->label('Email Preview')
                    ->icon('heroicon-o-eye')
                    ->openUrlInNewTab()
                    ->url(function ($record): string {
                        return self::getUrl('preview-email', ['record' => $record->slug]);
                    }),
            ])
            ->modifyQueryUsing(function ($query) {
                $query->with(['runs.jobBatch']);
            })
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            EmailLogsRelationManager::class,
            EmailVariantsRelationManager::class,
            RunsRelationManager::class,
            FollowUpsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            JobBatchInfoWidget::class,
            ReceivableGroupsTableWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailCampaigns::route('/'),
            // 'create' => Pages\CreateEmailCampaign::route('/create'),
            'edit' => EditEmailCampaign::route('/{record}/edit'),
            'preview-email' => PreviewEmailPage::route('/{record}/preview-email'),
            'start-campaign' => StartCampaignPage::route('/{record}/start'),
        ];
    }
}
