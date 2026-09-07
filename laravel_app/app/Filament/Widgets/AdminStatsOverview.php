<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\SellerApplication;
use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Models\Category;
use App\Models\MlPrediction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    protected function getStats(): array
    {
        // User stats
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $sellerCount = User::role('seller')->count();

        // Shop stats
        $totalShops = Shop::count();
        $approvedShops = Shop::where('status', 'approved')->count();
        $pendingShops = Shop::where('status', 'pending')->count();

        // Product stats
        $totalProducts = Product::count();
        $publishedProducts = Product::where('status', 'published')->count();
        $outOfStockProducts = Product::where('stock', 0)->count();

        // Order stats
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'delivered')->count();
        $ordersThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $revenueThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->sum('total');

        // Application stats
        $pendingApplications = SellerApplication::where('status', 'pending')->count();

        // ML Prediction stats
        $totalPredictions = MlPrediction::count();
        $predictionsThisMonth = MlPrediction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->description($newUsersThisMonth . ' new this month | ' . $sellerCount . ' sellers')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([7, 12, 8, 15, 11, 18, $newUsersThisMonth])
                ->color('success'),

            Stat::make('Total Shops', number_format($totalShops))
                ->description($approvedShops . ' approved | ' . $pendingShops . ' pending')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->chart([$pendingShops, $approvedShops])
                ->color('info'),

            Stat::make('Total Products', number_format($totalProducts))
                ->description($publishedProducts . ' published | ' . $outOfStockProducts . ' out of stock')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart([120, 150, 180, 200, $totalProducts])
                ->color('warning'),

            Stat::make('Total Orders', number_format($totalOrders))
                ->description($ordersThisMonth . ' this month | ' . $pendingOrders . ' pending')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->chart([5, 10, 15, 12, 18, 20, $ordersThisMonth])
                ->color('primary'),

            Stat::make('Revenue This Month', 'Rp ' . number_format($revenueThisMonth, 0, ',', '.'))
                ->description($completedOrders . ' completed orders')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart([500000, 750000, 1000000, 1250000, $revenueThisMonth])
                ->color('success'),

            Stat::make('ML Predictions', number_format($totalPredictions))
                ->description($predictionsThisMonth . ' new this month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([10, 15, 20, 25, $predictionsThisMonth])
                ->color('info'),

            Stat::make('Categories', number_format(Category::count()))
                ->description('Product categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('gray'),

            Stat::make('Pending Applications', $pendingApplications)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingApplications > 0 ? 'danger' : 'success'),
        ];
    }
}
