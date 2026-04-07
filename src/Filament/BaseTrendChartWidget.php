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
    protected ?string $heading = 'Users';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    public ?string $filter = 'Daily';

    // ! overwrite this with your model FQN
    protected string $modelFqn = User::class;

    protected function getData(): array
    {
        // This is hack do find better way to do this
        $this->heading = str($this->modelFqn)->afterLast('\\')->plural()->toString() . ' Trend';

        $query = Trend::model($this->modelFqn);

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
                    'label' => str($this->modelFqn)->afterLast('\\')->toString(),
                    'data' => $results->map(fn(TrendValue $value): mixed => $value->aggregate),
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
