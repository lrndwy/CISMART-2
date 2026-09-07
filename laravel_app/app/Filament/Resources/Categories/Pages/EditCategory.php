<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string | Htmlable
    {
        return "Ubah Kategori";
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
