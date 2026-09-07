<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Shops\Pages\CreateShop;
use App\Filament\Resources\Shops\Pages\EditShop;
use App\Filament\Resources\Shops\Pages\ListShops;
use App\Models\Shop;
use App\Services\QrisImageDecoder;
use App\Services\QrisPayload;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select as FormsSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen';

    protected static ?string $navigationLabel = 'Toko';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        // Prefer Gate if policy exists, fallback to role checks
        $user = Auth::user();
        if ($user && Gate::has('viewAny', Shop::class)) {
            return Gate::allows('viewAny', Shop::class);
        }

        return (bool) ($user && (method_exists($user, 'isAdmin') && $user->isAdmin() || method_exists($user, 'isSeller') && $user->isSeller()));
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        // Prefer policy if exists
        if ($user && Gate::has('create', Shop::class)) {
            return Gate::allows('create', Shop::class);
        }

        // Admin and sellers can create a shop. Sellers only create their own.
        return (bool) ($user && (
            (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isSeller') && $user->isSeller())
        ));
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        if ($user && Gate::has('update', $record)) {
            return Gate::allows('update', $record);
        }

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (method_exists($user, 'isSeller') && $user->isSeller()) {
            return $record->user_id === $user->id;
        }

        return false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        if ($user && Gate::has('delete', $record)) {
            return Gate::allows('delete', $record);
        }

        return (bool) ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('user');

        $user = Auth::user();

        // Sellers only see their own shops
        if ($user && method_exists($user, 'isSeller') && $user->isSeller()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    // Filament v4 Schema API
    public static function form(Schema $schema): Schema
    {
        $user = Auth::user();

        return $schema
            ->schema([
                Section::make('Informasi Toko')
                    ->schema([
                        // Relationship select for owner
                        FormsSelect::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->visible(fn () => $user && method_exists($user, 'isAdmin') && $user->isAdmin())
                            ->default(fn () => $user?->id),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama')
                            ->live(onBlur: true) // sync saat blur (lazy)
                            ->afterStateUpdated(function ($state, callable $set, $get, $record = null) {
                                // generate slug hanya saat create (no $record) atau slug kosong
                                if (! $record || empty($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->readOnly()
                            ->maxLength(255)
                            ->label('Slug'),

                        Textarea::make('description')
                            ->rows(4)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->label('Deskripsi'),
                    ])
                    ->columns(2),

                Section::make('Informasi Kontak')
                    ->schema([
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->label('Nomor HP'),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->rows(3)
                            ->maxLength(500)
                            ->label('Alamat Toko')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('logo')
                            ->image()
                            ->imageEditor()
                            ->directory('shops/logos')
                            ->disk('public')
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth(400)
                            ->imageResizeTargetHeight(400),

                        FileUpload::make('banner')
                            ->image()
                            ->imageEditor()
                            ->directory('shops/banners')
                            ->disk('public')
                            ->maxSize(5120)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth(1200)
                            ->imageResizeTargetHeight(675)
                            ->columnSpanFull(),
                    ]),

                Section::make('Lokasi Toko')
                    ->description('Klik pada peta untuk memilih lokasi atau masukkan koordinat secara manual')
                    ->schema([
                        View::make('filament.forms.components.map-picker')
                            ->columnSpanFull(),

                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('e.g., -6.200000')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get) {
                                // Trigger map update via Alpine.js event
                                if ($state && $get('longitude')) {
                                    // Map akan di-update otomatis via x-model
                                }
                            }),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.00000001)
                            ->placeholder('e.g., 106.816666')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get) {
                                // Trigger map update via Alpine.js event
                                if ($state && $get('latitude')) {
                                    // Map akan di-update otomatis via x-model
                                }
                            }),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Pengiriman')
                    ->description('Ongkir dihitung dari jarak peta pembeli ke lokasi toko. Contoh: setiap 100 meter = Rp 2.000, maka 350 meter = 4 satuan = Rp 8.000.')
                    ->schema([
                        Toggle::make('delivery_enabled')
                            ->label('Aktifkan pengiriman ke lokasi pembeli')
                            ->helperText('Pembeli menandai titik di peta saat checkout. Pastikan lokasi toko sudah diisi.')
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('delivery_unit_meters')
                            ->label('Satuan jarak')
                            ->numeric()
                            ->minValue(50)
                            ->step(50)
                            ->default(100)
                            ->suffix('meter')
                            ->required(fn (Get $get): bool => (bool) $get('delivery_enabled'))
                            ->helperText('Tarif dihitung per satuan ini. 100 = per 100 meter.')
                            ->visible(fn (Get $get): bool => (bool) $get('delivery_enabled')),

                        TextInput::make('delivery_rate')
                            ->label('Tarif per satuan')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->default(0)
                            ->required(fn (Get $get): bool => (bool) $get('delivery_enabled'))
                            ->helperText('Nominal rupiah untuk setiap satuan jarak.')
                            ->visible(fn (Get $get): bool => (bool) $get('delivery_enabled')),

                        Placeholder::make('delivery_preview')
                            ->label('Contoh perhitungan')
                            ->content(function (Get $get): string {
                                $unit = (int) $get('delivery_unit_meters');
                                $rate = (int) $get('delivery_rate');

                                if ($unit <= 0 || $rate <= 0) {
                                    return 'Isi satuan jarak dan tarif untuk melihat contoh.';
                                }

                                $sampleMeters = 350;
                                $units = (int) ceil($sampleMeters / $unit);
                                $cost = $units * $rate;

                                return "Jarak {$sampleMeters} m = {$units} satuan × Rp ".number_format($rate, 0, ',', '.').' = Rp '.number_format($cost, 0, ',', '.');
                            })
                            ->visible(fn (Get $get): bool => (bool) $get('delivery_enabled'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Pembayaran QRIS')
                    ->description('Unggah foto QRIS statis dari aplikasi bank atau e-wallet. Sistem akan membaca kode di dalam gambar. Pembeli membayar lewat QR ini, lalu mengunggah bukti untuk Anda verifikasi.')
                    ->schema([
                        FileUpload::make('qris_image')
                            ->label('Gambar QRIS Statis')
                            ->image()
                            ->directory('shops/qris')
                            ->disk('public')
                            ->maxSize(4096)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Ambil screenshot QRIS statis toko Anda, lalu unggah di sini. Hindari foto buram.')
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (blank($state)) {
                                    $set('qris_static_payload', null);

                                    return;
                                }

                                try {
                                    $set('qris_static_payload', QrisImageDecoder::decodeFromUpload($state));
                                } catch (\Throwable $e) {
                                    $set('qris_static_payload', null);

                                    Notification::make()
                                        ->danger()
                                        ->title('Gagal membaca QRIS')
                                        ->body($e->getMessage())
                                        ->send();
                                }
                            })
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get): void {
                                        if (blank($value)) {
                                            return;
                                        }

                                        if (! QrisPayload::isValid((string) $get('qris_static_payload'))) {
                                            $fail('Gambar QRIS tidak terbaca atau tidak valid. Unggah ulang foto QRIS yang lebih jelas.');
                                        }
                                    };
                                },
                            ]),

                        Hidden::make('qris_static_payload')
                            ->dehydrated()
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail): void {
                                        if (filled($value) && ! QrisPayload::isValid((string) $value)) {
                                            $fail('Payload QRIS tidak valid.');
                                        }
                                    };
                                },
                            ]),

                        Placeholder::make('qris_status')
                            ->label('Status pembacaan')
                            ->content(function (Get $get): HtmlString {
                                $payload = $get('qris_static_payload');

                                if (filled($payload) && QrisPayload::isValid((string) $payload)) {
                                    $merchant = e(QrisPayload::merchantName((string) $payload) ?? 'QRIS valid');

                                    return new HtmlString(
                                        '<span class="text-sm text-green-700 font-medium">QRIS terbaca — Merchant: '.$merchant.'</span>'
                                    );
                                }

                                return new HtmlString(
                                    '<span class="text-sm text-stone-500">Belum ada QRIS. Unggah gambar QRIS toko Anda.</span>'
                                );
                            })
                            ->columnSpanFull(),

                        FormsSelect::make('qris_mode')
                            ->label('Jenis QR yang ditampilkan ke pembeli')
                            ->options([
                                'static' => 'QRIS Statis — pembeli mengisi nominal sendiri',
                                'dynamic' => 'QRIS Dinamis — nominal otomatis sesuai total belanja',
                            ])
                            ->default('static')
                            ->required()
                            ->native(false)
                            ->helperText('Dinamis dibuat dari QRIS yang diunggah, dengan nominal dan nomor pesanan tertanam di QR.'),
                    ])
                    ->columns(2)
                    ->collapsed(fn ($record) => blank($record?->qris_static_payload) && blank($record?->qris_image)),

                Section::make('Status')
                    ->visible(fn () => $user && method_exists($user, 'isAdmin') && $user->isAdmin())
                    ->schema([
                        FormsSelect::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'suspended' => 'Suspended',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/shop-placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('Nama'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Jumlah Produk')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'suspended' => 'danger',
                        'rejected' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'suspended' => 'Suspended',
                        'rejected' => 'Rejected',
                    ]),
            ])
            // row actions (v4)
            ->recordActions([
                EditAction::make()->label('Ubah'),
                DeleteAction::make()->label('Hapus'),
            ])
            // toolbar / bulk actions (v4)
            ->toolbarActions([
                DeleteBulkAction::make()->label('Hapus'),
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
            'index' => ListShops::route('/'),
            'create' => CreateShop::route('/create'),
            'edit' => EditShop::route('/{record}/edit'),
        ];
    }

    /**
     * Pastikan payload QRIS tersinkron dari gambar yang diunggah.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function syncQrisPayloadFromImage(array $data): array
    {
        if (! array_key_exists('qris_image', $data)) {
            return $data;
        }

        if (blank($data['qris_image'])) {
            $data['qris_static_payload'] = null;

            return $data;
        }

        $existing = $data['qris_static_payload'] ?? null;

        if (filled($existing) && QrisPayload::isValid((string) $existing)) {
            return $data;
        }

        $data['qris_static_payload'] = QrisImageDecoder::decodeFromUpload($data['qris_image']);

        return $data;
    }
}
