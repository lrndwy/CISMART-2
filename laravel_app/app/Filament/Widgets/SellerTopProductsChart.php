<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerTopProductsChart extends ChartWidget
{
    protected ?string $heading = 'My Top 10 Products by Sales';
    protected static ?int $sort = 5;
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

        $topProducts = OrderItem::select('product_id', 'product_name', DB::raw('SUM(subtotal) as total_sales'))
            ->whereIn('shop_id', $shopIds)
            ->whereHas('order', fn($q) => $q->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']))
            ->groupBy('product_id', 'product_name')
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Sales (Rp)',
                    'data' => $topProducts->pluck('total_sales')->toArray(),
                    'backgroundColor' => 'rgba(168, 85, 247, 0.8)',
                ],
            ],
            'labels' => $topProducts->pluck('product_name')->map(fn($name) => strlen($name) > 20 ? substr($name, 0, 20) . '...' : $name)->toArray(),
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
