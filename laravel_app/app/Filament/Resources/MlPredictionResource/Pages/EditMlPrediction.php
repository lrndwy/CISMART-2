<?php

namespace App\Filament\Resources\MlPredictionResource\Pages;

use App\Filament\Resources\MlPredictionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditMlPrediction extends EditRecord
{
    protected static string $resource = MlPredictionResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $actions = [
            ViewAction::make()->label('Lihat'),
        ];

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $actions[] = DeleteAction::make()->label('Hapus');
        }

        return $actions;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
