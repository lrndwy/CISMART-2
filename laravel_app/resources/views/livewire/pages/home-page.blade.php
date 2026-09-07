{{-- Satu root element agar wire:id membungkus seluruh konten (termasuk tombol). --}}
<div>
<style>
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes pulse-glow {

        0%,
        100% {
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
        }

        50% {
            box-shadow: 0 0 40px rgba(34, 197, 94, 0.6);
        }
    }

    .float-animation {
        animation: float 3s ease-in-out infinite;
    }

    .glow-button {
        animation: pulse-glow 2s ease-in-out infinite;
    }

    .hero-gradient {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        position: relative;
        overflow: visible;
    }

    .hero-content {
        position: relative;
        z-index: 10;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
    }

    .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #fde047;
    }

    .stat-label {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 4px;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 32px !important;
        }

        .stats-grid {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 12px !important;
        }
    }
</style>
<div class="space-y-16">
    {{-- Hero Section --}}
    <div>
        <section class="hero-gradient relative min-h-screen flex items-center justify-center overflow-hidden pb-16">
            <!-- Main Content -->
            <div class="hero-content w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">

                    <!-- Left Content -->
                    <div class="text-center lg:text-left pt-8 lg:pt-0">
                        <div class="mb-8 lg:mb-6 flex justify-center lg:justify-start">
                            <span
                                class="inline-block bg-yellow-300 text-green-900 px-4 py-2 rounded-full text-xs sm:text-sm font-semibold">
                                ✨ Terdepan dalam E-commerce UMKM
                            </span>
                        </div>

                        <h1 class="hero-title text-4xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                            Pasar Online
                            <span class="block text-yellow-300 drop-shadow-lg">UMKM Cilacap</span>
                        </h1>

                        <p class="text-lg lg:text-xl text-gray-100 mb-8 leading-relaxed max-w-md mx-auto lg:mx-0">
                            Temukan ribuan produk unggulan dari UMKM Cilacap. Dari makanan khas, kerajinan tangan,
                            hingga produk inovatif. Belanja mudah, dukung ekonomi lokal! 🚀
                        </p>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-12 justify-center lg:justify-start">
                            <button wire:click="navigateToProducts"
                                class="glow-button bg-yellow-400 hover:bg-yellow-300 text-gray-900 px-8 py-4 rounded-lg font-bold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-center gap-2">
                                Jelajahi Produk
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </button>
                            <button wire:click="navigateToCommunity"
                                class="border-2 border-white text-white hover:bg-white hover:text-green-600 px-8 py-4 rounded-lg font-bold text-lg transition-all duration-300">
                                Bergabung Komunitas
                            </button>
                        </div>

                        <!-- Stats -->
                        <div class="stats-grid grid grid-cols-3 gap-4">
                            <div class="stat-card">
                                <div class="stat-number">200+</div>
                                <div class="stat-label">Produk UMKM</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">50+</div>
                                <div class="stat-label">Penjual Terpercaya</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">500+</div>
                                <div class="stat-label">Pelanggan Puas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Content - Logo -->
                    <div class="flex justify-center items-center">
                        <div class="relative w-full max-w-md">
                            <!-- Logo container -->
                            <div
                                class="relative float-animation rounded-3xl p-8 shadow-2xl bg-white bg-opacity-10 backdrop-blur-md border border-white border-opacity-20">
                                <img src="/logo-nobg.png" alt="ISMART Logo" class="w-full h-auto drop-shadow-2xl">
                            </div>

                            <!-- Floating badge -->
                            <div
                                class="absolute bottom-0 right-0 lg:-bottom-4 lg:right-8 bg-yellow-400 text-gray-900 px-4 lg:px-6 py-2 rounded-full font-bold shadow-lg text-xs lg:text-sm whitespace-nowrap transform translate-x-2 translate-y-2 lg:translate-x-0 lg:translate-y-0">
                                Terpercaya ✓
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    {{-- Stats Section --}}
    {{-- <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($stats as $stat)
                <div
                    class="bg-white rounded-xl shadow-lg p-6 text-center border border-gray-100 hover:shadow-xl transition-shadow">
                    <div class="bg-green-100 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <flux:icon :name="$stat['icon']" class="h-8 w-8 text-green-600" />
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">{{ $stat['value'] }}</div>
                    <div class="text-gray-600 text-sm font-medium">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section> --}}

    {{-- Featured Products --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Produk Unggulan</h2>
                <p class="text-gray-600">Produk terbaik pilihan dari UMKM Cilacap</p>
            </div>
            <button wire:click="navigateToProducts"
                class="text-green-600 hover:text-green-700 font-semibold flex items-center transition-colors">
                Lihat Semua
                <flux:icon.arrow-right class="ml-1 h-4 w-4" />
            </button>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
            @foreach ($featuredProducts as $product)
                <livewire:components.product-card :product="$product" :key="'product-' . $product->id" />
            @endforeach
        </div>
    </section>

    {{-- Top Stores --}}
    <section class="bg-gray-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">UMKM Terpopuler</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Toko-toko UMKM dengan rating terbaik dan produk berkualitas tinggi
                </p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                @foreach ($topStores as $store)
                    <livewire:components.store-card :shop="$store" :key="'store-' . $store->id" />
                @endforeach
            </div>
            <div class="text-center mt-8">
                <button wire:click="navigateToProducts"
                    class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors inline-flex items-center gap-2">
                    Jelajahi Semua UMKM
                    <flux:icon.arrow-right class="h-5 w-5" />
                </button>
            </div>
        </div>
    </section>

    {{-- Benefits --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Mengapa Memilih CISMART?</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Platform terpercaya yang menghubungkan UMKM Cilacap dengan konsumen di seluruh Indonesia
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach ($benefits as $benefit)
                <div class="text-center group">
                    <div class="text-4xl mb-4 transform group-hover:scale-110 transition-transform">
                        {{ $benefit['icon'] }}
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ $benefit['title'] }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $benefit['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA Section --}}
    {{-- CTA Section --}}
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-12 lg:p-16 border border-green-100">
                <div class="text-center">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                        Siap Memulai Berbisnis Online?
                    </h2>
                    <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                        Bergabunglah dengan ribuan UMKM Cilacap yang telah merasakan kemudahan berjualan di platform
                        ISMART.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="handleSellerRegistration"
                            class="bg-green-600 hover:bg-green-700 text-white px-8 py-4 rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-md">
                            Daftar Sebagai Penjual
                        </button>
                        <button wire:click="navigateToCommunity"
                            class="border-2 border-green-600 text-green-600 hover:bg-green-50 px-8 py-4 rounded-lg font-semibold transition-all duration-300">
                            Pelajari Lebih Lanjut
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact & Location Section --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- Contact Info --}}
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Hubungi Kami</h2>
                <p class="text-gray-600 mb-8">
                    Tim CISMART siap membantu Anda. Hubungi kami untuk pertanyaan, dukungan teknis, atau konsultasi
                    bisnis UMKM.
                </p>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <flux:icon.map-pin class="h-6 w-6 text-green-600" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Alamat Kantor</h3>
                            <p class="text-gray-600">
                                Jl. Sudirman No. 123<br>
                                Cilacap, Jawa Tengah 53211
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <flux:icon.phone class="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Telepon</h3>
                            <p class="text-gray-600">(0282) 123-4567</p>
                            <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                                class="text-green-600 hover:text-green-700 transition-colors">
                                WhatsApp: 0812-3456-7890
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <flux:icon.envelope class="h-6 w-6 text-purple-600" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Email</h3>
                            <p class="text-gray-600">info@cismart.id</p>
                            <p class="text-gray-600">support@cismart.id</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Mulai Sekarang</h2>
                <div class="space-y-4">
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Untuk Pembeli</h3>
                        <p class="text-gray-600 mb-4">
                            Temukan produk UMKM terbaik dari Cilacap dengan kualitas terjamin dan harga terjangkau.
                        </p>
                        <button wire:click="navigateToProducts"
                            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors w-full">
                            Mulai Belanja
                        </button>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Untuk UMKM</h3>
                        <p class="text-gray-600 mb-4">
                            Bergabunglah dengan platform terpercaya untuk memperluas jangkauan bisnis Anda.
                        </p>
                        <button wire:click="handleSellerRegistration"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors w-full">
                            Daftar Sebagai Penjual
                        </button>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Bergabung Komunitas</h3>
                        <p class="text-gray-600 mb-4">
                            Dapatkan tips, berbagi pengalaman, dan terhubung dengan sesama pelaku UMKM.
                        </p>
                        <button wire:click="navigateToCommunity"
                            class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors w-full">
                            Gabung Komunitas
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div>
