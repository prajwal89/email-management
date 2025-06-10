<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Widgets;

use Prajwal89\EmailManagement\Filament\BaseTrendChartWidget;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\SentEmail;

class SentEmailsTrendWidget extends BaseTrendChartWidget
{
    protected static string $modelFqn = EmailLog::class;

    protected static ?string $heading = 'Logs';

    protected static ?int $sort = 6;
}
