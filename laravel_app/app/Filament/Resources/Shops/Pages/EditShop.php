<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\ShopResource;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class EditShop extends EditRecord
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->isAdmin()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Ubah Toko';
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            return ShopResource::syncQrisPayloadFromImage($data);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'data.qris_image' => $e->getMessage(),
            ]);
        }
    }
}
