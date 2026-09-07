<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // gunakan tipe sesuai v4 supaya analyzer tidak mengeluh
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if ($user && Gate::has('viewAny', User::class)) {
            return Gate::allows('viewAny', User::class);
        }

        return (bool) ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
    }

    // Filament v4 Schema API: signature returns Schema
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Akun')
                    ->description('Detail informasi pengguna')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        // Password pada create
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            // hanya disimpan jika ada value
                            ->dehydrated(fn($state) => filled($state))
                            // hash sebelum disimpan
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->visible(fn(string $context): bool => $context === 'create')
                            ->columnSpanFull(),

                        // Password edit: optional, hanya tersimpan jika diisi
                        TextInput::make('password_edit')
                            ->label('Password Baru')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->visible(fn(string $context): bool => $context === 'edit')
                            ->helperText('Kosongkan jika tidak ingin mengubah password')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Informasi Kontak')
                    ->description('Detail kontak pengguna')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->placeholder('+62...')
                            ->columnSpanFull(),

                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Diverifikasi Pada')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Pengaturan Role & Permissions')
                    ->description('Kelola role dan permission pengguna')
                    ->schema([
                        // CheckboxList cocok untuk BelongsToMany (roles)
                        CheckboxList::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->options(Role::pluck('name', 'id'))
                            ->columns(1)
                            ->columnSpanFull(),

                        CheckboxList::make('permissions')
                            ->label('Permission')
                            ->relationship('permissions', 'name')
                            ->options(Permission::pluck('name', 'id'))
                            ->columns(2)
                            ->columnSpanFull()
                            ->helperText('Permission spesifik yang diberikan kepada pengguna'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->copyableState(fn(string $state): string => $state),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'admin' => 'danger',
                        'seller' => 'warning',
                        'buyer' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter by Role')
                    ->relationship('roles', 'name')
                    ->options(Role::pluck('name', 'id')),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->column('email_verified_at'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dibuat Dari'),
                        DatePicker::make('created_until')
                            ->label('Dibuat Hingga'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn(Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn(Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            // row actions (v4)
            ->recordActions([
                EditAction::make()->label("Ubah"),
                DeleteAction::make()->label("Hapus")
                    ->requiresConfirmation()
                    ->modalHeading('Hapus User')
                    ->modalDescription('Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            // toolbar / bulk actions (v4)
            ->toolbarActions([
                DeleteBulkAction::make()->label("Hapus")
                    ->requiresConfirmation()
                    ->modalHeading('Hapus User')
                    ->modalDescription('Apakah Anda yakin ingin menghapus user yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'permissions']);
    }
}
