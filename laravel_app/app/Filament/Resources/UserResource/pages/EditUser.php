<?php
// app/Filament/Resources/UserResource/Pages/EditUser.php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus User')
                ->modalDescription('Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'User berhasil diperbarui';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jika password_edit tidak diisi, hapus dari data
        if (empty($data['password_edit'])) {
            unset($data['password_edit']);
        } else {
            // Ganti password dengan password_edit
            $data['password'] = $data['password_edit'];
            unset($data['password_edit']);
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        // Sinkronisasi roles dan permissions
        if (isset($data['roles'])) {
            $record->syncRoles($data['roles']);
        }

        if (isset($data['permissions'])) {
            $record->syncPermissions($data['permissions']);
        }

        return $record;
    }
}
