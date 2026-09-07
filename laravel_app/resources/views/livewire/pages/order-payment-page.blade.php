<div class="min-h-screen bg-stone-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <p class="text-xs tracking-[0.2em] uppercase text-green-700 font-semibold">Pembayaran QRIS</p>
            <h1 class="text-2xl font-bold text-stone-900">{{ $order->order_number }}</h1>
            <p class="text-stone-600 text-sm mt-1">
                Scan QR di bawah, transfer sesuai nominal, lalu unggah bukti. Penjual yang memverifikasi.
            </p>
        </div>

        @if ($order->payment_status === 'verified')
            <div class="bg-green-50 border border-green-200 p-4 mb-6 text-green-800 text-sm">
                Pembayaran sudah diverifikasi. Pesanan diproses penjual.
            </div>
        @elseif ($order->payment_status === 'awaiting_verification')
            <div class="bg-amber-50 border border-amber-200 p-4 mb-6 text-amber-900 text-sm">
                Bukti sudah diterima. Menunggu verifikasi penjual.
            </div>
        @elseif ($order->payment_status === 'rejected')
            <div class="bg-red-50 border border-red-200 p-4 mb-6 text-red-800 text-sm">
                Bukti ditolak{{ $order->payment_rejection_note ? ': '.$order->payment_rejection_note : '.' }}
                Unggah ulang bukti yang jelas.
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border border-stone-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-stone-900">
                        {{ $order->qris_type === 'dynamic' ? 'QRIS Dinamis' : 'QRIS Statis' }}
                    </h2>
                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1">
                        {{ $order->qris_type === 'dynamic' ? 'Nominal tertanam' : 'Isi nominal manual' }}
                    </span>
                </div>

                <div class="flex justify-center bg-white p-4 border border-dashed border-stone-300 mb-4">
                    @if ($qrDataUri)
                        <img src="{{ $qrDataUri }}" alt="Kode QRIS {{ $order->order_number }}" width="300" height="300" class="h-auto max-w-full" />
                    @else
                        <div class="w-[260px] h-[260px] flex items-center justify-center text-stone-400 text-sm">
                            Kode QRIS tidak tersedia
                        </div>
                    @endif
                </div>

                <div class="text-center mb-4">
                    <p class="text-xs text-stone-500">Total dibayar</p>
                    <p class="text-3xl font-bold text-green-800">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </p>
                    @if ($order->qris_type === 'static')
                        <p class="text-xs text-stone-500 mt-2">Masukkan nominal ini saat scan QR statis.</p>
                    @endif
                </div>

                <p class="text-xs text-stone-500 break-all font-mono leading-relaxed">{{ $qrisPayload }}</p>
            </div>

            <div class="space-y-6">
                <div class="bg-white border border-stone-200 p-6">
                    <h2 class="font-semibold text-stone-900 mb-3">Unggah bukti</h2>
                    <form wire:submit="uploadProof" class="space-y-4">
                        <input type="file" wire:model="proof" accept="image/*"
                            class="block w-full text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-green-700 file:text-white file:text-sm" />
                        @error('proof')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="proof" class="text-sm text-stone-500">Mengunggah pratinjau...</div>

                        @if ($proof)
                            <img src="{{ $proof->temporaryUrl() }}" alt="Pratinjau bukti"
                                class="w-full max-h-48 object-contain border border-stone-200" />
                        @elseif ($order->payment_proof_path)
                            <img src="{{ asset('storage/'.$order->payment_proof_path) }}" alt="Bukti pembayaran"
                                class="w-full max-h-48 object-contain border border-stone-200" />
                        @endif

                        <button type="submit"
                            class="w-full bg-green-700 text-white py-3 font-medium hover:bg-green-800">
                            Kirim bukti pembayaran
                        </button>
                    </form>
                </div>

                <div class="bg-white border border-stone-200 p-6 text-sm text-stone-600 space-y-2">
                    <p class="font-medium text-stone-900">Cara bayar</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Buka GoPay, DANA, OVO, ShopeePay, atau mobile banking.</li>
                        <li>Scan QR di samping.</li>
                        @if ($order->qris_type === 'static')
                            <li>Isi nominal Rp {{ number_format($order->total, 0, ',', '.') }}.</li>
                        @else
                            <li>Pastikan nominal sudah sesuai, lalu bayar.</li>
                        @endif
                        <li>Simpan screenshot, unggah sebagai bukti.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
