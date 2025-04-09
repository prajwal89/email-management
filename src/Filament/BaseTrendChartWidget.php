<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

// find better place to keep this file as we require this in other modules
// we can always copy paste in other modules
class BaseTrendChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Users';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '280px';

    public ?string $filter = 'Daily';

    // ! overwrite this with your model FQN
    protected static string $modelFqn = User::class;

    protected function getData(): array
    {
        // This is hack do find better way to do this
        static::$heading = str(static::$modelFqn)->afterLast('\\')->plural()->toString() . ' Trend';

        $query = Trend::model(static::$modelFqn);

        $results = match ($this->filter) {
            'Monthly' => $query->between(
                start: now()->subMonths(40),
                end: now(),
            )->perMonth()->count(),
            'Daily' => $query->between(
                start: now()->subDays(40),
                end: now(),
            )->perDay()->count(),
            'Hourly' => $query->between(
                start: now()->subHours(40),
                end: now(),
            )->perHour()->count(),
            'Minutely' => $query->between(
                start: now()->subMinutes(40),
                end: now(),
            )->perMinute()->count()
        };

        return [
            'datasets' => [
                [
                    'label' => str(static::$modelFqn)->afterLast('\\')->toString(),
                    'data' => $results->map(fn (TrendValue $value): mixed => $value->aggregate),
                ],
            ],
            'labels' => $results->map(function (TrendValue $value): string {
                return $value->date;
            }),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'Monthly' => 'Monthly',
            'Daily' => 'Daily',
            'Hourly' => 'Hourly',
            'Minutely' => 'Minutely',
        ];
    }
}
