<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Order;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'Riwayat Status';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('from_status')
                    ->label('Dari')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? Order::statusLabelFor($state) : '—'),
                TextColumn::make('to_status')
                    ->label('Ke')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::statusLabelFor($state)),
                TextColumn::make('actor.name')
                    ->label('Oleh')
                    ->placeholder('Sistem'),
                TextColumn::make('actor_role')
                    ->label('Peran')
                    ->placeholder('—'),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}