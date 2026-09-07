<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\ShopResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class CreateShop extends CreateRecord
{
    protected static string $resource = ShopResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Tambah Toko';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan');
    }

    // ganti label "Create & create another"
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Simpan & buat lagi');
    }

    // ganti label Cancel
    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user && $user->isSeller() && ! $user->isAdmin()) {
            $data['user_id'] = $user->id;
            $data['status'] = 'approved';
        }

        try {
            return ShopResource::syncQrisPayloadFromImage($data);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'data.qris_image' => $e->getMessage(),
            ]);
        }
    }
}
