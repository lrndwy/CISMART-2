@php
    $finalPrice = $product->discount
        ? floor($product->price * (1 - $product->discount / 100))
        : $product->price;

    $shopLogo = $product->shop->logo
        ? (filter_var($product->shop->logo, FILTER_VALIDATE_URL)
            ? $product->shop->logo
            : Storage::url($product->shop->logo))
        : 'https://picsum.photos/seed/cismart-l'.$product->shop->id.'/240/240';

    $mainImage = $productImages[$selectedImage] ?? asset('images/placeholder.png');
@endphp

<div class="bg-stone-50 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-stone-500" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-green-700 transition-colors">Beranda</a>
            <svg class="h-3.5 w-3.5 text-stone-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
            <a href="{{ route('products.index') }}" class="hover:text-green-700 transition-colors">Produk</a>
            <svg class="h-3.5 w-3.5 text-stone-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
            <span class="truncate text-stone-700 font-medium">{{ $product->name }}</span>
        </nav>

        {{-- Asymmetric split: gallery (7) / buy box (5) --}}
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

            {{-- Gallery --}}
            <div class="lg:col-span-7 reveal" style="--i: 0">
                <div class="relative overflow-hidden rounded-2xl border border-stone-200/70 bg-white shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                    <img src="{{ $mainImage }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover" />

                    <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                        @if ($product->discount)
                            <span class="rounded-full bg-green-700/90 px-3 py-1 text-xs font-semibold text-white">
                                -{{ $product->discount }}%
                            </span>
                        @endif
                        @if ($product->is_featured)
                            <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-stone-800 inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 fill-amber-500 text-amber-500" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                Unggulan
                            </span>
                        @endif
                    </div>

                    @if ($product->stock === 0)
                        <div class="absolute inset-0 flex items-center justify-center bg-stone-900/45">
                            <span class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-stone-900">Stok Habis</span>
                        </div>
                    @endif
                </div>

                @if (count($productImages) > 1)
                    <div class="mt-4 flex gap-3 overflow-x-auto pb-1">
                        @foreach ($productImages as $index => $image)
                            <button wire:click="selectImage({{ $index }})" aria-label="Gambar {{ $index + 1 }}"
                                class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border transition
                                    {{ $selectedImage === $index
                                        ? 'border-transparent ring-2 ring-green-700'
                                        : 'border-stone-200/70 hover:border-stone-300' }}">
                                <img src="{{ $image }}" alt="{{ $product->name }} {{ $index + 1 }}"
                                    class="h-full w-full object-cover {{ $selectedImage === $index ? '' : 'opacity-80 hover:opacity-100' }} transition" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Buy box --}}
            <div class="lg:col-span-5 reveal lg:sticky lg:top-24" style="--i: 1">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">
                    {{ $product->category->name ?? 'Produk UMKM' }}
                </p>
                <h1 class="mt-2 text-2xl md:text-3xl font-bold tracking-tight text-stone-900 leading-tight">
                    {{ $product->name }}
                </h1>

                {{-- Rating + sold --}}
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 fill-amber-500 text-amber-500" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="font-semibold text-stone-900">{{ number_format($averageRating, 1) }}</span>
                    </span>
                    <span class="text-stone-300">·</span>
                    <span class="text-stone-500">{{ $product->sold ?? 0 }} terjual</span>
                </div>

                {{-- Price --}}
                <div class="mt-5 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <span class="text-xs font-medium text-stone-500">Rp</span>
                    <span class="text-3xl font-bold tracking-tight text-stone-900">{{ number_format($finalPrice, 0, ',', '.') }}</span>
                    @if ($product->discount)
                        <span class="text-base text-stone-400 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    @endif
                </div>

                <div class="mt-6 h-px bg-stone-200"></div>

                {{-- Store --}}
                <div class="mt-6 flex items-center gap-3">
                    <img src="{{ $shopLogo }}" alt="{{ $product->shop->name }}"
                        class="h-12 w-12 rounded-xl object-cover ring-1 ring-stone-200/70 shrink-0" />
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('shops.show', $product->shop->slug) }}"
                            class="text-sm font-semibold text-stone-900 hover:text-green-700 transition-colors block truncate">
                            {{ $product->shop->name }}
                        </a>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-stone-500">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 fill-amber-500 text-amber-500" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                {{ $product->shop->rating ?? 0 }}
                            </span>
                            <span class="inline-flex items-center gap-1 truncate">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                <span class="truncate">{{ $product->shop->address ?? 'Lokasi tidak tersedia' }}</span>
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('shops.show', $product->shop->slug) }}"
                        class="shrink-0 rounded-lg border border-stone-300 px-3 py-2 text-xs font-medium text-stone-700 hover:border-green-700 hover:text-green-700 active:scale-[0.98] transition">
                        Kunjungi
                    </a>
                </div>

                {{-- Quantity --}}
                <div class="mt-6">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-stone-700">Jumlah</label>
                        @if ($product->stock > 0 && $product->stock < 10)
                            <span class="text-xs font-medium text-amber-600">Sisa {{ $product->stock }} unit</span>
                        @endif
                    </div>
                    <div class="mt-2 flex items-center gap-4">
                        <div class="inline-flex items-center rounded-lg border border-stone-300 bg-white">
                            <button wire:click="decrementQuantity" aria-label="Kurangi jumlah"
                                class="p-2.5 text-stone-600 hover:text-green-700 active:scale-[0.92] transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                </svg>
                            </button>
                            <span class="w-12 text-center text-sm font-semibold tabular-nums text-stone-900">{{ $quantity }}</span>
                            <button wire:click="incrementQuantity" @disabled($quantity >= $product->stock)
                                aria-label="Tambah jumlah"
                                class="p-2.5 text-stone-600 hover:text-green-700 active:scale-[0.92] transition disabled:cursor-not-allowed disabled:text-stone-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                </svg>
                            </button>
                        </div>
                        <span class="text-sm text-stone-500">Stok {{ $product->stock }}</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-6 space-y-3">
                    <button wire:click="addToCart" @disabled($product->stock === 0)
                        class="w-full rounded-lg px-4 py-3 text-sm font-semibold transition active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-400
                            {{ $product->stock === 0 ? '' : 'bg-green-700 text-white hover:bg-green-800' }}
                            inline-flex items-center justify-center gap-2">
                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .894.448.864.957L4.5 21h13.5l.75-12H6" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25h6M12 17.25v-6" />
                        </svg>
                        {{ $product->stock === 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
                    </button>

                    <div class="flex gap-3">
                        <button type="button" wire:click="toggleFavorite"
                            aria-pressed="{{ $isFavorited ? 'true' : 'false' }}"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border px-3 py-2.5 text-sm font-medium transition active:scale-[0.98]
                                {{ $isFavorited
                                    ? 'border-green-700 bg-green-50 text-green-800'
                                    : 'border-stone-300 text-stone-600 hover:border-green-700 hover:text-green-700' }}">
                            <svg class="h-4 w-4" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.312-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                            {{ $isFavorited ? 'Difavoritkan' : 'Favorit' }}
                            @if ($favoriteCount > 0)
                                <span class="rounded-full bg-white/70 px-1.5 text-xs tabular-nums">{{ $favoriteCount }}</span>
                            @endif
                        </button>

                        <button type="button"
                            x-data="{
                                share() {
                                    const url = window.location.href;
                                    const payload = {
                                        title: @js($product->name),
                                        text: @js('Lihat '.$product->name.' di CISMART'),
                                        url,
                                    };
                                    if (navigator.share) {
                                        navigator.share(payload).catch(() => {});
                                        return;
                                    }
                                    if (navigator.clipboard) {
                                        navigator.clipboard.writeText(url)
                                            .then(() => Livewire.dispatch('notify', { message: 'Tautan produk disalin.', type: 'success' }))
                                            .catch(() => Livewire.dispatch('notify', { message: 'Gagal menyalin tautan.', type: 'error' }));
                                        return;
                                    }
                                    Livewire.dispatch('notify', { message: 'Perangkat tidak mendukung berbagi.', type: 'error' });
                                },
                            }"
                            @click="share()"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-stone-300 px-3 py-2.5 text-sm font-medium text-stone-600 transition hover:border-green-700 hover:text-green-700 active:scale-[0.98]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                            </svg>
                            Bagikan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details + reviews --}}
        <div class="mt-14 reveal" style="--i: 2">
            <div class="border-b border-stone-200">
                <nav class="flex gap-8">
                    <button wire:click="$set('showReviews', false)"
                        class="pb-3.5 px-1 -mb-px border-b-2 text-sm transition-colors {{ !$showReviews ? 'border-green-700 text-green-700 font-semibold' : 'border-transparent text-stone-500 hover:text-stone-700' }}">
                        Deskripsi Produk
                    </button>
                    <button wire:click="$set('showReviews', true)"
                        class="pb-3.5 px-1 -mb-px border-b-2 text-sm transition-colors {{ $showReviews ? 'border-green-700 text-green-700 font-semibold' : 'border-transparent text-stone-500 hover:text-stone-700' }}">
                        Ulasan
                    </button>
                </nav>
            </div>

            <div class="pt-8">
                @if (!$showReviews)
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        <div class="lg:col-span-7">
                            <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Deskripsi</h3>
                            <p class="mt-3 text-stone-600 leading-relaxed max-w-[65ch] whitespace-pre-line">{{ $product->description }}</p>
                        </div>

                        <div class="lg:col-span-5">
                            <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Spesifikasi</h3>
                            <dl class="mt-3 divide-y divide-stone-200 border-t border-stone-200 text-sm">
                                <div class="flex justify-between gap-4 py-3">
                                    <dt class="text-stone-500">Kategori</dt>
                                    <dd class="text-right font-medium text-stone-900">{{ $product->category->name ?? 'Tidak tersedia' }}</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-3">
                                    <dt class="text-stone-500">Berat</dt>
                                    <dd class="text-right font-medium text-stone-900">{{ $product->weight_gram }} gram</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-3">
                                    <dt class="text-stone-500">Kondisi</dt>
                                    <dd class="text-right font-medium text-stone-900">Baru</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-3">
                                    <dt class="text-stone-500">Dikirim dari</dt>
                                    <dd class="text-right font-medium text-stone-900">{{ $product->shop->address ?? 'Tidak tersedia' }}</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-3">
                                    <dt class="text-stone-500">Ongkir</dt>
                                    <dd class="text-right font-medium text-stone-900">Mulai Rp 15.000</dd>
                                </div>
                                <div class="flex justify-between gap-4 py-3">
                                    <dt class="text-stone-500">Estimasi tiba</dt>
                                    <dd class="text-right font-medium text-stone-900">1–3 hari kerja</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                @else
                    <div class="space-y-6">
                        <div class="rounded-2xl border border-stone-200/70 bg-white p-6">
                            <div class="flex items-center gap-4">
                                <span class="text-3xl font-bold text-stone-900">{{ number_format($averageRating, 1) }}</span>
                                <div>
                                    <span class="text-amber-500">{{ str_repeat('★', (int) round($averageRating)) }}<span class="text-stone-300">{{ str_repeat('★', max(0, 5 - (int) round($averageRating))) }}</span></span>
                                    <p class="text-xs text-stone-500">{{ $product->total_reviews ?? 0 }} ulasan produk</p>
                                </div>
                            </div>
                        </div>

                        @if ($canReview)
                            <div class="rounded-2xl border border-stone-200/70 bg-white p-6">
                                <h3 class="text-sm font-semibold text-stone-900">Tulis ulasan produk</h3>
                                <div class="mt-3 flex items-center gap-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <button type="button" wire:click="$set('rating', {{ $i }})"
                                            class="text-2xl leading-none {{ $rating >= $i ? 'text-amber-500' : 'text-stone-300' }}">★</button>
                                    @endfor
                                    <span class="ml-2 text-xs text-stone-500">{{ $rating }}/5</span>
                                </div>
                                <textarea wire:model="comment" rows="3" placeholder="Ceritakan kualitas produk ini..."
                                    class="mt-3 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></textarea>
                                @error('comment')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                @error('rating')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <button wire:click="submitReview" wire:loading.attr="disabled"
                                    class="mt-3 rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800 disabled:opacity-50">
                                    Kirim ulasan
                                </button>
                            </div>
                        @elseif ($myReview)
                            <p class="text-xs text-stone-500">Kamu sudah mengulas produk ini
                                pada {{ $myReview->created_at->translatedFormat('d F Y') }}.</p>
                        @endif

                        @forelse ($reviews as $review)
                            <div wire:key="review-{{ $review->id }}" class="rounded-2xl border border-stone-200/70 bg-white p-5">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-stone-100 text-xs font-semibold text-stone-600">
                                            {{ $review->user?->initials() ?? 'U' }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-medium text-stone-900">{{ $review->user?->name ?? 'Pembeli' }}</p>
                                            <p class="text-xs text-stone-500">{{ $review->created_at->translatedFormat('d F Y') }}</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-amber-500">{{ str_repeat('★', (int) $review->rating) }}<span class="text-stone-300">{{ str_repeat('★', 5 - (int) $review->rating) }}</span></span>
                                </div>
                                <p class="mt-3 text-sm leading-relaxed text-stone-600 whitespace-pre-line">{{ $review->comment }}</p>
                            </div>
                        @empty
                    <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-12 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100">
                            <svg class="h-6 w-6 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-stone-900">Belum ada ulasan</h3>
                        <p class="mt-1 text-xs text-stone-500">Ulasan dari pembeli yang sudah menerima produk akan muncul di sini.</p>
                    </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
