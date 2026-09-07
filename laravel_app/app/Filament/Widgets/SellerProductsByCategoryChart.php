<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SellerProductsByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'My Products by Category';

    protected static ?int $sort = 3;

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

        $categories = Category::query()
            ->whereHas('products', fn ($q) => $q->whereIn('shop_id', $shopIds))
            ->withCount([
                'products' => fn ($q) => $q->whereIn('shop_id', $shopIds),
            ])
            ->orderByDesc('products_count')
            ->limit(8)
            ->get();

        $colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(251, 191, 36, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(249, 115, 22, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => $categories->pluck('products_count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $categories->count()),
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
