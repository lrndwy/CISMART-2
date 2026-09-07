<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Kategori';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if ($user && Gate::has('viewAny', Category::class)) {
            return Gate::allows('viewAny', Category::class);
        }

        return (bool) ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
    }

    // Filament v4 Schema API
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Kategori')
                    ->schema([
                        // Flag untuk menandai kalau user sudah mengubah slug sendiri.
                        Hidden::make('slug_manually_changed')
                            ->default(false)
                            ->dehydrated(false), // jangan submit ke DB
                        TextInput::make('name')
                            ->required()
                            ->lazy() // <--- sync saat blur (mengurangi re-render saat mengetik cepat)
                            ->maxLength(255)
                            ->afterStateUpdated(function ($state, callable $set, $get, $record = null) {
                                // auto-set slug hanya kalau:
                                // - record baru (create), atau slug masih kosong
                                // - DAN user belum mengedit slug sendiri
                                if ((! $record || empty($get('slug'))) && ! $get('slug_manually_changed')) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            })
                            ->label("Nama"),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Jika user mengubah slug manual, tandai flag supaya auto-generator tidak menimpa
                                $set('slug_manually_changed', true);
                            })
                            ->readOnly()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->label("Deskripsi"),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label("Nama"),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Jumlah Produk')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label("Ubah"),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()->label("Hapus"),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
