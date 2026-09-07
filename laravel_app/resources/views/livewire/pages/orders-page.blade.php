<div class="min-h-screen bg-stone-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-stone-900 mb-6">Pesanan saya</h1>

        @forelse ($orders as $order)
            <div class="bg-white border border-stone-200 p-5 mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="font-semibold text-stone-900">{{ $order->order_number }}</p>
                    <p class="text-sm text-stone-600">
                        {{ $order->payment_method_label }} · {{ $order->payment_status_label }} · Rp {{ number_format($order->total, 0, ',', '.') }}
                    </p>
                </div>
                @if ($order->isQris())
                    <a href="{{ route('orders.pay', $order) }}"
                        class="inline-flex justify-center bg-green-700 text-white px-4 py-2 text-sm font-medium hover:bg-green-800">
                        {{ $order->payment_status === 'verified' ? 'Lihat pembayaran' : 'Bayar / unggah bukti' }}
                    </a>
                @endif
            </div>
        @empty
            <p class="text-stone-600">Belum ada pesanan.</p>
        @endempty
    </div>
</div>
