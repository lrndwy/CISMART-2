<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Shop;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return [
                \App\Filament\Widgets\AdminStatsOverview::class,
            ];
        }

        if (method_exists($user, 'isSeller') && $user->isSeller()) {
            return [
                \App\Filament\Widgets\SellerStatsOverview::class,
            ];
        }

        return [];
    }

    public function getColumns(): array|int
    {
        return 2;
    }
}
