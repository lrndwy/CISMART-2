<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Ulasan';
    }
}
