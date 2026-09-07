<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\ShopResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShops extends ListRecords
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Toko')
                ->visible(fn () => auth()->user()?->isAdmin() || auth()->user()?->isSeller()),
        ];
    }

    public function getTitle(): string
    {
        return 'Manajemen Toko';
    }
}
