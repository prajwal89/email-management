<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Widgets;

use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Prajwal89\EmailManagement\Commands\CreateReceivableGroupCommand;
use Prajwal89\EmailManagement\Helpers\Helper;
use Prajwal89\EmailManagement\Models\ReceivableGroup;

class ReceivableGroupsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Receivable Groups';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '280px';

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('create')
                    ->icon('heroicon-o-plus')
                    ->outlined()
                    ->label('Create Group')
                    ->modalHeading('Instructions for Creating New Group')
                    ->modalContent(function (): Htmlable {
                        return new HtmlString(Helper::getCommandSignature(CreateReceivableGroupCommand::class));
                    }),
            ])
            ->query(
                ReceivableGroup::query()
            )
            ->columns([
                TextColumn::make('classname'),
                TextColumn::make('total')->sortable(),
                TextColumn::make('description'),
            ]);
    }
}
