<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class AdminRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Trend (Last 30 Days)';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('admin');
    }

    protected function getData(): array
    {
        // Get last 30 days revenue grouped by week
        $weeks = collect(range(3, 0))->map(function ($weeksAgo) {
            $startDate = now()->subWeeks($weeksAgo + 1);
            $endDate = now()->subWeeks($weeksAgo);
            
            $revenue = Order::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
                ->sum('total');

            return [
                'label' => 'Week ' . (4 - $weeksAgo),
                'revenue' => $revenue,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $weeks->pluck('revenue')->toArray(),
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                    ],
                ],
            ],
            'labels' => $weeks->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
