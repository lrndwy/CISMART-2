<div class="bg-stone-50 py-10 lg:py-14">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="pb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Akun</p>
                <h1 class="mt-2 text-3xl md:text-4xl font-bold tracking-tight text-stone-900">Pesanan saya</h1>
            </div>
            @if ($orders->isNotEmpty())
                <p class="text-sm text-stone-500 tabular-nums">{{ $orders->count() }} pesanan</p>
            @endif
        </div>

        @if ($orders->isEmpty())
            <div class="rounded-2xl border border-stone-200/70 bg-white py-16 text-center shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                <svg class="h-12 w-12 text-stone-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                </svg>
                <h2 class="text-lg font-semibold text-stone-900">Belum ada pesanan</h2>
                <p class="text-stone-500 text-sm mt-1 mb-5">Produk yang kamu beli akan muncul di sini.</p>
                <a href="{{ route('products.index') }}"
                    class="inline-flex rounded-lg bg-green-700 text-white text-sm font-medium px-5 py-2.5 hover:bg-green-800 active:scale-[0.98] transition">
                    Mulai Belanja
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($orders as $order)
                    @php
                        $statusClasses = match ($order->status) {
                            'pending' => 'bg-amber-50 text-amber-700',
                            'confirmed' => 'bg-blue-50 text-blue-700',
                            'processing' => 'bg-blue-50 text-blue-700',
                            'shipped' => 'bg-green-50 text-green-700',
                            'delivered' => 'bg-green-50 text-green-800',
                            'cancelled' => 'bg-red-50 text-red-700',
                            default => 'bg-stone-100 text-stone-600',
                        };
                        $paymentClasses = match ($order->payment_status) {
                            'unpaid' => 'bg-amber-50 text-amber-700',
                            'awaiting_verification' => 'bg-blue-50 text-blue-700',
                            'verified' => 'bg-green-50 text-green-700',
                            'rejected' => 'bg-red-50 text-red-700',
                            default => 'bg-stone-100 text-stone-600',
                        };
                    @endphp

                    <article class="rounded-2xl border border-stone-200/70 bg-white overflow-hidden shadow-[0_1px_2px_rgba(28,25,23,0.04)]">
                        {{-- Header: nomor, tanggal, status --}}
                        <header class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-stone-200/70 bg-stone-50/60">
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">Nomor pesanan</p>
                                <p class="font-semibold text-stone-900 tabular-nums mt-0.5">{{ $order->order_number }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <p class="text-sm text-stone-500 tabular-nums">{{ $order->created_at->translatedFormat('d F Y · H:i') }}</p>
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">{{ $order->status_label }}</span>
                            </div>
                        </header>

                        {{-- Item --}}
                        <ul class="divide-y divide-stone-100">
                            @foreach ($order->items as $item)
                                <li class="flex items-start gap-4 px-5 py-4">
                                    <img src="{{ $item->product_image
                                        ? (filter_var($item->product_image, FILTER_VALIDATE_URL) ? $item->product_image : Storage::url($item->product_image))
                                        : 'https://picsum.photos/seed/cismart-item-' . $item->id . '/96/96' }}"
                                        alt="{{ $item->product_name }}" class="h-14 w-14 rounded-lg object-cover shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-stone-900">{{ $item->product_name }}</p>
                                        <p class="mt-1 flex items-center gap-1.5 text-xs text-stone-500">
                                            <svg class="h-3.5 w-3.5 text-stone-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                                            </svg>
                                            <span class="truncate">{{ $item->shop_name }}</span>
                                        </p>
                                        <p class="mt-1 text-xs text-stone-500 tabular-nums">
                                            {{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-semibold text-stone-900 tabular-nums whitespace-nowrap">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Footer: pembayaran, pengiriman, total, aksi --}}
                        <footer class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 border-t border-stone-200/70 bg-stone-50/40">
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-stone-500">
                                <span class="flex items-center gap-1.5">
                                    <span>Pembayaran</span>
                                    <span class="font-medium text-stone-700">{{ $order->payment_method_label }}</span>
                                    <span class="rounded-full px-2 py-0.5 font-medium {{ $paymentClasses }}">{{ $order->payment_status_label }}</span>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span>Pengiriman</span>
                                    <span class="font-medium text-stone-700">{{ $order->shipping_method_label }}</span>
                                    @if ($order->shipping_city)
                                        <span class="text-stone-400">· {{ $order->shipping_city }}</span>
                                    @endif
                                </span>
                            </div>

                            <div class="flex items-center gap-4">
                                <p class="text-sm font-bold text-stone-900 tabular-nums">
                                    Rp {{ number_format($order->total, 0, ',', '.') }}
                                </p>
                                @if ($order->isQris())
                                    <a href="{{ route('orders.pay', $order) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 text-white px-4 py-2 text-sm font-medium hover:bg-green-800 active:scale-[0.98] transition whitespace-nowrap">
                                        {{ $order->payment_status === 'verified' ? 'Lihat pembayaran' : 'Bayar / unggah bukti' }}
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-xs text-stone-500">Bayar saat pesanan diterima</span>
                                @endif
                            </div>
                        </footer>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
