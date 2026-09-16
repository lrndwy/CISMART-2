<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Pages\ViewReview;
use App\Models\Review;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen';

    protected static ?string $navigationLabel = 'Ulasan';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return (bool) Auth::user()?->isAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            Section::make('Ulasan')->schema([
                TextInput::make('shop.name')->label('Toko')->disabled()->dehydrated(false),
                TextInput::make('user.name')->label('Pembeli')->disabled()->dehydrated(false),
                TextInput::make('rating')->label('Rating')->disabled()->dehydrated(false),
                Textarea::make('comment')->label('Ulasan')->disabled()->dehydrated(false)->columnSpanFull(),
                TextInput::make('created_at')->label('Dibuat')->disabled()->dehydrated(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shop.name')->label('Toko')->searchable(),
                TextColumn::make('user.name')->label('Pembeli')->searchable(),
                TextColumn::make('rating')->label('Rating')->badge()
                    ->formatStateUsing(fn (int $state): string => $state.' / 5')
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('comment')->label('Ulasan')->limit(60)->wrap(),
                TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('shop_id')->label('Toko')->relationship('shop', 'name')->searchable(),
                SelectFilter::make('rating')->label('Rating')
                    ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'view' => ViewReview::route('/{record}'),
        ];
    }
}
