<!-- resources/views/livewire/components/header.blade.php -->
<header class="bg-white shadow-lg sticky top-0 z-50">
    {{-- Top Bar --}}
    <div class="bg-green-600 text-white py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center space-x-2">
                    <flux:icon.map-pin class="h-4 w-4" />
                    <span>Melayani seluruh Indonesia - Khusus Produk UMKM Cilacap</span>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <span>📞 (0282) 123-4567</span>
                    <span>✉️ info@cismart.id</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Header --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- ImageLogo --}}
            <img src="/logo-nobg.png" alt="" width="100">

            {{-- Search Bar --}}
            {{-- <div class="hidden md:flex flex-1 max-w-lg mx-8">
                <form wire:submit="search" class="relative w-full">
                    <input type="text" wire:model="searchQuery" placeholder="Cari produk UMKM Cilacap..."
                        class="w-full pl-4 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" />
                    <button type="submit"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-green-600">
                        <flux:icon.magnifying-glass class="h-5 w-5" />
                    </button>
                </form>
            </div> --}}

            {{-- Navigation & Actions --}}
            <div class="flex items-center space-x-4">
                {{-- Desktop Navigation --}}
                <nav class="hidden md:flex space-x-6">
                    <button wire:click="navigate('home')"
                        class="text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'text-green-600 border-b-2 border-green-600 pb-1' : 'text-gray-700 hover:text-green-600' }}">
                        Beranda
                    </button>
                    <button wire:click="navigate('products')"
                        class="text-sm font-medium transition-colors {{ request()->routeIs('products.*') ? 'text-green-600 border-b-2 border-green-600 pb-1' : 'text-gray-700 hover:text-green-600' }}">
                        Produk
                    </button>
                    <button wire:click="navigate('shops')"
                        class="text-sm font-medium transition-colors {{ request()->routeIs('shops.*') ? 'text-green-600 border-b-2 border-green-600 pb-1' : 'text-gray-700 hover:text-green-600' }}">
                        UMKM
                    </button>
                    {{-- <button wire:click="navigate('community')"
                        class="text-sm font-medium transition-colors {{ request()->routeIs('community.*') ? 'text-green-600 border-b-2 border-green-600 pb-1' : 'text-gray-700 hover:text-green-600' }}">
                        Komunitas
                    </button>
                    <button wire:click="navigate('blog')"
                        class="text-sm font-medium transition-colors {{ request()->routeIs('blog.*') ? 'text-green-600 border-b-2 border-green-600 pb-1' : 'text-gray-700 hover:text-green-600' }}">
                        Blog
                    </button> --}}
                </nav>

                {{-- Action Buttons --}}
                <div class="flex items-center space-x-2">
                    <button wire:click="navigate('cart')"
                        class="relative p-2 text-gray-600 hover:text-green-600 transition-colors">
                        <flux:icon.shopping-cart class="h-5 w-5" />
                        @if ($cartItemCount > 0)
                            <span
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $cartItemCount }}
                            </span>
                        @endif
                    </button>

                    @auth
                        <button wire:click="navigate('orders')"
                            class="hidden md:inline text-sm font-medium text-gray-700 hover:text-green-600">
                            Pesanan
                        </button>
                        <div class="relative">
                            <button wire:click="navigate('orders')"
                                class="flex items-center space-x-2 text-gray-700 hover:text-green-600">
                                <flux:icon.user class="h-5 w-5" />
                                <span class="hidden md:inline text-sm">{{ auth()->user()->name }}</span>
                            </button>
                        </div>
                        <button wire:click="logout"
                            class="hidden md:inline text-sm font-medium text-gray-500 hover:text-red-600">
                            Keluar
                        </button>
                    @else
                        <button wire:click="showAuthModal"
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                            Masuk
                        </button>
                    @endauth

                    {{-- Mobile Menu Button --}}
                    <button wire:click="toggleMobileMenu" class="md:hidden p-2 text-gray-600 hover:text-green-600">
                        <flux:icon.bars-3 class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Search --}}
        {{-- <div class="md:hidden pb-4">
            <form wire:submit="search" class="relative">
                <input type="text" wire:model="searchQuery" placeholder="Cari produk UMKM Cilacap..."
                    class="w-full pl-4 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" />
                <button type="submit"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-green-600">
                    <flux:icon.magnifying-glass class="h-5 w-5" />
                </button>
            </form>
        </div> --}}

        {{-- Mobile Menu --}}
        @if ($showMobileMenu)
            <div class="md:hidden border-t border-gray-200 py-4">
                <nav class="space-y-2">
                    <button wire:click="navigate('home')"
                        class="block w-full text-left px-2 py-2 text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'text-green-600 bg-green-50 rounded' : 'text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded' }}">
                        Beranda
                    </button>
                    <button wire:click="navigate('products')"
                        class="block w-full text-left px-2 py-2 text-sm font-medium transition-colors {{ request()->routeIs('products.*') ? 'text-green-600 bg-green-50 rounded' : 'text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded' }}">
                        Produk
                    </button>
                    <button wire:click="navigate('shops')"
                        class="block w-full text-left px-2 py-2 text-sm font-medium transition-colors {{ request()->routeIs('shops.*') ? 'text-green-600 bg-green-50 rounded' : 'text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded' }}">
                        UMKM
                    </button>
                    {{-- <button wire:click="navigate('community')"
                        class="block w-full text-left px-2 py-2 text-sm font-medium transition-colors {{ request()->routeIs('community.*') ? 'text-green-600 bg-green-50 rounded' : 'text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded' }}">
                        Komunitas
                    </button>
                    <button wire:click="navigate('blog')"
                        class="block w-full text-left px-2 py-2 text-sm font-medium transition-colors {{ request()->routeIs('blog.*') ? 'text-green-600 bg-green-50 rounded' : 'text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded' }}">
                        Blog
                    </button> --}}
                </nav>
            </div>
        @endif
    </div>
</header>
