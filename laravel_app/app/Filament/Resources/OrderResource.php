<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pesanan';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['user', 'items', 'items.product', 'items.shop']);

        // If user is seller, only show orders containing their products
        if (Auth::check() && ! Auth::user()->isAdmin() && Auth::user()->isSeller()) {
            $userShopIds = Auth::user()->shops->pluck('id')->toArray();

            $query->whereHas('items', function ($q) use ($userShopIds) {
                $q->whereIn('shop_id', $userShopIds);
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Pesanan')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Nomor Pesanan')
                            ->disabled()
                            ->default('Auto-generated'),

                        Select::make('user_id')
                            ->label('Pembeli')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null || ! Auth::user()->isAdmin())
                            ->helperText('User yang melakukan pemesanan (pelanggan)'),

                        Select::make('status')
                            ->label('Status Pesanan')
                            ->options([
                                'pending' => 'Menunggu Konfirmasi',
                                'confirmed' => 'Dikonfirmasi',
                                'processing' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'whatsapp-order' => 'Pesan via WhatsApp',
                                'bank-transfer' => 'Transfer Bank',
                                'cod' => 'Bayar di Tempat',
                                'qris' => 'QRIS',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('shipping_method')
                            ->label('Metode Pengiriman')
                            ->options([
                                'delivery' => 'Kirim ke lokasi',
                                'pickup' => 'Ambil di toko',
                                'regular' => 'Reguler (3-5 hari)',
                                'express' => 'Express (1-2 hari)',
                                'same-day' => 'Same Day (Cilacap)',
                                'none' => 'Tidak ada pengiriman',
                            ])
                            ->native(false),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])
                    ->columns(2),

                Section::make('Alamat Pengiriman')
                    ->schema([
                        TextInput::make('shipping_name')
                            ->label('Nama Penerima')
                            ->required()
                            ->helperText('Orang yang akan menerima paket (bisa berbeda dari pembeli)'),

                        TextInput::make('shipping_phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->required(),

                        Textarea::make('shipping_address')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3),

                        TextInput::make('shipping_city')
                            ->label('Kota')
                            ->required(),

                        TextInput::make('shipping_postal_code')
                            ->label('Kode Pos'),

                        Textarea::make('shipping_notes')
                            ->label('Catatan Pengiriman')
                            ->rows(2),

                        TextInput::make('delivery_latitude')
                            ->label('Latitude pembeli')
                            ->disabled()
                            ->visible(fn ($record) => filled($record?->delivery_latitude)),

                        TextInput::make('delivery_longitude')
                            ->label('Longitude pembeli')
                            ->disabled()
                            ->visible(fn ($record) => filled($record?->delivery_longitude)),

                        TextInput::make('delivery_distance_meters')
                            ->label('Jarak dari toko')
                            ->disabled()
                            ->suffix('meter')
                            ->visible(fn ($record) => filled($record?->delivery_distance_meters)),

                        Placeholder::make('delivery_map_link')
                            ->label('Lokasi di peta')
                            ->content(function ($record) {
                                if (! filled($record?->delivery_latitude) || ! filled($record?->delivery_longitude)) {
                                    return '-';
                                }

                                $lat = $record->delivery_latitude;
                                $lng = $record->delivery_longitude;
                                $url = 'https://www.google.com/maps?q='.$lat.','.$lng;

                                return new HtmlString(
                                    '<a href="'.e($url).'" target="_blank" rel="noopener" class="text-primary-600 underline">Buka lokasi pembeli</a>'
                                );
                            })
                            ->visible(fn ($record) => filled($record?->delivery_latitude))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Informasi Pembayaran')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),

                        TextInput::make('shipping_cost')
                            ->label('Ongkos Kirim')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),

                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),

                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'unpaid' => 'Belum dibayar',
                                'awaiting_verification' => 'Menunggu verifikasi',
                                'verified' => 'Terverifikasi',
                                'rejected' => 'Ditolak',
                                'not_required' => 'Tidak memakai QRIS',
                            ])
                            ->disabled(),

                        Select::make('qris_type')
                            ->label('Jenis QRIS')
                            ->options([
                                'static' => 'Statis',
                                'dynamic' => 'Dinamis',
                            ])
                            ->disabled()
                            ->visible(fn ($record) => $record?->payment_method === 'qris'),

                        FileUpload::make('payment_proof_path')
                            ->label('Bukti Pembayaran')
                            ->disk('public')
                            ->image()
                            ->downloadable()
                            ->openable()
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn ($record) => filled($record?->payment_proof_path)),

                        Textarea::make('payment_rejection_note')
                            ->label('Catatan penolakan')
                            ->disabled()
                            ->visible(fn ($record) => filled($record?->payment_rejection_note))
                            ->columnSpanFull(),

                        DateTimePicker::make('confirmed_at')
                            ->label('Dikonfirmasi Pada'),

                        DateTimePicker::make('cancelled_at')
                            ->label('Dibatalkan Pada'),

                        DateTimePicker::make('whatsapp_sent_at')
                            ->label('WhatsApp Dikirim Pada'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Nomor Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label('Pembeli')
                    ->description(fn (Order $record): string => $record->user->email ?? '')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shipping_name')
                    ->label('Penerima')
                    ->description(fn (Order $record): string => $record->shipping_phone)
                    ->searchable(),

                TextColumn::make('items.shop.name')
                    ->label('Toko')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'primary' => 'processing',
                        'success' => fn ($state) => in_array($state, ['shipped', 'delivered']),
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'delivered' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                BadgeColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'whatsapp-order' => 'WhatsApp',
                        'bank-transfer' => 'Transfer',
                        'cod' => 'COD',
                        'qris' => 'QRIS',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'whatsapp-order',
                        'info' => 'bank-transfer',
                        'warning' => 'cod',
                        'primary' => 'qris',
                    ]),

                BadgeColumn::make('payment_status')
                    ->label('Bayar')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'unpaid' => 'Belum bayar',
                        'awaiting_verification' => 'Cek bukti',
                        'verified' => 'Lunas',
                        'rejected' => 'Ditolak',
                        'not_required' => '-',
                        default => $state ?? '-',
                    })
                    ->colors([
                        'gray' => 'unpaid',
                        'warning' => 'awaiting_verification',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ]),

                TextColumn::make('created_at')
                    ->label('Tanggal Pesanan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Konfirmasi',
                        'confirmed' => 'Dikonfirmasi',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'delivered' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'whatsapp-order' => 'Pesan via WhatsApp',
                        'bank-transfer' => 'Transfer Bank',
                        'cod' => 'Bayar di Tempat',
                        'qris' => 'QRIS',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn () => Auth::check() && Auth::user()->isAdmin()),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->visible(fn () => Auth::check() && Auth::user()->isAdmin()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $query = Order::where(function ($q) {
            $q->where('status', 'pending')
                ->orWhere('payment_status', 'awaiting_verification');
        });

        // If user is seller, only count orders containing their products
        if (Auth::check() && ! Auth::user()->isAdmin() && Auth::user()->isSeller()) {
            $userShopIds = Auth::user()->shops->pluck('id')->toArray();

            $query->whereHas('items', function ($q) use ($userShopIds) {
                $q->whereIn('shop_id', $userShopIds);
            });
        }

        $count = $query->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
