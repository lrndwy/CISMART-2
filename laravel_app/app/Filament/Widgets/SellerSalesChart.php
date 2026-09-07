<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SellerSalesChart extends ChartWidget
{
    protected ?string $heading = 'My Sales (Last 7 Days)';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('seller');
    }

    protected function getData(): array
    {
        $user = auth()->user();
        $shopIds = $user->shops()->pluck('id');

        // Get last 7 days sales
        $data = collect(range(6, 0))->map(function ($daysAgo) use ($shopIds) {
            $date = now()->subDays($daysAgo);
            $sales = OrderItem::whereIn('shop_id', $shopIds)
                ->whereHas('order', fn($q) => $q->whereDate('created_at', $date)
                    ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']))
                ->sum('subtotal');

            return [
                'date' => $date->format('M d'),
                'sales' => $sales,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Sales (Rp)',
                    'data' => $data->pluck('sales')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
