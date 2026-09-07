{{-- resources/views/filament/forms/components/map-picker.blade.php --}}

<div x-data="{
    latitude: $wire.entangle('data.latitude'),
    longitude: $wire.entangle('data.longitude'),
    map: null,
    marker: null,
    isSearching: false,
    searchQuery: '',
    searchResults: [],

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

        // DEFAULT COORDINATES FOR CILACAP
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
                title: 'Drag to change location'
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

            if (window.L && window.L.Control && window.L.Control.Fullscreen) {
                try {
                    this.map.addControl(new L.Control.Fullscreen({ position: 'topleft' }));
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

        try {
            const q = encodeURIComponent(this.searchQuery);
            const url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + q + '&limit=5';
            const response = await fetch(url);
            if (!response.ok) throw new Error('Geocoding request failed: ' + response.status);
            const data = await response.json();
            this.searchResults = data || [];
        } catch (error) {
            console.error('Search error:', error);
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
        try { this.map.invalidateSize(); } catch (e) {}
    },

    getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    if (this.marker) this.marker.setLatLng([lat, lng]);
                    if (this.map) this.map.setView([lat, lng], 15);
                    this.updatePosition(lat, lng);
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    alert('Tidak dapat mengakses lokasi. Pastikan izin lokasi sudah diberikan.');
                }, { enableHighAccuracy: true, timeout: 10000 }
            );
        } else {
            alert('Browser tidak mendukung geolocation.');
        }
    }
}" x-init="init()" class="space-y-3">
    <!-- Search Box -->
    <div class="relative">
        <div class="flex gap-3">
            <div class="flex-1 relative">
                <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchLocation()"
                    placeholder="Cari lokasi (contoh: Cilacap)"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm transition duration-75 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500" />

                <!-- Search Results Dropdown -->
                <div x-show="searchResults.length > 0" x-transition
                    class="absolute top-full left-0 right-0 z-10 mt-1 max-h-60 overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800">
                    <template x-for="result in searchResults" :key="result.place_id">
                        <div @click="selectSearchResult(result)"
                            class="border-b border-gray-200 px-4 py-2.5 cursor-pointer transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50 last:border-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="result.display_name">
                            </p>
                        </div>
                    </template>
                </div>
            </div>

            <button type="button" @click="searchLocation()" :disabled="isSearching"
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition duration-75 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-70 dark:focus:ring-offset-gray-900">
                <span x-show="!isSearching">Cari</span>
                <span x-show="isSearching" class="inline-flex animate-spin">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </span>
            </button>

            <button type="button" @click="getCurrentLocation()" title="Gunakan lokasi saat ini"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-900 transition duration-75 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                <span class="mr-2">📍</span>
                <span>Lokasi Saya</span>
            </button>
        </div>
    </div>

    <!-- Map Container -->
    <div class="relative">
        <div x-ref="mapContainer" id="shop-map" wire:ignore
            class="w-full rounded-lg border border-gray-300 bg-gray-50 shadow-sm dark:border-gray-600 dark:bg-gray-800"
            style="height: 450px; z-index: 0;"></div>
    </div>

    <!-- Info Box -->
    <div class="flex gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/20">
        {{-- <svg class="h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor"
            viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd" />
        </svg> --}}
        <div class="flex-1 text-sm text-blue-800 dark:text-blue-300">
            <p class="mb-2 font-semibold">Cara menggunakan peta:</p>
            <ul class="space-y-1 text-xs">
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 dark:text-blue-400 flex-shrink-0">•</span>
                    <span>Klik pada peta untuk memilih lokasi</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 dark:text-blue-400 flex-shrink-0">•</span>
                    <span>Drag marker (pin merah) untuk menggeser posisi</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 dark:text-blue-400 flex-shrink-0">•</span>
                    <span>Gunakan search box untuk mencari alamat</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 dark:text-blue-400 flex-shrink-0">•</span>
                    <span>Klik "Lokasi Saya" untuk menggunakan lokasi saat ini</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 dark:text-blue-400 flex-shrink-0">•</span>
                    <span>Koordinat akan otomatis terisi di field Latitude dan Longitude</span>
                </li>
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
