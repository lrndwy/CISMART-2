<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected ?string $pendingStatus = null;

    /**
     * Jangan tulis `status` langsung: simpan target, lalu jalankan lewat
     * Order::transitionTo() supaya stok, sold_count, riwayat, timestamp,
     * dan notifikasi ikut terurus.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $target = $data['status'] ?? $this->record->status;
        $this->pendingStatus = $target !== $this->record->status ? $target : null;

        $data['status'] = $this->record->status;

        unset($data['confirmed_at'], $data['cancelled_at'], $data['whatsapp_sent_at']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->pendingStatus === null) {
            return;
        }

        $applied = $this->record->transitionTo($this->pendingStatus, Auth::user(), 'Diubah dari panel');

        if (! $applied) {
            Notification::make()
                ->title('Transisi status ditolak')
                ->body('Status tidak berubah. Pastikan pembayaran sudah terverifikasi dan transisi memang diizinkan (mis. Dikirim → Selesai).')
                ->danger()
                ->send();
        }

        $this->record->refresh();
    }
}