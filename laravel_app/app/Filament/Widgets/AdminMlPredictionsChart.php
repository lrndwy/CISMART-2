<?php

namespace App\Filament\Widgets;

use App\Models\MlPrediction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class AdminMlPredictionsChart extends ChartWidget
{
    protected ?string $heading = 'ML Predictions by Cluster';
    protected static ?int $sort = 4;
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('admin');
    }

    protected function getData(): array
    {
        $clusterCounts = [
            'Mikro/Kecil' => MlPrediction::where('predicted_cluster', 0)->count(),
            'Menengah' => MlPrediction::where('predicted_cluster', 1)->count(),
            'Besar' => MlPrediction::where('predicted_cluster', 2)->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'UMKM by Cluster',
                    'data' => array_values($clusterCounts),
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.8)',  // Yellow for Mikro
                        'rgba(59, 130, 246, 0.8)',  // Blue for Menengah
                        'rgba(16, 185, 129, 0.8)',  // Green for Besar
                    ],
                ],
            ],
            'labels' => array_keys($clusterCounts),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
