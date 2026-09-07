<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class SellerLatestOrders extends BaseWidget
{
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('seller');
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $shopIds = $user->shops()->pluck('id');

        return $table
            ->query(
                OrderItem::query()
                    ->whereIn('shop_id', $shopIds)
                    ->with(['order', 'product', 'shop'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('shop_name')
                    ->label('Shop')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR'),
                Tables\Columns\BadgeColumn::make('order.status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'primary' => 'processing',
                        'success' => fn($state) => in_array($state, ['shipped', 'delivered']),
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->heading('My Latest Orders');
    }
}
