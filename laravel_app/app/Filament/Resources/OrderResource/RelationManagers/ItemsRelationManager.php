<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'product_name';

    protected static ?string $title = 'Item Pesanan';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // If user is seller, only show items from their shops
                $user = Auth::user();
                if ($user && $user->hasRole('seller')) {
                    $userShopIds = $user->shops()->pluck('id')->toArray();
                    if (!empty($userShopIds)) {
                        $query->whereIn('shop_id', $userShopIds);
                    }
                }
            })
            ->columns([
                ImageColumn::make('product_image')
                    ->label('Gambar')
                    ->square()
                    ->size(60),

                TextColumn::make('product_name')
                    ->label('Produk')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn($record) => "SKU: {$record->product_id}"),

                TextColumn::make('shop_name')
                    ->label('Toko')
                    ->searchable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR'),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->weight('bold')
                    ->color('success'),
            ])
            ->defaultSort('id', 'asc')
            ->paginated(false); // Show all items without pagination
    }
}
