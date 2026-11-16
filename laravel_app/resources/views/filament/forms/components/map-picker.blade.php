{{-- resources/views/filament/forms/components/map-picker.blade.php --}}

<div x-data="{
    latitude: $wire.entangle('data.latitude'),
    longitude: $wire.entangle('data.longitude'),
    map: null,
    marker: null,
    isSearching: false,
    searchQuery: '',
    searchResults: [],
    searchDone: false,
    locateError: '',

    init() {
        this.$nextTick(() => {
            try { this.initMap(); } catch (e) { console.error('init error', e); }
        });
    },

    initMap() {
        if (typeof L === 'undefined') {
            console.warn('Leaflet belum tersedia, retry initMap...');
            setTimeout(() => this.initMap(), 150);
            return;
        }

        if (this.map) {
            try { this.map.remove(); } catch (e) { console.warn('Gagal remove map (ignored):', e); }
            this.map = null;
            this.marker = null;
        }

        // Koordinat default: pusat kota Cilacap.
        const defaultLat = -7.7181;
        const defaultLng = 109.0181;
        const defaultZoom = 13;

        const lat = parseFloat(this.latitude) || defaultLat;
        const lng = parseFloat(this.longitude) || defaultLng;

        try {
            this.map = L.map(this.$refs.mapContainer).setView([lat, lng], defaultZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(this.map);

            this.marker = L.marker([lat, lng], {
                draggable: true,
                title: 'Geser untuk mengubah lokasi'
            }).addTo(this.map);

            // Jika koordinat awal berasal dari default (belum diisi), tampilkan popup kecil
            if ((!this.latitude || !this.longitude) || (this.latitude === '' && this.longitude === '')) {
                this.marker.bindPopup('Lokasi default: Cilacap').openPopup();
                setTimeout(() => { try { this.map.closePopup(); } catch (e) {} }, 1600);
            }

            this.marker.on('dragend', (e) => {
                const position = e.target.getLatLng();
                this.updatePosition(position.lat, position.lng);
            });

            this.map.on('click', (e) => {
                const position = e.latlng;
                if (this.marker) this.marker.setLatLng(position);
                this.updatePosition(position.lat, position.lng);
            });

            if (window.L && window.L.Control && window.L.Control.FullScreen) {
                try {
                    this.map.addControl(new L.Control.FullScreen({ position: 'topleft' }));
                } catch (err) {
                    console.warn('Fullscreen control error:', err);
                }
            }

            setTimeout(() => {
                try { this.map.invalidateSize(); } catch (e) {}
            }, 200);

            this.$watch('latitude', (value) => {
                const latVal = parseFloat(value);
                const lngVal = parseFloat(this.longitude);
                if (!isNaN(latVal) && !isNaN(lngVal) && this.marker) {
                    this.marker.setLatLng([latVal, lngVal]);
                    this.map.panTo([latVal, lngVal]);
                }
            });

            this.$watch('longitude', (value) => {
                const latVal = parseFloat(this.latitude);
                const lngVal = parseFloat(value);
                if (!isNaN(latVal) && !isNaN(lngVal) && this.marker) {
                    this.marker.setLatLng([latVal, lngVal]);
                    this.map.panTo([latVal, lngVal]);
                }
            });

        } catch (err) {
            console.error('Gagal inisialisasi peta:', err);
        }
    },

    updatePosition(lat, lng) {
        try {
            this.latitude = Number(lat).toFixed(8);
            this.longitude = Number(lng).toFixed(8);
        } catch (e) {
            console.warn('updatePosition error', e);
        }
    },

    async searchLocation() {
        if (!this.searchQuery || !this.searchQuery.trim()) return;
        this.isSearching = true;
        this.searchResults = [];
        this.searchDone = false;

        try {
            const q = encodeURIComponent(this.searchQuery);
            const url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + q + '&limit=5';
            const response = await fetch(url);
            if (!response.ok) throw new Error('Geocoding request failed: ' + response.status);
            const data = await response.json();
            this.searchResults = data || [];
            this.searchDone = true;
        } catch (error) {
            console.error('Search error:', error);
            this.searchDone = true;
        } finally {
            this.isSearching = false;
        }
    },

    selectSearchResult(result) {
        const lat = parseFloat(result.lat);
        const lng = parseFloat(result.lon);
        if (this.marker) this.marker.setLatLng([lat, lng]);
        if (this.map) this.map.setView([lat, lng], 15);
        this.updatePosition(lat, lng);
        this.searchQuery = result.display_name || this.searchQuery;
        this.searchResults = [];
        this.searchDone = false;
        try { this.map.invalidateSize(); } catch (e) {}
    },

    clearSearch() {
        this.searchQuery = '';
        this.searchResults = [];
        this.searchDone = false;
    },

    getCurrentLocation() {
        this.locateError = '';

        if (! ('geolocation' in navigator)) {
            this.locateError = 'Browser ini tidak mendukung pembacaan lokasi.';
            return;
        }

        if (window.isSecureContext === false) {
            this.locateError = 'Pembacaan lokasi butuh koneksi aman (HTTPS). Buka panel lewat https:// atau localhost.';
            return;
        }

        const request = (options) => new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, options);
        });

        // Coba pembacaan cepat (akurasi kasar) dulu, baru presisi tinggi.
        // enableHighAccuracy langsung dengan timeout pendek sering timeout di
        // perangkat tanpa GPS walau izin lokasi sudah diberikan.
        request({ enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 })
            .then(null, (error) => {
                if (error && error.code === 1) {
                    throw error;
                }

                return request({ enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
            })
            .then((position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                if (this.marker) this.marker.setLatLng([lat, lng]);
                if (this.map) this.map.setView([lat, lng], 15);
                this.updatePosition(lat, lng);
            })
            .catch((error) => {
                console.error('Geolocation error:', error);
                this.locateError = this.locationErrorMessage(error);
            });
    },

    locationErrorMessage(error) {
        // 1 = PERMISSION_DENIED, 2 = POSITION_UNAVAILABLE, 3 = TIMEOUT
        if (error && error.code === 1) {
            return 'Akses lokasi diblokir untuk situs ini. Aktifkan izin lokasi di pengaturan situs browser, lalu muat ulang.';
        }
        if (error && error.code === 2) {
            return 'Lokasi perangkat tidak tersedia saat ini. Coba lagi, atau tandai titik di peta.';
        }
        if (error && error.code === 3) {
            return 'Waktu pembacaan lokasi habis. Coba lagi, atau tandai titik di peta.';
        }
        return 'Tidak dapat mengakses lokasi. Tandai titik di peta secara manual.';
    }
}" x-init="init()" class="space-y-4">

    {{-- Pencarian lokasi --}}
    <div class="relative">
        <div class="flex flex-col gap-2 sm:flex-row">
            <div class="relative flex-1">
                <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                    <x-filament::input type="text" x-model="searchQuery" x-on:keydown.enter.prevent="searchLocation()"
                        placeholder="Cari jalan, kelurahan, atau nama tempat (contoh: Jl. Gatot Subroto Cilacap)" />
                </x-filament::input.wrapper>

                {{-- Hasil pencarian --}}
                <div x-show="searchResults.length > 0" x-cloak x-transition.origin.top
                    class="absolute inset-x-0 top-full z-20 mt-1 max-h-64 overflow-y-auto rounded-xl bg-white shadow-lg ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/20">
                    <template x-for="result in searchResults" :key="result.place_id">
                        <button type="button" x-on:click="selectSearchResult(result)"
                            class="flex w-full items-start gap-2.5 border-b border-gray-100 px-3 py-2.5 text-left transition last:border-0 hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                            <x-filament::icon icon="heroicon-m-map-pin"
                                class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />
                            <span class="text-sm text-gray-950 dark:text-white" x-text="result.display_name"></span>
                        </button>
                    </template>
                </div>

                {{-- Tidak ada hasil --}}
                <p x-show="searchDone && searchResults.length === 0" x-cloak
                    class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                    Lokasi tidak ditemukan. Coba kata kunci lain, atau klik langsung pada peta.
                </p>
            </div>

            <div class="flex gap-2">
                <x-filament::button type="button" x-on:click="searchLocation()" x-bind:disabled="isSearching"
                    icon="heroicon-m-magnifying-glass">
                    <span x-show="!isSearching">Cari</span>
                    <span x-show="isSearching" x-cloak>Cari…</span>
                </x-filament::button>

                <x-filament::button type="button" color="gray" x-on:click="getCurrentLocation()"
                    icon="heroicon-m-map-pin">
                    Lokasi Saya
                </x-filament::button>
            </div>
        </div>

        <p x-show="locateError" x-text="locateError" x-cloak
            class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
    </div>

    {{-- Peta --}}
    <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/10 dark:ring-white/20">
        <div x-ref="mapContainer" id="shop-map" wire:ignore
            class="w-full bg-gray-50 dark:bg-gray-800" style="height: 420px; z-index: 0;"></div>
    </div>

    {{-- Ringkasan titik terpilih --}}
    <div
        class="flex flex-wrap items-center gap-x-6 gap-y-2 rounded-xl bg-gray-50 px-4 py-3 text-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
        <span class="flex items-center gap-1.5">
            <span class="text-gray-500 dark:text-gray-400">Latitude</span>
            <span class="font-mono font-medium text-gray-950 dark:text-white"
                x-text="latitude ? latitude : 'belum dipilih'"></span>
        </span>
        <span class="flex items-center gap-1.5">
            <span class="text-gray-500 dark:text-gray-400">Longitude</span>
            <span class="font-mono font-medium text-gray-950 dark:text-white"
                x-text="longitude ? longitude : 'belum dipilih'"></span>
        </span>
        <x-filament::badge x-show="latitude && longitude" x-cloak color="success" icon="heroicon-m-check-circle"
            class="ml-auto">
            Lokasi sudah ditandai
        </x-filament::badge>
        <x-filament::badge x-show="!latitude || !longitude" x-cloak color="warning" icon="heroicon-m-exclamation-triangle"
            class="ml-auto">
            Belum ada lokasi
        </x-filament::badge>
    </div>

    {{-- Petunjuk --}}
    <div class="flex gap-3 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
        <x-filament::icon icon="heroicon-m-information-circle"
            class="mt-0.5 h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500" />
        <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
            <p class="font-semibold">Cara menentukan lokasi toko</p>
            <ul class="list-inside list-disc space-y-0.5 text-xs">
                <li>Klik pada peta untuk menaruh pin, atau geser pin merah ke titik yang tepat.</li>
                <li>Gunakan kolom pencarian bila Anda hanya tahu nama jalan atau daerahnya.</li>
                <li>Nilai Latitude dan Longitude di bawah peta ikut terisi otomatis.</li>
                <li>Lokasi ini dipakai pembeli sebagai titik awal perhitungan ongkir.</li>
            </ul>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>
@endpush
