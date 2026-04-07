<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Widgets;

use Prajwal89\EmailManagement\Filament\BaseTrendChartWidget;
use Prajwal89\EmailManagement\Models\EmailLog;

class SentEmailsTrendWidget extends BaseTrendChartWidget
{
    protected string $modelFqn = EmailLog::class;

    protected ?string $heading = 'Logs';

}
