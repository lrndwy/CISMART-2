<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select as FormsSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    // gunakan union types sesuai yang erwartet Filament
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';
    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Produk';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // If user is seller, only show their products
        if (! Auth::user()?->isAdmin()) {
            $userShopIds = Auth::user()?->shops->pluck('id') ?? collect([]);
            $query->whereIn('shop_id', $userShopIds);
        }

        return $query;
    }


    public static function canCreate(): bool
    {
        return Gate::allows('create', Product::class);
    }

    public static function canEdit(Model $record): bool
    {
        return Gate::allows('update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::allows('delete', $record);
    }

    // Filament v4 Schema API
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        FormsSelect::make('shop_id')
                            ->label('Toko')
                            ->options(function () {
                                if (Auth::user()?->isAdmin()) {
                                    return Shop::pluck('name', 'id')->toArray();
                                }
                                return Auth::user()?->shops->pluck('name', 'id')->toArray() ?? [];
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->label("Nama Produk")
                            ->afterStateUpdated(function ($state, callable $set, $record) {
                                // hanya set slug kalau create (record null) atau slug kosong
                                if (! $record) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->readOnly()
                            ->unique(ignoreRecord: true),

                        FormsSelect::make('category_id')
                            ->label('Kategori')
                            ->options(Category::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Detail Produk')
                    ->schema([
                        Textarea::make('description')
                            ->required()
                            ->columnSpanFull()
                            ->label("Deskripsi"),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->maxValue(999999999.99)
                            ->label("Harga"),

                        TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->label("Stok"),

                        TextInput::make('weight_gram')
                            ->label('Berat (gram)')
                            ->numeric()
                            ->suffix('g')
                            ->minValue(1),
                    ])
                    ->columns(3),

                Section::make('Media dan Pengaturan')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Foto Produk')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(5)
                            ->imageEditor()
                            ->directory('products/images')
                            ->disk('public')
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(800)
                            ->imageResizeTargetHeight(800)
                            ->helperText('Upload hingga 5 gambar produk. Gambar pertama akan menjadi gambar utama.')
                            ->columnSpanFull(),

                        Toggle::make('is_featured')
                            ->label('Produk Unggulan'),

                        FormsSelect::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('first_image')
                    ->label('Gambar')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label("Nama")
                    ->limit(30),

                Tables\Columns\TextColumn::make('shop.name')
                    ->label('Shop')
                    ->searchable()
                    ->sortable()
                    ->label("Toko")
                    ->toggleable(isToggledHiddenByDefault: Auth::user()?->isSeller()),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->label("Harga")
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->label("Stok")
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->selectablePlaceholder(false),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label("Dibuat")
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('is_featured')
                    ->query(fn(Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Featured Only'),

                Tables\Filters\Filter::make('out_of_stock')
                    ->query(fn(Builder $query): Builder => $query->where('stock', '<=', 0))
                    ->label('Out of Stock'),
            ])

            // row actions (v4)
            ->recordActions([
                ViewAction::make()->label("Lihat"),
                EditAction::make()->label("Ubah"),
                // Delete action (row)
                DeleteAction::make()->label("Hapus"),
            ])

            // toolbar / bulk actions (v4)
            ->toolbarActions([
                DeleteBulkAction::make(),

                BulkAction::make('publish')
                    ->label('Publish Selected')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            if (Gate::allows('update', $record)) {
                                $record->update(['status' => 'published']);
                            }
                        }
                    })
                    ->requiresConfirmation(),

                BulkAction::make('archive')
                    ->label('Archive Selected')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            if (Gate::allows('update', $record)) {
                                $record->update(['status' => 'archived']);
                            }
                        }
                    })
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['shop', 'category']);
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['name', 'description', 'shop.name'];
    }
}
