<?php

namespace App\Filament\Resources\SellerApplications\Pages;

use App\Filament\Resources\SellerApplicationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class EditSellerApplication extends EditRecord
{
    protected static string $resource = SellerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // If application is approved, assign seller role
        if ($this->record->status === 'approved' && $this->record->wasChanged('status')) {
            $this->record->update([
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            $this->record->user->assignRole('seller');
        }
    }

    public function getTitle(): string | Htmlable
    {
        return "Ubah Permohonan";
    }

    // ganti label tombol "Save changes" / Save pada footer form
    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()->label('Simpan Perubahan');
    }

    // ganti label tombol Cancel pada footer
    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }

    // (opsional) konfigurasi tombol Delete yang muncul di header
    protected function configureDeleteAction(DeleteAction $action): void
    {
        $action->label('Hapus');
    }
}
