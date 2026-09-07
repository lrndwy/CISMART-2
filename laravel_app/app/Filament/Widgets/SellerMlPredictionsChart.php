<?php

namespace App\Filament\Widgets;

use App\Models\MlPrediction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SellerMlPredictionsChart extends ChartWidget
{
    protected ?string $heading = 'My UMKM Classification';
    protected static ?int $sort = 6;
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

        $predictions = MlPrediction::whereIn('shop_id', $shopIds)->get();

        if ($predictions->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'No predictions yet',
                        'data' => [1],
                        'backgroundColor' => ['rgba(156, 163, 175, 0.8)'],
                    ],
                ],
                'labels' => ['No Data'],
            ];
        }

        $clusterCounts = [
            'Mikro/Kecil' => $predictions->where('predicted_cluster', 0)->count(),
            'Menengah' => $predictions->where('predicted_cluster', 1)->count(),
            'Besar' => $predictions->where('predicted_cluster', 2)->count(),
        ];

        // Only include clusters with count > 0
        $filteredCounts = collect($clusterCounts)->filter(fn($count) => $count > 0);

        return [
            'datasets' => [
                [
                    'label' => 'UMKM Classification',
                    'data' => $filteredCounts->values()->toArray(),
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.8)',  // Yellow for Mikro
                        'rgba(59, 130, 246, 0.8)',  // Blue for Menengah
                        'rgba(16, 185, 129, 0.8)',  // Green for Besar
                    ],
                ],
            ],
            'labels' => $filteredCounts->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
