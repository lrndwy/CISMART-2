<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MlPrediction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SellerStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && method_exists($user, 'isSeller') && $user->isSeller();
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $shopIds = $user->shops()->pluck('id');

        // Shop stats
        $totalShops = $user->shops()->count();
        $approvedShops = $user->shops()->where('status', 'approved')->count();

        // Product stats
        $totalProducts = Product::whereIn('shop_id', $shopIds)->count();
        $publishedProducts = Product::whereIn('shop_id', $shopIds)
            ->where('status', 'published')
            ->count();
        $draftProducts = Product::whereIn('shop_id', $shopIds)
            ->where('status', 'draft')
            ->count();
        $outOfStock = Product::whereIn('shop_id', $shopIds)
            ->where('stock', 0)
            ->count();
        $lowStock = Product::whereIn('shop_id', $shopIds)
            ->where('stock', '>', 0)
            ->where('stock', '<=', 10)
            ->count();

        // Order stats
        $totalOrders = OrderItem::whereIn('shop_id', $shopIds)
            ->distinct('order_id')
            ->count('order_id');
        $pendingOrders = OrderItem::whereIn('shop_id', $shopIds)
            ->whereHas('order', fn($q) => $q->where('status', 'pending'))
            ->distinct('order_id')
            ->count('order_id');
        $completedOrders = OrderItem::whereIn('shop_id', $shopIds)
            ->whereHas('order', fn($q) => $q->where('status', 'delivered'))
            ->distinct('order_id')
            ->count('order_id');
        $ordersThisMonth = OrderItem::whereIn('shop_id', $shopIds)
            ->whereHas('order', fn($q) => $q->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year))
            ->distinct('order_id')
            ->count('order_id');

        // Revenue stats
        $totalRevenue = OrderItem::whereIn('shop_id', $shopIds)
            ->whereHas('order', fn($q) => $q->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']))
            ->sum('subtotal');
        $revenueThisMonth = OrderItem::whereIn('shop_id', $shopIds)
            ->whereHas('order', fn($q) => $q->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']))
            ->sum('subtotal');

        // ML Predictions for seller's shops
        $mlPredictions = MlPrediction::whereIn('shop_id', $shopIds)->count();

        return [
            Stat::make('My Shops', $totalShops)
                ->description($approvedShops . ' approved')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success'),

            Stat::make('Total Products', number_format($totalProducts))
                ->description($publishedProducts . ' published | ' . $draftProducts . ' draft')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart([80, 100, 120, 150, $totalProducts])
                ->color('info'),

            Stat::make('Stock Alert', $outOfStock + $lowStock)
                ->description($outOfStock . ' out of stock | ' . $lowStock . ' low stock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->chart([$outOfStock, $lowStock])
                ->color($outOfStock > 0 ? 'danger' : 'warning'),

            Stat::make('Total Orders', number_format($totalOrders))
                ->description($ordersThisMonth . ' this month | ' . $pendingOrders . ' pending')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->chart([5, 8, 12, 15, $ordersThisMonth])
                ->color('primary'),

            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Rp ' . number_format($revenueThisMonth, 0, ',', '.') . ' this month')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart([300000, 500000, 750000, 1000000, $revenueThisMonth])
                ->color('success'),

            Stat::make('Completed Orders', number_format($completedOrders))
                ->description('Successfully delivered')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('ML Predictions', number_format($mlPredictions))
                ->description('Business forecasts')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Avg. Order Value', $totalOrders > 0 ? 'Rp ' . number_format($totalRevenue / $totalOrders, 0, ',', '.') : 'Rp 0')
                ->description('Per order')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
