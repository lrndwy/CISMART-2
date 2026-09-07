<?php

namespace App\Filament\Resources\SellerApplications\Pages;

use App\Filament\Resources\SellerApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSellerApplications extends ListRecords
{
    protected static string $resource = SellerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {

        return 'Permohonan Penjual';
    }
}
