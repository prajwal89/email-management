<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource\Widgets;

use Prajwal89\EmailManagement\Filament\BaseTrendChartWidget;
use Prajwal89\EmailManagement\Models\NewsletterEmail;

class NewsletterEmailTrendChartWidget extends BaseTrendChartWidget
{
    protected static ?string $heading = 'Newsletter Emails';

    protected static string $modelFqn = NewsletterEmail::class;
}
