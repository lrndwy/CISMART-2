<!-- resources/views/livewire/components/footer.blade.php -->
<footer class="bg-gray-900 text-white">
    {{-- Main Footer --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Company Info --}}
            <div>
                <div class="flex items-center mb-4">
                    <img src="{{ asset('/logo-nobg.png') }}" alt="CISMART Logo" width="100">
                </div>
                <p class="text-gray-400 mb-4">
                    Platform digital untuk mendukung UMKM Cilacap berkembang dan menjangkau pasar yang lebih luas di
                    seluruh Indonesia.
                </p>
                <div class="flex space-x-4">
                    <button class="text-gray-400 hover:text-green-400 transition-colors">
                        <x-bi-facebook class="h-5 w-5" />
                    </button>
                    <button class="text-gray-400 hover:text-green-400 transition-colors">
                        <x-bi-instagram class="h-5 w-5" />
                    </button>
                    <button class="text-gray-400 hover:text-green-400 transition-colors">
                        <x-bi-youtube class="h-5 w-5" />
                    </button>
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <h4 class="text-lg font-semibold mb-4">Menu Cepat</h4>
                <ul class="space-y-2">
                    <li>
                        <button wire:click="navigate('home')" class="text-gray-400 hover:text-white transition-colors">
                            Beranda
                        </button>
                    </li>
                    <li>
                        <button wire:click="navigate('products')"
                            class="text-gray-400 hover:text-white transition-colors">
                            Produk UMKM
                        </button>
                    </li>
                    <li>
                        <button wire:click="navigate('community')"
                            class="text-gray-400 hover:text-white transition-colors">
                            Komunitas
                        </button>
                    </li>
                    <li>
                        <button wire:click="navigate('blog')" class="text-gray-400 hover:text-white transition-colors">
                            Blog & Artikel
                        </button>
                    </li>
                    <li>
                        <button wire:click="navigate('dashboard')"
                            class="text-gray-400 hover:text-white transition-colors">
                            Dashboard
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Support --}}
            <div>
                <h4 class="text-lg font-semibold mb-4">Dukungan</h4>
                <ul class="space-y-2 text-gray-400">
                    <li>
                        <button wire:click="navigate('community')" class="hover:text-white transition-colors">
                            Cara Berbelanja
                        </button>
                    </li>
                    <li>
                        <button wire:click="navigate('community')" class="hover:text-white transition-colors">
                            Cara Berjualan
                        </button>
                    </li>
                    <li>
                        <button class="hover:text-white transition-colors">
                            Bantuan & FAQ
                        </button>
                    </li>
                    <li>
                        <button class="hover:text-white transition-colors">
                            Syarat & Ketentuan
                        </button>
                    </li>
                    <li>
                        <button class="hover:text-white transition-colors">
                            Kebijakan Privasi
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Contact Info --}}
            <div>
                <h4 class="text-lg font-semibold mb-4">Kontak Kami</h4>
                <div class="space-y-3">
                    <div class="flex items-center space-x-2 text-gray-400">
                        <flux:icon.map-pin class="h-4 w-4 flex-shrink-0" />
                        <span class="text-sm">
                            Jl. Sudirman No. 123<br>
                            Cilacap, Jawa Tengah 53211
                        </span>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-400">
                        <flux:icon.phone class="h-4 w-4" />
                        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                            class="text-sm hover:text-green-400 transition-colors">
                            WhatsApp: 0812-3456-7890
                        </a>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-400">
                        <flux:icon.envelope class="h-4 w-4" />
                        <span class="text-sm">info@cismart.id</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Footer --}}
    <div class="border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    © {{ date('Y') }} CISMART. Semua hak cipta dilindungi.
                </p>
                <div class="flex space-x-6 mt-2 md:mt-0">
                    <span class="text-gray-400 text-sm">Powered by</span>
                    <span class="text-green-400 text-sm font-medium">Pemerintah Kabupaten Cilacap</span>
                </div>
            </div>
        </div>
    </div>
</footer>
