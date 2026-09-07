<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MlPredictionResource\Pages;
use App\Models\MlPrediction;
use App\Models\Shop;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select as FormsSelect;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MlPredictionResource extends Resource
{
    protected static ?string $model = MlPrediction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Machine Learning';

    protected static ?string $navigationLabel = 'Prediksi UMKM';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['user', 'shop']);

        // If user is not admin, only show their predictions
        $user = Auth::user();
        if ($user && method_exists($user, 'isAdmin') && !$user->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();

        return $schema
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        FormsSelect::make('shop_id')
                            ->label('Toko (Opsional)')
                            ->options(function () use ($user, $isAdmin) {
                                if ($isAdmin) {
                                    return Shop::pluck('name', 'id')->toArray();
                                }
                                return $user?->shops->pluck('name', 'id')->toArray() ?? [];
                            })
                            ->searchable()
                            ->preload(),

                        FormsSelect::make('prediction_type')
                            ->label('Tipe Prediksi')
                            ->options([
                                'single' => 'Single',
                                'batch' => 'Batch',
                            ])
                            ->default('single')
                            ->required(),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Data Keuangan')
                    ->description('Masukkan data keuangan UMKM dalam Rupiah')
                    ->schema([
                        TextInput::make('omzet')
                            ->label('Omzet (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(0),

                        TextInput::make('aset')
                            ->label('Total Aset (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(0),

                        TextInput::make('modal_kerja')
                            ->label('Modal Kerja (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(0),

                        TextInput::make('jumlah_investasi')
                            ->label('Jumlah Investasi (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->minValue(0),

                        TextInput::make('bangunan_gedung')
                            ->label('Bangunan/Gedung (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('mesin_peralatan')
                            ->label('Mesin/Peralatan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('mesin_peralatan_impor')
                            ->label('Mesin/Peralatan Impor (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('pembelian_pematangan_tanah')
                            ->label('Pembelian/Pematangan Tanah (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('lain_lain')
                            ->label('Lain-lain (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(3),

                Section::make('Data Operasional')
                    ->schema([
                        TextInput::make('jumlah_tenaga_kerja')
                            ->label('Jumlah Tenaga Kerja')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),

                        TextInput::make('tki')
                            ->label('Tenaga Kerja Indonesia (TKI)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        FormsSelect::make('jenis_perusahaan')
                            ->label('Jenis Perusahaan')
                            ->options([
                                'PT' => 'PT',
                                'CV' => 'CV',
                                'UD' => 'UD',
                                'Perorangan' => 'Perorangan',
                                'Koperasi' => 'Koperasi',
                                'Firma' => 'Firma',
                            ])
                            ->required()
                            ->searchable(),

                        FormsSelect::make('risiko_proyek')
                            ->label('Risiko Proyek')
                            ->options([
                                'Rendah' => 'Rendah',
                                'Sedang' => 'Sedang',
                                'Tinggi' => 'Tinggi',
                            ])
                            ->required(),

                        FormsSelect::make('skala_usaha')
                            ->label('Skala Usaha')
                            ->options([
                                'Mikro' => 'Mikro',
                                'Kecil' => 'Kecil',
                                'Menengah' => 'Menengah',
                            ])
                            ->required(),

                        FormsSelect::make('status_penanaman_modal')
                            ->label('Status Penanaman Modal')
                            ->options([
                                'PMDN' => 'PMDN',
                                'PMA' => 'PMA',
                                'Non-Fasilitas' => 'Non-Fasilitas',
                            ])
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Lokasi & Sektor')
                    ->schema([
                        TextInput::make('kecamatan_usaha')
                            ->label('Kecamatan Usaha')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('kelurahan_usaha')
                            ->label('Kelurahan Usaha')
                            ->required()
                            ->maxLength(255),

                        FormsSelect::make('kl_sektor_pembina')
                            ->label('Sektor Pembina')
                            ->options([
                                'Pangan' => 'Pangan',
                                'Sandang' => 'Sandang',
                                'Kimia & Bahan Bangunan' => 'Kimia & Bahan Bangunan',
                                'Logam & Mesin' => 'Logam & Mesin',
                                'Aneka' => 'Aneka',
                            ])
                            ->required()
                            ->searchable(),

                        TextInput::make('judul_kbli')
                            ->label('Judul KBLI')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Klasifikasi Baku Lapangan Usaha Indonesia'),
                    ])
                    ->columns(2),

                Section::make('Hasil Prediksi')
                    ->description('Diisi otomatis setelah prediksi dari ML Service')
                    ->schema([
                        FormsSelect::make('predicted_cluster')
                            ->label('Cluster Prediksi')
                            ->options([
                                0 => 'Cluster A - UMKM Skala Kecil',
                                1 => 'Cluster B - UMKM Skala Menengah',
                                2 => 'Cluster C - UMKM Skala Besar',
                            ])
                            ->disabled(),

                        TextInput::make('confidence')
                            ->label('Confidence Score')
                            ->numeric()
                            ->disabled()
                            ->suffix('%')
                            ->helperText('Tingkat keyakinan model (0-1)'),

                        TextInput::make('model_version')
                            ->label('Model Version')
                            ->disabled()
                            ->maxLength(50),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->hidden(fn($record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('shop.name')
                    ->label('Toko')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->default('-'),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: function () {
                        $user = Auth::user();
                        return !($user && method_exists($user, 'isAdmin') && $user->isAdmin());
                    }),

                TextColumn::make('omzet')
                    ->label('Omzet')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('jumlah_tenaga_kerja')
                    ->label('Tenaga Kerja')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('skala_usaha')
                    ->label('Skala Usaha')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Mikro' => 'gray',
                        'Kecil' => 'info',
                        'Menengah' => 'success',
                        default => 'gray',
                    }),

                BadgeColumn::make('predicted_cluster')
                    ->label('Cluster')
                    ->formatStateUsing(fn($state): string => match ($state) {
                        0 => 'Cluster A',
                        1 => 'Cluster B',
                        2 => 'Cluster C',
                        default => 'N/A',
                    })
                    ->colors([
                        'warning' => 0,
                        'info' => 1,
                        'success' => 2,
                    ]),

                TextColumn::make('cluster_description')
                    ->label('Kategori Cluster')
                    ->wrap()
                    ->limit(80)
                    ->tooltip(fn(MlPrediction $record): ?string => $record->cluster_description)
                    ->placeholder('Belum ada prediksi')
                    ->toggleable()
                    ->searchable(false),

                TextColumn::make('confidence')
                    ->label('Confidence')
                    ->formatStateUsing(fn($state) => $state ? number_format($state * 100, 2) . '%' : '-')
                    ->sortable(),

                TextColumn::make('prediction_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'single' => 'info',
                        'batch' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('predicted_cluster')
                    ->label('Cluster')
                    ->options([
                        0 => 'Cluster A - Kecil',
                        1 => 'Cluster B - Menengah',
                        2 => 'Cluster C - Besar',
                    ]),

                SelectFilter::make('skala_usaha')
                    ->label('Skala Usaha')
                    ->options([
                        'Mikro' => 'Mikro',
                        'Kecil' => 'Kecil',
                        'Menengah' => 'Menengah',
                    ]),

                SelectFilter::make('prediction_type')
                    ->label('Tipe Prediksi')
                    ->options([
                        'single' => 'Single',
                        'batch' => 'Batch',
                    ]),
            ])
            ->actions([
                ViewAction::make()->label('Lihat'),
                EditAction::make()->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Hapus')
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
                    }),
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
            'index' => Pages\ListMlPredictions::route('/'),
            'create' => Pages\CreateMlPrediction::route('/create'),
            'view' => Pages\ViewMlPrediction::route('/{record}'),
            'edit' => Pages\EditMlPrediction::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $query = MlPrediction::query();

        // If user is not admin, only count their predictions
        $user = Auth::user();
        if ($user && method_exists($user, 'isAdmin') && !$user->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $count = $query->where('created_at', '>=', now()->subDays(7))->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
