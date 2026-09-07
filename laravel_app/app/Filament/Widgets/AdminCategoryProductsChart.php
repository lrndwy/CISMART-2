<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class AdminCategoryProductsChart extends ChartWidget
{
    protected ?string $heading = 'Products by Category';
    protected static ?int $sort = 5;
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('admin');
    }

    protected function getData(): array
    {
        $categories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => $categories->pluck('products_count')->toArray(),
                    'backgroundColor' => 'rgba(168, 85, 247, 0.8)',
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
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
