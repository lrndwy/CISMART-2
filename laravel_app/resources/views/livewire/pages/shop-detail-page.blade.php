@php
    $input = 'text-stone-600';
@endphp
<div class="bg-stone-50">
    {{-- Banner + identitas --}}
    <section class="relative">
        <div class="h-56 md:h-72 bg-cover bg-center" style="background-image: url('{{ $bannerUrl }}')"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-stone-50 via-stone-50/20 to-transparent"></div>

        <div class="absolute top-4 left-4 sm:left-6 lg:left-8">
            <a href="{{ route('shops.index') }}"
                class="inline-flex items-center gap-1.5 rounded-full bg-white/90 text-stone-700 hover:text-green-700 text-sm font-medium pl-2.5 pr-4 py-1.5 shadow-[0_1px_2px_rgba(28,25,23,0.05)] transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Semua UMKM
            </a>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative">
        {{-- Kartu identitas --}}
        <div class="reveal rounded-2xl border border-stone-200/70 bg-white p-6 lg:p-8 shadow-[0_12px_28px_-14px_rgba(28,25,23,0.18)]">
            <div class="flex flex-col md:flex-row md:items-start gap-6">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <img src="{{ $logoUrl }}" alt="{{ $shop->name }}"
                        class="h-20 w-20 md:h-24 md:w-24 rounded-2xl ring-2 ring-white object-cover shadow-[0_1px_2px_rgba(28,25,23,0.05)] shrink-0" />
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-stone-900">{{ $shop->name }}</h1>
                            @if ($shop->is_verified)
                                <span class="bg-green-50 text-green-800 text-xs px-2 py-1 rounded-full font-medium inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                    </svg>
                                    Terverifikasi
                                </span>
                            @endif
                            @if ($shop->is_power_seller)
                                <span class="bg-amber-50 text-amber-700 text-xs px-2 py-1 rounded-full font-medium inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    Power Seller
                                </span>
                            @endif
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm {{ $input }}">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                {{ $shop->address }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 fill-amber-500 text-amber-500" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="font-medium text-stone-900">{{ $shop->rating ?? 0 }}</span>
                                <span class="text-stone-500">({{ $shop->total_reviews ?? 0 }} ulasan)</span>
                            </span>
                            <span>{{ $shop->products_count ?? 0 }} produk</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Strip pencapaian --}}
        <div class="mt-8 grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-stone-200 bg-white border border-stone-200/70 rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
            @foreach ($achievements as $achievement)
                <div class="p-5 flex items-start gap-3">
                    <span class="bg-green-50 text-green-700 rounded-lg p-2 h-fit shrink-0">
                        @switch($achievement['icon'])
                            @case('trophy')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.316-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 00-2.748 1.35m8.362-.352a6.726 6.726 0 012.748 1.35M18.75 4.236c.982.143 1.954.316 2.916.52A6.004 6.004 0 0116.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492v1.516m-7.487 6.878A8.95 8.95 0 0112 13.5c1.497 0 2.905.408 4.107 1.122" />
                                </svg>
                            @break
                        @case('star')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        @break
                    @case('truck')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                    @break
                @case('chat')
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.178C4.968 16.65 3 14.576 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                @break
            @endswitch
            </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900">{{ $achievement['label'] }}</p>
                        <p class="text-xs text-stone-500 mt-0.5 leading-snug">{{ $achievement['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Tabs --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        <div class="border-b border-stone-200">
            <nav class="flex gap-8">
                <button wire:click="setActiveTab('products')"
                    class="pb-3.5 px-1 border-b-2 text-sm transition-colors -mb-px {{ $activeTab === 'products' ? 'border-green-700 text-green-700 font-semibold' : 'border-transparent text-stone-500 hover:text-stone-700' }}">
                    Produk
                    <span class="ml-1.5 text-xs {{ $activeTab === 'products' ? 'text-green-700' : 'text-stone-400' }}">
                        {{ count($shopProducts) }}
                    </span>
                </button>
                <button wire:click="setActiveTab('about')"
                    class="pb-3.5 px-1 border-b-2 text-sm transition-colors -mb-px {{ $activeTab === 'about' ? 'border-green-700 text-green-700 font-semibold' : 'border-transparent text-stone-500 hover:text-stone-700' }}">
                    Tentang Toko
                </button>
                <button wire:click="setActiveTab('reviews')"
                    class="pb-3.5 px-1 border-b-2 text-sm transition-colors -mb-px {{ $activeTab === 'reviews' ? 'border-green-700 text-green-700 font-semibold' : 'border-transparent text-stone-500 hover:text-stone-700' }}">
                    Ulasan
                    <span class="ml-1.5 text-xs {{ $activeTab === 'reviews' ? 'text-green-700' : 'text-stone-400' }}">
                        {{ $shop->total_reviews ?? 0 }}
                    </span>
                </button>
            </nav>
        </div>
    </div>

    {{-- Konten tab --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" wire:loading.class="opacity-50 transition-opacity duration-200" wire:target="setActiveTab">
        @if ($activeTab === 'products')
            @if (count($shopProducts) > 0)
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
                    @foreach ($shopProducts as $product)
                        <div wire:key="product-{{ $product->id }}" class="reveal" style="--i: {{ $loop->index % 8 }}">
                            @include('components.product-card-static', ['product' => $product])
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20">
                    <svg class="h-12 w-12 text-stone-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                    <h3 class="text-lg font-semibold text-stone-900">Belum ada produk</h3>
                    <p class="text-stone-500 text-sm mt-1">Toko ini sedang mempersiapkan produk-produk terbaiknya.</p>
                </div>
            @endif
        @endif

        @if ($activeTab === 'about')
            <div class="grid lg:grid-cols-[1.4fr_1fr] gap-10">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Profil</p>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-stone-900">Tentang {{ $shop->name }}</h2>
                    <p class="mt-4 text-stone-600 leading-relaxed max-w-[65ch] whitespace-pre-line">{{ $shop->description }}</p>

                    <dl class="mt-8 divide-y divide-stone-200 border-t border-stone-200 text-sm max-w-md">
                        <div class="flex justify-between py-3">
                            <dt class="text-stone-500">Alamat</dt>
                            <dd class="text-stone-900 font-medium text-right">{{ $shop->address }}</dd>
                        </div>
                        <div class="flex justify-between py-3">
                            <dt class="text-stone-500">Telepon</dt>
                            <dd class="text-stone-900 font-medium">
                                <a href="tel:{{ $shop->phone }}" class="hover:text-green-700 transition-colors">{{ $shop->phone }}</a>
                            </dd>
                        </div>
                        @if ($shop->email)
                            <div class="flex justify-between py-3">
                                <dt class="text-stone-500">Email</dt>
                                <dd class="text-stone-900 font-medium">{{ $shop->email }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between py-3">
                            <dt class="text-stone-500">Status</dt>
                            <dd class="text-stone-900 font-medium">{{ $shop->is_verified ? 'Terverifikasi CISMART' : 'UMKM terdaftar' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Jam Operasional</h3>
                        <div class="divide-y divide-stone-200 border border-stone-200/70 rounded-xl bg-white overflow-hidden shadow-[0_1px_2px_rgba(28,25,23,0.05)] text-sm">
                            <div class="flex justify-between px-4 py-3">
                                <span class="text-stone-600">Senin - Jumat</span>
                                <span class="font-medium text-stone-900">08:00 - 17:00</span>
                            </div>
                            <div class="flex justify-between px-4 py-3">
                                <span class="text-stone-600">Sabtu</span>
                                <span class="font-medium text-stone-900">08:00 - 15:00</span>
                            </div>
                            <div class="flex justify-between px-4 py-3">
                                <span class="text-stone-600">Minggu</span>
                                <span class="font-medium text-stone-400">Tutup</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Kebijakan Toko</h3>
                        <ul class="space-y-2.5 text-sm text-stone-600">
                            @foreach (['Garansi kualitas produk 100%', 'Retur gratis dalam 7 hari', 'Pengiriman gratis minimal Rp 100.000', 'Proses pesanan maksimal 1x24 jam'] as $policy)
                                <li class="flex items-start gap-2.5">
                                    <svg class="h-4 w-4 text-green-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    {{ $policy }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if ($activeTab === 'reviews')
            <div class="grid lg:grid-cols-[280px_1fr] gap-10 items-start">
                {{-- Ringkasan --}}
                <div class="border border-stone-200/70 rounded-xl bg-white p-6 shadow-[0_1px_2px_rgba(28,25,23,0.05)] lg:sticky lg:top-24">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Rating toko</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-stone-900">
                        {{ $shop->rating ?? 0 }}<span class="text-lg font-medium text-stone-400">/5</span>
                    </p>
                    <div class="mt-2 flex">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 {{ $i < round($shop->rating ?? 0) ? 'fill-amber-500 text-amber-500' : 'fill-none text-stone-300' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        @endfor
                    </div>
                    <p class="text-sm text-stone-500 mt-2">{{ $shop->total_reviews ?? 0 }} ulasan · {{ $shop->response_rate ?? 0 }}% respon</p>
                    <p class="text-xs text-stone-400 mt-4 leading-relaxed border-t border-stone-200 pt-4">
                        Ulasan ditampilkan setelah transaksi diverifikasi penjual.
                    </p>
                </div>

                {{-- Panel aksi + daftar ulasan --}}
                <div class="space-y-6">
                    @auth
                        @if ($canReview)
                            <div class="border border-stone-200/70 rounded-xl bg-white p-6 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                                <h3 class="text-sm font-semibold text-stone-900">Tulis ulasanmu</h3>
                                <p class="text-xs text-stone-500 mt-1">Kamu pernah berbelanja di toko ini. Nilai pengalamanmu untuk membantu pembeli lain.</p>

                                <form wire:submit="submitReview" class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Rating</label>
                                        <div class="flex items-center gap-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <button type="button" wire:click="setRating({{ $i }})" aria-label="{{ $i }} bintang"
                                                    class="p-0.5 transition active:scale-[0.9]">
                                                    <svg class="h-7 w-7 {{ $i <= $rating ? 'fill-amber-500 text-amber-500' : 'fill-none text-stone-300' }}"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                                    </svg>
                                                </button>
                                            @endfor
                                        </div>
                                        @error('rating') <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="review-comment" class="block text-sm font-medium text-stone-700 mb-1.5">Ulasan</label>
                                        <textarea id="review-comment" wire:model.blur="comment" rows="4" maxlength="1000"
                                            class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700"
                                            placeholder="Ceritakan pengalaman belanjamu di toko ini (minimal 10 karakter)."></textarea>
                                        @error('comment') <p class="text-sm text-red-600 mt-1.5">{{ $message }}</p> @enderror
                                    </div>

                                    <button type="submit" wire:loading.attr="disabled" wire:target="submitReview"
                                        class="inline-flex items-center justify-center rounded-lg bg-green-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-green-800 active:scale-[0.98] transition disabled:opacity-60">
                                        <span wire:loading.remove wire:target="submitReview">Kirim Ulasan</span>
                                        <span wire:loading wire:target="submitReview">Mengirim…</span>
                                    </button>
                                </form>
                            </div>
                        @elseif ($myReview)
                            <div class="border border-green-200 bg-green-50/60 rounded-xl p-5">
                                <p class="text-sm font-medium text-green-900">Kamu sudah memberi ulasan untuk toko ini</p>
                                <p class="text-xs text-green-800 mt-1">
                                    {{ $myReview->rating }} bintang · {{ $myReview->created_at->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="border border-stone-200/70 rounded-xl bg-white p-6 text-center shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                            <p class="text-sm text-stone-600">Masuk dan pernah berbelanja di toko ini untuk memberi ulasan.</p>
                            <a href="{{ route('login', ['redirect' => route('shops.show', $shop->slug)]) }}"
                                class="mt-3 inline-flex rounded-lg bg-green-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-green-800 active:scale-[0.98] transition">
                                Masuk
                            </a>
                        </div>
                    @endauth

                    <div class="divide-y divide-stone-200 border border-stone-200/70 rounded-xl bg-white overflow-hidden shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                        @forelse ($reviews as $review)
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-4 mb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="h-10 w-10 rounded-full bg-green-50 text-green-800 flex items-center justify-center text-sm font-semibold shrink-0">
                                            {{ implode('', array_map(fn ($n) => $n[0] ?? '', explode(' ', $review->user?->name ?? 'Pembeli'))) }}
                                        </span>
                                        <div>
                                            <div class="font-medium text-stone-900 text-sm">{{ $review->user?->name ?? 'Pembeli' }}</div>
                                            <div class="text-xs text-stone-500">{{ $review->created_at->translatedFormat('d F Y') }}</div>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0">
                                        @for ($i = 0; $i < $review->rating; $i++)
                                            <svg class="h-3.5 w-3.5 fill-amber-500 text-amber-500" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-sm text-stone-600 leading-relaxed">{{ $review->comment }}</p>
                                <p class="text-xs text-green-800 mt-3 inline-flex items-center gap-1.5 bg-green-50 border border-green-200 rounded-full px-2.5 py-1">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    Pembelian terverifikasi
                                </p>
                            </div>
                        @empty
                            <div class="p-10 text-center">
                                <h3 class="text-sm font-semibold text-stone-900">Belum ada ulasan</h3>
                                <p class="text-xs text-stone-500 mt-1">Ulasan dari pembeli yang pernah bertransaksi akan muncul di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
