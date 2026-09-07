<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Pesanan Baru')
                ->visible(fn() => Auth::check() && Auth::user()->isAdmin()),
        ];
    }

    public function getTitle(): string
    {
        return 'Daftar Pesanan';
    }
}
