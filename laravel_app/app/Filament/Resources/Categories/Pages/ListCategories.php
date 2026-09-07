<?php

namespace App\Filament\Resources\Categories\Pages;

use Illuminate\Contracts\Support\Htmlable;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label("Tambah Kategori"),
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return "Kategori";
    }
}
