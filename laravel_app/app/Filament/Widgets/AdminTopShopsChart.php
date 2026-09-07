<?php

namespace App\Filament\Widgets;

use App\Models\Shop;
use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminTopShopsChart extends ChartWidget
{
    protected ?string $heading = 'Top 10 Shops by Sales';
    protected static ?int $sort = 6;
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('admin');
    }

    protected function getData(): array
    {
        $topShops = OrderItem::select('shop_id', DB::raw('SUM(subtotal) as total_sales'))
            ->whereHas('order', fn($q) => $q->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']))
            ->groupBy('shop_id')
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->with('shop')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Sales (Rp)',
                    'data' => $topShops->pluck('total_sales')->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                ],
            ],
            'labels' => $topShops->map(fn($item) => $item->shop?->name ?? 'Unknown')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
        ];
    }
}
