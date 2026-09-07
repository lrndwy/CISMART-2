<?php

namespace App\Filament\Resources\MlPredictionResource\Pages;

use App\Filament\Resources\MlPredictionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMlPredictions extends ListRecords
{
    protected static string $resource = MlPredictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Prediksi Baru'),
        ];
    }
}
