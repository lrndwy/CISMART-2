@php
    $user = auth()->user();
    $hasDashboard = $user && ($user->isSeller() || $user->isAdmin());
@endphp

<header class="sticky top-0 z-50">
    {{-- Utility bar --}}
    <div class="bg-green-950 text-green-100/90 text-[11px] leading-none py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <span class="flex items-center gap-1.5 truncate">
                <flux:icon.map-pin class="h-3.5 w-3.5 shrink-0 text-green-400" />
                <span class="truncate">Khusus produk UMKM Cilacap &mdash; dikirim ke seluruh Indonesia</span>
            </span>
            <span class="hidden md:flex items-center space-x-5 text-green-200/80">
                <span class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                    (0282) 123-4567
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    info@cismart.id
                </span>
            </span>
        </div>
    </div>

    {{-- Main bar: liquid glass --}}
    <div class="bg-white/80 backdrop-blur-xl border-b border-stone-200/70 shadow-[inset_0_1px_0_rgba(255,255,255,0.7),0_8px_28px_-18px_rgba(28,25,23,0.25)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-4">
                {{-- Brand --}}
                <button wire:click="navigate('home')" class="shrink-0 active:scale-[0.97] transition" aria-label="Beranda CISMART">
                    <img src="/logo-nobg.png" alt="CISMART" class="h-12 sm:h-16 w-auto">
                </button>

                {{-- Desktop nav --}}
                <nav class="hidden md:flex items-center gap-1" aria-label="Navigasi utama">
                    @foreach ([['home', 'Beranda'], ['products', 'Produk'], ['shops', 'UMKM']] as [$key, $label])
                        <button wire:click="navigate('{{ $key }}')"
                            class="px-3.5 py-2 rounded-full text-sm transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]
                                {{ request()->routeIs($key === 'home' ? 'home' : $key . '.*')
                                    ? 'bg-green-50 text-green-800 font-semibold'
                                    : 'text-stone-600 hover:text-green-800 hover:bg-stone-100/80 font-medium' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>

                {{-- Actions --}}
                <div class="flex items-center gap-2 sm:gap-2.5">
                    {{-- Cart --}}
                    <button wire:click="navigate('cart')"
                        class="relative h-9 w-9 rounded-full border border-stone-200 bg-white flex items-center justify-center text-stone-600 hover:border-green-700 hover:text-green-700 active:scale-[0.95] transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
                        aria-label="Keranjang">
                        <flux:icon.shopping-cart class="h-[18px] w-[18px]" />
                        @if ($cartItemCount > 0)
                            <span
                                class="absolute -top-1 -right-1 bg-green-700 text-white text-[10px] min-w-[17px] h-[17px] rounded-full flex items-center justify-center font-semibold ring-2 ring-white">
                                {{ $cartItemCount }}
                            </span>
                        @endif
                    </button>

                    @auth
                        {{-- Seller / admin: dashboard menu --}}
                        @if ($hasDashboard)
                            <a href="{{ route('dashboard') }}"
                                class="hidden md:inline-flex items-center gap-2 rounded-full bg-green-900 hover:bg-green-800 text-white pl-3.5 pr-4 py-2 text-sm font-semibold shadow-[0_6px_16px_-8px_rgba(20,83,45,0.6)] hover:scale-[1.02] active:scale-[0.97] transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]">
                                <svg class="h-4 w-4 text-green-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3h11.25l5.25 5.25v12a.75.75 0 01-.75.75H3.75a.75.75 0 01-.75-.75V3.75A.75.75 0 013.75 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 18.75v-6h9v6" />
                                </svg>
                                {{ $user->isAdmin() ? 'Dashboard Admin' : 'Dashboard Penjualan' }}
                                <span class="relative flex h-2 w-2" aria-hidden="true">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-60"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
                                </span>
                            </a>
                        @endif

                        {{-- Account dropdown (desktop) --}}
                        <div x-data="{ open: false }" class="relative hidden md:block">
                            <button @click="open = !open" :aria-expanded="open" aria-haspopup="menu"
                                class="flex items-center gap-1.5 h-9 pl-1 pr-2 rounded-full border border-stone-200 bg-white hover:border-green-700 active:scale-[0.97] transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]">
                                <span class="h-7 w-7 rounded-full bg-green-900 text-white text-[11px] font-semibold flex items-center justify-center">
                                    {{ $user->initials() }}
                                </span>
                                <span class="max-w-[110px] truncate text-sm font-medium text-stone-700">{{ $user->name }}</span>
                                <svg class="h-3.5 w-3.5 text-stone-400 transition-transform duration-200" :class="open && 'rotate-180'"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false"
                                x-transition:enter="menu-in"
                                style="display: none;"
                                class="absolute right-0 mt-2 w-60 origin-top-right rounded-2xl border border-stone-200/80 bg-white/95 backdrop-blur-xl py-1.5 shadow-[0_20px_40px_-20px_rgba(28,25,23,0.3)]"
                                role="menu">
                                <div class="px-4 pt-2.5 pb-3 border-b border-stone-100">
                                    <p class="text-sm font-semibold text-stone-900 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-stone-500 truncate mt-0.5">{{ $user->email }}</p>
                                    <span class="inline-block mt-2 text-[10px] font-semibold uppercase tracking-[0.14em] rounded-full px-2 py-0.5
                                        {{ $user->isSeller() ? 'bg-green-50 text-green-700' : ($user->isAdmin() ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600') }}">
                                        {{ $user->isAdmin() ? 'Admin' : ($user->isSeller() ? 'Penjual' : 'Pembeli') }}
                                    </span>
                                </div>

                                <button wire:click="navigate('orders')" @click="open = false" role="menuitem"
                                    class="w-full text-left px-4 py-2 text-sm text-stone-700 hover:bg-stone-50 hover:text-green-800 transition-colors">
                                    Pesanan Saya
                                </button>
                                <button wire:click="navigate('profile')" @click="open = false" role="menuitem"
                                    class="w-full text-left px-4 py-2 text-sm text-stone-700 hover:bg-stone-50 hover:text-green-800 transition-colors">
                                    Profil Saya
                                </button>
                                @if ($hasDashboard)
                                    <a href="{{ route('dashboard') }}" @click="open = false" role="menuitem"
                                        class="flex items-center justify-between px-4 py-2 text-sm font-medium text-green-800 hover:bg-green-50 transition-colors">
                                        {{ $user->isAdmin() ? 'Dashboard Admin' : 'Dashboard Penjualan' }}
                                        <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H7.5a1.5 1.5 0 00-1.5 1.5v9A1.5 1.5 0 007.5 18h9a1.5 1.5 0 001.5-1.5v-6M10.5 13.5L21 3m0 0h-6m6 0v6" />
                                        </svg>
                                    </a>
                                @endif

                                <div class="border-t border-stone-100 mt-1 pt-1">
                                    <button wire:click="logout" role="menuitem"
                                        class="w-full text-left px-4 py-2 text-sm text-stone-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                        Keluar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Guest --}}
                        <a href="{{ route('register') }}" wire:navigate
                            class="hidden md:inline-flex items-center rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 hover:border-green-700 hover:text-green-800 active:scale-[0.97] transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]">
                            Daftar
                        </a>
                        <button wire:click="showAuthModal"
                            class="hidden md:inline-flex items-center rounded-full bg-green-700 hover:bg-green-800 text-white px-4 py-2 text-sm font-semibold shadow-[0_6px_16px_-8px_rgba(21,128,61,0.7)] active:scale-[0.97] transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]">
                            Masuk
                        </button>
                    @endauth

                    {{-- Mobile toggle --}}
                    <button wire:click="toggleMobileMenu" class="md:hidden h-9 w-9 rounded-full border border-stone-200 bg-white flex items-center justify-center text-stone-700 active:scale-[0.95] transition"
                        aria-label="Menu" aria-expanded="{{ $showMobileMenu ? 'true' : 'false' }}">
                        @if ($showMobileMenu)
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile menu --}}
        @if ($showMobileMenu)
            <div class="md:hidden border-t border-stone-200/70 bg-white/95 backdrop-blur-xl shadow-[inset_0_1px_0_rgba(255,255,255,0.7)]">
                <nav class="max-w-7xl mx-auto px-4 py-4 space-y-1" aria-label="Navigasi seluler">
                    @php
                        $mobileLinks = [['home', 'Beranda'], ['products', 'Produk'], ['shops', 'UMKM']];
                        if (auth()->check()) {
                            $mobileLinks[] = ['cart', 'Keranjang'];
                            $mobileLinks[] = ['orders', 'Pesanan'];
                            $mobileLinks[] = ['profile', 'Profil'];
                        }
                    @endphp
                    @foreach ($mobileLinks as $i => $item)
                        <button wire:click="navigate('{{ $item[0] }}')" style="--i: {{ $i }}"
                            class="reveal block w-full text-left px-4 py-2.5 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs($item[0] === 'home' ? 'home' : $item[0] . '.*') ? 'text-green-800 bg-green-50' : 'text-stone-600 hover:text-green-700 hover:bg-stone-50' }}">
                            {{ $item[1] }}
                        </button>
                    @endforeach

                    @auth
                        @if ($hasDashboard)
                            <a href="{{ route('dashboard') }}" style="--i: 5"
                                class="reveal mt-2 flex items-center justify-between rounded-xl bg-green-900 text-white px-4 py-3 text-sm font-semibold active:scale-[0.98] transition">
                                {{ $user->isAdmin() ? 'Dashboard Admin' : 'Dashboard Penjualan' }}
                                <svg class="h-4 w-4 text-green-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        @endif
                        <button wire:click="logout" style="--i: 6"
                            class="reveal block w-full text-left px-4 py-2.5 text-sm font-medium rounded-xl text-stone-600 hover:text-red-600 hover:bg-red-50 transition-colors">
                            Keluar
                        </button>
                    @else
                        <div class="reveal grid grid-cols-2 gap-2 pt-2" style="--i: 5">
                            <a href="{{ route('register') }}"
                                class="text-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-medium text-stone-700 hover:border-green-700 hover:text-green-800 transition-colors">Daftar</a>
                            <a href="{{ route('login') }}"
                                class="text-center rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800 transition-colors">Masuk</a>
                        </div>
                    @endauth
                </nav>
            </div>
        @endif
    </div>
</header>
