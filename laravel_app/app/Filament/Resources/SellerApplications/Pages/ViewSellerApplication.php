<?php

namespace App\Filament\Resources\SellerApplications\Pages;

use App\Filament\Resources\SellerApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSellerApplication extends ViewRecord
{
    protected static string $resource = SellerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
