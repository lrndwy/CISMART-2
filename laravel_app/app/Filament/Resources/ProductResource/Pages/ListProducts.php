<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label("Tambah Produk"),
        ];
    }

    public function getTitle(): string
    {

        if (Filament::auth()->user()->isAdmin()) {
            return 'Semua Produk';
        }

        return 'Produk Saya';
    }
}
