<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SellerStockStatusChart extends ChartWidget
{
    protected ?string $heading = 'Product Stock Status';
    protected static ?int $sort = 4;
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

        $outOfStock = Product::whereIn('shop_id', $shopIds)->where('stock', 0)->count();
        $lowStock = Product::whereIn('shop_id', $shopIds)->where('stock', '>', 0)->where('stock', '<=', 10)->count();
        $normalStock = Product::whereIn('shop_id', $shopIds)->where('stock', '>', 10)->where('stock', '<=', 50)->count();
        $highStock = Product::whereIn('shop_id', $shopIds)->where('stock', '>', 50)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => [$outOfStock, $lowStock, $normalStock, $highStock],
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.8)',   // Red for out of stock
                        'rgba(251, 191, 36, 0.8)',  // Yellow for low stock
                        'rgba(59, 130, 246, 0.8)',  // Blue for normal
                        'rgba(16, 185, 129, 0.8)',  // Green for high stock
                    ],
                ],
            ],
            'labels' => ['Out of Stock', 'Low Stock (≤10)', 'Normal (11-50)', 'High Stock (>50)'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
