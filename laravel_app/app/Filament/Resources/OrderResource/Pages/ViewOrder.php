<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verifyPayment')
                ->label('Verifikasi pembayaran')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi pembayaran QRIS?')
                ->modalDescription('Pastikan nominal dan bukti transfer sesuai. Pesanan akan dikonfirmasi.')
                ->visible(fn () => $this->record->isAwaitingPaymentVerification()
                    && Auth::user()?->can('verifyPayment', $this->record))
                ->action(function () {
                    $this->record->verifyPayment();

                    Notification::make()
                        ->title('Pembayaran diverifikasi')
                        ->success()
                        ->send();
                }),

            Action::make('rejectPayment')
                ->label('Tolak bukti')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->isAwaitingPaymentVerification()
                    && Auth::user()?->can('verifyPayment', $this->record))
                ->form([
                    Textarea::make('note')
                        ->label('Alasan')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->rejectPayment($data['note']);

                    Notification::make()
                        ->title('Bukti ditolak')
                        ->warning()
                        ->send();
                }),

            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => Auth::check() && Auth::user()->isAdmin()),
        ];
    }

    public function getTitle(): string
    {
        return 'Detail Pesanan';
    }
}
