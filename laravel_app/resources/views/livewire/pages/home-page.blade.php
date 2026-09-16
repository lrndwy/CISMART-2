{{-- Satu root element agar wire:id membungkus seluruh konten (termasuk tombol). --}}
<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden py-16 lg:py-24">
        {{-- Latar dekoratif (tanpa gambar; diklip oleh overflow-hidden) --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            {{-- Wash gradien hijau lembut --}}
            <div class="absolute inset-0"
                style="background-image: radial-gradient(58% 55% at 50% 0%, rgba(21,128,61,0.12) 0%, rgba(21,128,61,0.04) 40%, rgba(21,128,61,0) 72%);">
            </div>

            {{-- Pola titik yang memudar ke tepi --}}
            <div class="absolute inset-0"
                style="background-image: radial-gradient(rgba(28,25,23,0.10) 1px, transparent 1px); background-size: 22px 22px; -webkit-mask-image: radial-gradient(68% 60% at 50% 38%, #000 0%, transparent 78%); mask-image: radial-gradient(68% 60% at 50% 38%, #000 0%, transparent 78%);">
            </div>

            {{-- Cincin konsentris di belakang judul --}}
            <div class="absolute left-1/2 top-2 -translate-x-1/2 sm:top-4">
                <div class="h-[22rem] w-[22rem] rounded-full border border-green-700/10 sm:h-[30rem] sm:w-[30rem]"></div>
                <div class="absolute inset-6 rounded-full border border-green-700/10 sm:inset-8"></div>
                <div class="absolute inset-12 rounded-full border border-stone-900/5 sm:inset-16"></div>
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-3xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">
                    CISMART · Pasar Online UMKM Cilacap
                </p>
                <h1 class="mt-4 text-4xl md:text-6xl font-bold tracking-tighter leading-none text-stone-900">
                    Pasar online<br />
                    <span class="text-green-700">UMKM Cilacap.</span>
                </h1>
                <p class="mt-5 mx-auto max-w-[52ch] text-stone-600 leading-relaxed">
                    Temukan produk unggulan UMKM Cilacap — dari makanan khas sampai kerajinan tangan.
                    Belanja langsung dari produsennya, dukung ekonomi lokal.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                    <a href="{{ route('products.index') }}"
                        class="bg-green-700 hover:bg-green-800 active:scale-[0.98] transition text-white rounded-lg px-6 py-3.5 font-semibold text-sm text-center">
                        Jelajahi Produk
                    </a>
                    <a href="{{ route('shops.index') }}"
                        class="border border-stone-300 hover:border-green-700 hover:text-green-700 transition text-stone-700 rounded-lg px-6 py-3.5 font-semibold text-sm text-center">
                        Kenali Tokonya
                    </a>
                </div>

                <ul class="mt-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-sm text-stone-500">
                    <li class="inline-flex items-center gap-2">
                        <svg class="h-4.5 w-4.5 shrink-0 text-green-700" fill="none" stroke="currentColor"
                            stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                        Toko terverifikasi
                    </li>
                    <li class="inline-flex items-center gap-2">
                        <svg class="h-4.5 w-4.5 shrink-0 text-green-700" fill="none" stroke="currentColor"
                            stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                        </svg>
                        QRIS &amp; bayar di tempat
                    </li>
                    <li class="inline-flex items-center gap-2">
                        <svg class="h-4.5 w-4.5 shrink-0 text-green-700" fill="none" stroke="currentColor"
                            stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        Kirim dalam kota
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- Statistik --}}
    <div class="border-y border-stone-200 bg-white">
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 divide-x divide-stone-200">
            <div class="px-6 py-5">
                <p class="text-2xl font-bold text-stone-900">{{ $productCount }}+</p>
                <p class="text-xs uppercase tracking-wider text-stone-500 mt-1">Produk</p>
            </div>
            <div class="px-6 py-5">
                <p class="text-2xl font-bold text-stone-900">{{ $shopCount }}+</p>
                <p class="text-xs uppercase tracking-wider text-stone-500 mt-1">UMKM Terkurasi</p>
            </div>
            <div class="px-6 py-5">
                <p class="text-2xl font-bold text-stone-900">QRIS</p>
                <p class="text-xs uppercase tracking-wider text-stone-500 mt-1">Pembayaran</p>
            </div>
            <div class="px-6 py-5">
                <p class="text-2xl font-bold text-stone-900">100%</p>
                <p class="text-xs uppercase tracking-wider text-stone-500 mt-1">Produk Lokal</p>
            </div>
        </div>
    </div>

    {{-- Produk Unggulan --}}
    @if ($featuredProducts->count() > 0)
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex items-end justify-between border-b border-stone-200 pb-4 mb-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Pilihan Terbaik</p>
                    <h2 class="mt-2 text-2xl md:text-3xl font-bold tracking-tight text-stone-900">Produk Unggulan</h2>
                    <p class="text-stone-500 text-sm mt-1">Dikurasi langsung dari toko UMKM terpercaya.</p>
                </div>
                <a href="{{ route('products.index') }}"
                    class="text-sm font-semibold text-green-700 hover:text-green-800 inline-flex items-center gap-1.5 whitespace-nowrap">
                    Lihat semua
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                @foreach ($featuredProducts as $product)
                    <div class="reveal" style="--i: {{ $loop->index }}">
                        <livewire:components.product-card :product="$product" :key="'product-' . $product->id" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- UMKM Terpopuler --}}
    @if ($topStores->count() > 0)
        <section class="bg-white border-y border-stone-200 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between border-b border-stone-200 pb-4 mb-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Dari Produsennya</p>
                        <h2 class="mt-2 text-2xl md:text-3xl font-bold tracking-tight text-stone-900">UMKM Terpopuler</h2>
                        <p class="text-stone-500 text-sm mt-1">Toko yang paling aktif melayani pembeli.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($topStores as $shop)
                        <div class="reveal" style="--i: {{ $loop->index }}">
                            <livewire:components.store-card :shop="$shop" :key="'store-' . $shop->id" />
                        </div>
                    @endforeach
                </div>
                <div class="mt-10">
                    <a href="{{ route('shops.index') }}"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-700 hover:text-green-800">
                        Jelajahi Semua UMKM
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- Manfaat --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-stone-900">
            Kenapa belanja di CISMART?
        </h2>
        <div class="mt-10 grid md:grid-cols-2 gap-x-12 gap-y-8">
            @foreach ($benefits as $benefit)
                <div class="flex gap-4 border-t border-stone-200 pt-6 reveal" style="--i: {{ $loop->index }}">
                    <span class="bg-green-50 text-green-700 rounded-lg p-2.5 h-fit">
                        @switch($benefit['icon'])
                            @case('cart')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                </svg>
                            @break
                        @case('star')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        @break
                    @case('heart')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    @break
                @case('truck')
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                @break
            @endswitch
            </span>
            <div>
                <h3 class="font-semibold text-stone-900">{{ $benefit['title'] }}</h3>
                <p class="text-sm text-stone-600 leading-relaxed mt-1">{{ $benefit['description'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
    </section>

    {{-- CTA seller --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="bg-green-900 rounded-2xl p-10 lg:p-14 grid lg:grid-cols-[1.4fr_1fr] items-center gap-8">
            <div>
                <h2 class="text-white text-3xl font-bold tracking-tight">Punya produk UMKM? Titip jual di CISMART.</h2>
                <p class="text-green-100/80 mt-3 leading-relaxed">
                    Daftarkan tokomu, kelola produk, dan terima pesanan QRIS tanpa ribet. Tim kami membantu
                    dari kurasi sampai pengiriman.
                </p>
            </div>
            <div class="lg:justify-self-end">
                <button wire:click="handleSellerRegistration"
                    class="bg-white text-green-900 rounded-lg px-6 py-3.5 font-semibold hover:bg-green-50 active:scale-[0.98] transition">
                    Mulai Berjualan
                </button>
            </div>
        </div>
    </section>

    {{-- Kontak & Mulai Sekarang --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-stone-200">
        <div class="grid lg:grid-cols-2 gap-12">
            {{-- Kontak --}}
            <div>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-stone-900">Hubungi Kami</h2>
                <p class="text-stone-600 mt-3 mb-8 leading-relaxed">
                    Tim CISMART siap membantu Anda. Hubungi kami untuk pertanyaan, dukungan teknis, atau konsultasi
                    bisnis UMKM.
                </p>
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <span class="bg-green-50 text-green-700 rounded-lg p-2.5 h-fit">
                            <flux:icon.map-pin class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-stone-900 mb-1">Alamat Kantor</h3>
                            <p class="text-stone-600">
                                Jl. Sudirman No. 123<br>
                                Cilacap, Jawa Tengah 53211
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="bg-green-50 text-green-700 rounded-lg p-2.5 h-fit">
                            <flux:icon.phone class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-stone-900 mb-1">Telepon</h3>
                            <p class="text-stone-600">(0282) 123-4567</p>
                            <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                                class="text-green-700 hover:text-green-800 transition-colors">
                                WhatsApp: 0812-3456-7890
                            </a>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="bg-green-50 text-green-700 rounded-lg p-2.5 h-fit">
                            <flux:icon.envelope class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-stone-900 mb-1">Email</h3>
                            <p class="text-stone-600">info@cismart.id</p>
                            <p class="text-stone-600">support@cismart.id</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick actions --}}
            <div>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-stone-900">Mulai Sekarang</h2>
                <div class="mt-8 divide-y divide-stone-200 border border-stone-200/70 rounded-xl bg-white overflow-hidden shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                    <div class="p-6 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-stone-900">Untuk Pembeli</h3>
                            <p class="text-sm text-stone-500 mt-1">Temukan produk UMKM Cilacap dengan kualitas terjamin.</p>
                        </div>
                        <a href="{{ route('products.index') }}"
                            class="text-sm font-semibold text-green-700 hover:text-green-800 whitespace-nowrap">
                            Mulai Belanja
                        </a>
                    </div>
                    <div class="p-6 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-stone-900">Untuk UMKM</h3>
                            <p class="text-sm text-stone-500 mt-1">Perluas jangkauan bisnismu lewat platform terpercaya.</p>
                        </div>
                        <button wire:click="handleSellerRegistration"
                            class="text-sm font-semibold text-green-700 hover:text-green-800 whitespace-nowrap">
                            Daftar Penjual
                        </button>
                    </div>
                    <div class="p-6 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-stone-900">Untuk Pencari Oleh-Oleh</h3>
                            <p class="text-sm text-stone-500 mt-1">Kenali toko dan produsennya sebelum membeli.</p>
                        </div>
                        <a href="{{ route('shops.index') }}"
                            class="text-sm font-semibold text-green-700 hover:text-green-800 whitespace-nowrap">
                            Cari Toko
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
