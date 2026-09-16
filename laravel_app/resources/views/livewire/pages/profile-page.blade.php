@php
    $user = auth()->user();
    $roleLabel = $user?->isAdmin() ? 'Admin' : ($user?->isSeller() ? 'Penjual' : 'Pembeli');
    $inputClass = 'w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700';
@endphp

<div class="bg-stone-50 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">

        {{-- Header --}}
        <div class="reveal" style="--i: 0">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Akun</p>
            <h1 class="mt-2 text-3xl md:text-4xl font-bold tracking-tight text-stone-900">Profil Saya</h1>
            <p class="mt-2 text-stone-500">Kelola informasi akunmu dan lihat produk yang kamu favoritkan.</p>
        </div>

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- Informasi profil --}}
            <div class="lg:col-span-7 reveal" style="--i: 1">
                <div class="rounded-2xl border border-stone-200/70 bg-white p-6 lg:p-8 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                    <h2 class="text-sm font-semibold text-stone-900">Informasi Profil</h2>
                    <p class="mt-1 text-xs text-stone-500">Perbarui nama, email, dan kontak yang tampil pada pesananmu.</p>

                    <form wire:submit="updateProfile" class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="p-name" class="block text-sm font-medium text-stone-700 mb-1.5">Nama lengkap</label>
                            <input id="p-name" type="text" wire:model.blur="name" autocomplete="name"
                                class="{{ $inputClass }}" placeholder="Nama lengkapmu" />
                            @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="p-email" class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                            <input id="p-email" type="email" wire:model.blur="email" autocomplete="email"
                                class="{{ $inputClass }}" placeholder="nama@email.com" />
                            @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="p-phone" class="block text-sm font-medium text-stone-700 mb-1.5">Nomor telepon</label>
                            <input id="p-phone" type="tel" wire:model.blur="phone" autocomplete="tel"
                                class="{{ $inputClass }}" placeholder="08xxxxxxxxxx" />
                            @error('phone') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="p-address" class="block text-sm font-medium text-stone-700 mb-1.5">Alamat</label>
                            <textarea id="p-address" wire:model.blur="address" rows="3" autocomplete="street-address"
                                class="{{ $inputClass }} resize-none" placeholder="Alamat pengiriman utama"></textarea>
                            @error('address') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit" wire:loading.attr="disabled" wire:target="updateProfile"
                                class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 active:scale-[0.98] disabled:opacity-60">
                                <span wire:loading.remove wire:target="updateProfile">Simpan Perubahan</span>
                                <span wire:loading wire:target="updateProfile">Menyimpan…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Ringkasan akun + password --}}
            <div class="lg:col-span-5 space-y-6 reveal" style="--i: 2">
                <div class="rounded-2xl border border-stone-200/70 bg-white p-6 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-900 text-lg font-semibold text-white">
                            {{ $user?->initials() }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-base font-semibold text-stone-900">{{ $user?->name }}</p>
                            <p class="truncate text-sm text-stone-500">{{ $user?->email }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em]
                            {{ $user?->isSeller() ? 'bg-green-50 text-green-700' : ($user?->isAdmin() ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600') }}">
                            {{ $roleLabel }}
                        </span>
                        @if ($user?->created_at)
                            <span class="text-xs text-stone-500">Bergabung {{ $user->created_at->translatedFormat('F Y') }}</span>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl border border-stone-200/70 bg-white p-6 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                    <h2 class="text-sm font-semibold text-stone-900">Ubah Password</h2>
                    <p class="mt-1 text-xs text-stone-500">Gunakan minimal 8 karakter.</p>

                    <form wire:submit="updatePassword" class="mt-6 space-y-4">
                        <div>
                            <label for="p-current" class="block text-sm font-medium text-stone-700 mb-1.5">Password saat ini</label>
                            <input id="p-current" type="password" wire:model.blur="current_password" autocomplete="current-password"
                                class="{{ $inputClass }}" placeholder="••••••••" />
                            @error('current_password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="p-new" class="block text-sm font-medium text-stone-700 mb-1.5">Password baru</label>
                            <input id="p-new" type="password" wire:model.blur="password" autocomplete="new-password"
                                class="{{ $inputClass }}" placeholder="••••••••" />
                            @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="p-confirm" class="block text-sm font-medium text-stone-700 mb-1.5">Konfirmasi password baru</label>
                            <input id="p-confirm" type="password" wire:model.blur="password_confirmation" autocomplete="new-password"
                                class="{{ $inputClass }}" placeholder="••••••••" />
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword"
                            class="inline-flex items-center justify-center rounded-lg border border-stone-300 px-5 py-2.5 text-sm font-semibold text-stone-700 transition hover:border-green-700 hover:text-green-700 active:scale-[0.98] disabled:opacity-60">
                            <span wire:loading.remove wire:target="updatePassword">Perbarui Password</span>
                            <span wire:loading wire:target="updatePassword">Menyimpan…</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Produk difavoritkan --}}
        <div class="mt-14 reveal" style="--i: 3">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold tracking-tight text-stone-900">Produk Difavoritkan</h2>
                    <p class="mt-1 text-sm text-stone-500">
                        {{ $favorites->count() }} produk tersimpan
                    </p>
                </div>
                <a href="{{ route('products.index') }}"
                    class="hidden sm:inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-green-700 hover:text-green-800 transition-colors">
                    Jelajahi produk
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>

            @if ($favorites->isEmpty())
                <div class="mt-5 rounded-2xl border border-dashed border-stone-300 bg-white p-12 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100">
                        <svg class="h-6 w-6 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.312-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </span>
                    <h3 class="mt-4 text-sm font-semibold text-stone-900">Belum ada produk favorit</h3>
                    <p class="mt-1 text-xs text-stone-500">Tekan tombol Favorit pada halaman produk untuk menyimpannya di sini.</p>
                    <a href="{{ route('products.index') }}"
                        class="mt-4 inline-flex rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800 active:scale-[0.98]">
                        Jelajahi Produk
                    </a>
                </div>
            @else
                <div class="mt-5 grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
                    @foreach ($favorites as $product)
                        <div class="group overflow-hidden rounded-xl border border-stone-200/70 bg-white shadow-[0_1px_2px_rgba(28,25,23,0.05)] transition-transform duration-300 hover:-translate-y-0.5 hover:shadow-[0_12px_28px_-14px_rgba(28,25,23,0.18)]">
                            <a href="{{ route('products.show', $product->slug) }}" class="block overflow-hidden">
                                <img src="{{ $product->first_image ? (filter_var($product->first_image, FILTER_VALIDATE_URL) ? $product->first_image : Storage::url($product->first_image)) : 'https://picsum.photos/seed/cismart-p'.$product->id.'/640/480' }}"
                                    alt="{{ $product->name }}"
                                    class="aspect-[4/3] w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]" />
                            </a>
                            <div class="p-4">
                                <a href="{{ route('products.show', $product->slug) }}"
                                    class="block text-sm font-semibold leading-snug text-stone-900 line-clamp-2 transition-colors group-hover:text-green-700">
                                    {{ $product->name }}
                                </a>
                                <p class="mt-1 truncate text-xs text-stone-500">{{ $product->shop->name ?? 'Toko tidak tersedia' }}</p>
                                <p class="mt-2 text-[15px] font-bold text-stone-900">
                                    <span class="text-xs font-medium text-stone-500">Rp</span> {{ number_format($product->price, 0, ',', '.') }}
                                </p>

                                <div class="mt-3 flex items-center gap-2">
                                    <a href="{{ route('products.show', $product->slug) }}"
                                        class="flex-1 rounded-lg bg-green-700 py-2 text-center text-sm font-medium text-white transition hover:bg-green-800 active:scale-[0.98]">
                                        Lihat Produk
                                    </a>
                                    <button type="button" wire:click="removeFavorite({{ $product->id }})"
                                        wire:loading.attr="disabled" wire:target="removeFavorite({{ $product->id }})"
                                        aria-label="Hapus {{ $product->name }} dari favorit" title="Hapus dari favorit"
                                        class="rounded-lg border border-stone-300 p-2 text-stone-500 transition hover:border-red-300 hover:bg-red-50 hover:text-red-600 active:scale-[0.98] disabled:opacity-50">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.312-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
