<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Prajwal89\EmailManagement\Models\EmailLog;

class SendableOverview extends BaseWidget
{
    public ?string $sendableType = null;

    public ?string $sendableId = null;

    protected function getStats(): array
    {
        // Create a base query that conditionally filters by sendable type and ID
        $baseQuery = EmailLog::query()
            ->when($this->sendableId, function (Builder $query) {
                // dd($this->sendableId);
                $query->where('sendable_id', $this->sendableId);
            })
            ->when($this->sendableType, function (Builder $query) {
                $query->where('sendable_type', $this->sendableType);
            });

        // Date ranges for comparison
        $last7Days = Carbon::now()->subDays(7);
        $previous7Days = Carbon::now()->subDays(14);

        // Total emails sent in last 7 days vs previous 7 days
        $emailsSentLast7Days = (clone $baseQuery)
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', $last7Days)
            ->count();

        $emailsSentPrevious7Days = (clone $baseQuery)
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', $previous7Days)
            ->where('sent_at', '<', $last7Days)
            ->count();

        $emailsSentChange = $emailsSentPrevious7Days > 0
            ? round((($emailsSentLast7Days - $emailsSentPrevious7Days) / $emailsSentPrevious7Days) * 100, 1)
            : ($emailsSentLast7Days > 0 ? 100 : 0);

        // Total opens in last 7 days vs previous 7 days
        $totalOpensLast7Days = (clone $baseQuery)
            ->where('sent_at', '>=', $last7Days)
            ->sum('opens');

        $totalOpensPrevious7Days = (clone $baseQuery)
            ->where('sent_at', '>=', $previous7Days)
            ->where('sent_at', '<', $last7Days)
            ->sum('opens');

        $opensChange = $totalOpensPrevious7Days > 0
            ? round((($totalOpensLast7Days - $totalOpensPrevious7Days) / $totalOpensPrevious7Days) * 100, 1)
            : ($totalOpensLast7Days > 0 ? 100 : 0);

        // Click-through rate (CTR) in last 7 days vs previous 7 days
        $totalClicksLast7Days = (clone $baseQuery)
            ->where('sent_at', '>=', $last7Days)
            ->sum('clicks');

        $totalClicksPrevious7Days = (clone $baseQuery)
            ->where('sent_at', '>=', $previous7Days)
            ->where('sent_at', '<', $last7Days)
            ->sum('clicks');

        $ctrLast7Days = $emailsSentLast7Days > 0
            ? round(($totalClicksLast7Days / $emailsSentLast7Days) * 100, 2)
            : 0;

        $ctrPrevious7Days = $emailsSentPrevious7Days > 0
            ? round(($totalClicksPrevious7Days / $emailsSentPrevious7Days) * 100, 2)
            : 0;

        $ctrChange = round($ctrLast7Days - $ctrPrevious7Days, 2);

        // Bounce rate in last 7 days vs previous 7 days
        $totalBouncesLast7Days = (clone $baseQuery)
            ->bounced()
            ->where('sent_at', '>=', $last7Days)
            ->count();

        $totalBouncesPrevious7Days = (clone $baseQuery)
            ->bounced()
            ->where('sent_at', '>=', $previous7Days)
            ->where('sent_at', '<', $last7Days)
            ->count();

        $bounceRateLast7Days = $emailsSentLast7Days > 0
            ? round(($totalBouncesLast7Days / $emailsSentLast7Days) * 100, 2)
            : 0;

        $bounceRatePrevious7Days = $emailsSentPrevious7Days > 0
            ? round(($totalBouncesPrevious7Days / $emailsSentPrevious7Days) * 100, 2)
            : 0;

        $bounceRateChange = round($bounceRateLast7Days - $bounceRatePrevious7Days, 2);

        // Chart data for daily emails sent over last 7 days
        $sentChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = (clone $baseQuery)
                ->whereNotNull('sent_at')
                ->whereDate('sent_at', $date)
                ->count();
            $sentChartData[] = $count;
        }

        // Chart data for daily opens over last 7 days
        $opensChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $opens = (clone $baseQuery)
                ->whereDate('sent_at', $date)
                ->sum('opens');
            $opensChartData[] = $opens;
        }

        // Chart data for daily CTR over last 7 days
        $ctrChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dailySent = (clone $baseQuery)
                ->whereNotNull('sent_at')
                ->whereDate('sent_at', $date)
                ->count();
            $dailyClicks = (clone $baseQuery)
                ->whereDate('sent_at', $date)
                ->sum('clicks');
            $dailyCtr = $dailySent > 0 ? round(($dailyClicks / $dailySent) * 100, 2) : 0;
            $ctrChartData[] = $dailyCtr;
        }

        // Chart data for daily bounce rate over last 7 days
        $bounceChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dailySent = (clone $baseQuery)
                ->whereNotNull('sent_at')
                ->whereDate('sent_at', $date)
                ->count();
            $dailyBounces = (clone $baseQuery)
                ->where(function ($query) {
                    $query->whereNotNull('soft_bounced_at')
                        ->orWhereNotNull('hard_bounced_at');
                })
                ->whereDate('sent_at', $date)
                ->count();
            $dailyBounceRate = $dailySent > 0 ? round(($dailyBounces / $dailySent) * 100, 2) : 0;
            $bounceChartData[] = $dailyBounceRate;
        }

        return [
            Stat::make('Total Sent (7 days)', number_format($emailsSentLast7Days))
                ->description($emailsSentChange >= 0 ? "+{$emailsSentChange}% from previous week" : "{$emailsSentChange}% from previous week")
                ->descriptionIcon($emailsSentChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($sentChartData)
                ->color($emailsSentChange >= 0 ? 'success' : 'danger'),

            Stat::make('Total Opens', number_format($totalOpensLast7Days))
                ->description($opensChange >= 0 ? "+{$opensChange}% from previous week" : "{$opensChange}% from previous week")
                ->descriptionIcon($opensChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($opensChartData)
                ->color($opensChange >= 0 ? 'success' : 'danger'),

            Stat::make('CTR (7 days)', $ctrLast7Days . '%')
                ->description($ctrChange >= 0 ? "+{$ctrChange}% from previous week" : "{$ctrChange}% from previous week")
                ->descriptionIcon($ctrChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($ctrChartData)
                ->color($ctrChange >= 0 ? 'success' : 'danger'),

            Stat::make('Bounce Rate (7 days)', $bounceRateLast7Days . '%')
                ->description($bounceRateChange <= 0 ? abs($bounceRateChange) . '% decrease from previous week' : "+{$bounceRateChange}% from previous week")
                ->descriptionIcon($bounceRateChange <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->chart($bounceChartData)
                ->color($bounceRateChange <= 0 ? 'success' : 'danger'),
        ];
    }
}
