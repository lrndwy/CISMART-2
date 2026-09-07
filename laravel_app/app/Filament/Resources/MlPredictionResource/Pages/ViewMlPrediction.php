<?php

namespace App\Filament\Resources\MlPredictionResource\Pages;

use App\Filament\Resources\MlPredictionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMlPrediction extends ViewRecord
{
    protected static string $resource = MlPredictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Ubah'),
        ];
    }
}
