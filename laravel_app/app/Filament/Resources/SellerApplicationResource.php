<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SellerApplications\Pages\CreateSellerApplication;
use App\Filament\Resources\SellerApplications\Pages\EditSellerApplication;
use App\Filament\Resources\SellerApplications\Pages\ListSellerApplications;
use App\Filament\Resources\SellerApplications\Pages\ViewSellerApplication;
use App\Models\SellerApplication;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section; // <- Section (v4)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get as UtilitiesGet;
use Illuminate\Support\Facades\Auth;

class SellerApplicationResource extends Resource
{
    protected static ?string $model = SellerApplication::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen';

    protected static ?string $navigationLabel = 'Permohonan Penjual';

    protected static ?int $navigationSort = 2;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                // gunakan Section dari Filament\Schemas\Components
                Section::make('Informasi Pengguna')
                    ->schema([
                        TextInput::make('user.name')
                            ->label('Nama Pengguna')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('user.email')
                            ->label('Email Pengguna')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Section::make('Informasi Bisnis')
                    ->schema([
                        TextInput::make('business_name')
                            ->label('Nama Toko')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('business_type')
                            ->label('Jenis Usaha')
                            ->options([
                                'food' => 'Food & Beverage',
                                'craft' => 'Handicraft',
                                'souvenir' => 'Souvenir',
                                'fashion' => 'Fashion',
                                'agriculture' => 'Agriculture',
                                'technology' => 'Technology',
                                'service' => 'Service',
                                'other' => 'Other',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('business_description')
                            ->label('Deskripsi Toko')
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('business_address')
                            ->label('Alamat Toko')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('business_phone')
                            ->label('No HP Toko')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('business_email')
                            ->label('Email Toko')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Section::make('Review')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending'  => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->live(), // buat form re-render ketika status berubah

                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->visible(fn(UtilitiesGet $get): bool => $get('status') === 'rejected')
                            ->required(fn(UtilitiesGet $get): bool => $get('status') === 'rejected')
                            // hanya dehydrate (simpan) ketika status = rejected
                            ->dehydrated(fn(UtilitiesGet $get): bool => $get('status') === 'rejected'),
                    ]),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('business_name')
                    ->searchable()
                    ->label("Nama Toko")
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('business_type')
                    ->badge()
                    ->label("Jenis Usaha")
                    ->color(fn(?string $state): string => match ($state) {
                        'food' => 'success',
                        'craft' => 'warning',
                        'souvenir' => 'info',
                        default => 'gray',
                    }),

                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label("Status Permohonan")
                    ->color(fn(?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied At')
                    ->dateTime()
                    ->label("Diajukan Pada")
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->label("Disetujui Pada")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            // filters tetap boleh seperti sebelumnya (pakai Tables\Filters\SelectFilter)
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('business_type')
                    ->options([
                        'food' => 'Food & Beverage',
                        'craft' => 'Handicraft',
                        'souvenir' => 'Souvenir',
                        'fashion' => 'Fashion',
                        'agriculture' => 'Agriculture',
                        'technology' => 'Technology',
                        'service' => 'Service',
                        'other' => 'Other',
                    ]),
            ])

            // --- GANTI actions() LAMA → recordActions() ---
            ->recordActions([
                // contoh: view & edit (pakai Action/ViewAction dari Filament\Actions)
                ViewAction::make(),
                EditAction::make(),

                // approve: custom row action
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(SellerApplication $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->action(function (SellerApplication $record): void {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => Auth::id(),
                        ]);

                        // assign role to user (pastikan relation user ada)
                        if ($record->user) {
                            $record->user->assignRole('seller');
                        }

                        Notification::make()
                            ->title('Application approved successfully')
                            ->success()
                            ->send();
                    }),

                // reject: row action yang membuka form modal untuk alasan reject
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn(SellerApplication $record): bool => $record->isPending())
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required(),
                    ])
                    ->action(function (SellerApplication $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Application rejected')
                            ->success()
                            ->send();
                    }),
            ])
            // --- Bulk / header actions: gunakan toolbarActions/headerActions ---
            ->toolbarActions([
                // Delete bulk action built-in
                DeleteBulkAction::make(),
                // contoh: custom bulk action (opsional)
                // BulkAction::make('export')
                //     ->action(fn (Collection $records) => ...),
            ])
            // gunakan default sort seperti semula
            ->defaultSort('created_at', 'desc');
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
            'index' => ListSellerApplications::route('/'),
            'create' => CreateSellerApplication::route('/create'),
            'view' => ViewSellerApplication::route('/{record}'),
            'edit' => EditSellerApplication::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : null;
    }
}
