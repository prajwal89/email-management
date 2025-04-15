<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Widgets;

use Prajwal89\EmailManagement\Filament\BaseTrendChartWidget;
use Prajwal89\EmailManagement\Models\SentEmail;

class SentEmailsTrendWidget extends BaseTrendChartWidget
{
    protected static string $modelFqn = SentEmail::class;

    protected static ?string $heading = 'Sent Emails';

    protected static ?int $sort = 6;
}
