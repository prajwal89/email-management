<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource\Widgets;

use Prajwal89\EmailManagement\Filament\BaseTrendChartWidget;
use Prajwal89\EmailManagement\Models\EmailVisit;

class EmailVisitsTrendWidget extends BaseTrendChartWidget
{
    protected static string $modelFqn = EmailVisit::class;
}
