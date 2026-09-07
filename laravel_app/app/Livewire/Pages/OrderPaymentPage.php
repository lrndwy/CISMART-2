<?php

namespace App\Livewire\Pages;

use App\Models\Order;
use App\Services\QrisPayload;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class OrderPaymentPage extends Component
{
    use WithFileUploads;

    public Order $order;

    public $proof;

    public function mount(Order $order): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login', ['redirect' => request()->fullUrl()]));

            return;
        }

        $this->authorize('view', $order);

        if (! $order->isQris()) {
            abort(404);
        }

        $this->order = $order;
    }

    public function uploadProof(): void
    {
        $this->authorize('uploadPaymentProof', $this->order);

        if (! $this->order->needsPaymentProof() && ! $this->order->isAwaitingPaymentVerification()) {
            $this->dispatch('notify', [
                'message' => 'Bukti pembayaran sudah diverifikasi.',
                'type' => 'info',
            ]);

            return;
        }

        $this->validate([
            'proof' => 'required|image|max:4096',
        ], [
            'proof.required' => 'Unggah foto bukti pembayaran.',
            'proof.image' => 'Bukti harus berupa gambar (JPG atau PNG).',
            'proof.max' => 'Ukuran gambar maksimal 4 MB.',
        ]);

        $path = $this->proof->store('payment-proofs', 'public');

        $this->order->markPaymentProofUploaded($path);
        $this->order->refresh();
        $this->proof = null;

        $this->dispatch('notify', [
            'message' => 'Bukti terkirim. Menunggu verifikasi penjual.',
            'type' => 'success',
        ]);
    }

    public function render()
    {
        $payload = $this->payloadForQr();

        return view('livewire.pages.order-payment-page', [
            'qrDataUri' => $this->renderQrDataUri($payload),
            'qrisPayload' => $payload,
        ]);
    }

    protected function payloadForQr(): string
    {
        $payload = (string) ($this->order?->qris_payload ?? '');

        if ($payload === '' || $this->order?->qris_type !== 'dynamic') {
            return $payload;
        }

        try {
            return QrisPayload::toDynamic($payload, $this->order->total);
        } catch (\Throwable) {
            return $payload;
        }
    }

    protected function renderQrDataUri(string $payload): string
    {
        if ($payload === '') {
            return '';
        }

        $qrCode = new QrCode(
            data: $payload,
            encoding: new Encoding('ISO-8859-1'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 16,
            backgroundColor: new Color(255, 255, 255),
        );

        return (new PngWriter)->write($qrCode)->getDataUri();
    }
}
