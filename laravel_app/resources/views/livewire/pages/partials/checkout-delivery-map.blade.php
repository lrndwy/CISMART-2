@php
    $shopLat = $checkoutShop?->latitude;
    $shopLng = $checkoutShop?->longitude;
@endphp

<div
    x-data="checkoutDeliveryMap()"
    data-shop-lat="{{ $shopLat }}"
    data-shop-lng="{{ $shopLng }}"
    wire:ignore
    class="space-y-3"
>
    <div class="flex flex-wrap gap-2">
        <button type="button" @click="getCurrentLocation()"
            class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            <span x-show="!locating">Gunakan lokasi saya</span>
            <span x-show="locating">Membaca lokasi...</span>
        </button>
        <p class="self-center text-xs text-gray-500">Atau klik peta, lalu geser pin hijau.</p>
    </div>

    <p x-show="locateError" x-text="locateError" class="text-sm text-red-600"></p>

    <div x-ref="mapContainer" class="w-full h-72 rounded-lg border border-gray-300 bg-gray-100" style="z-index: 0;"></div>

    <div class="flex flex-wrap gap-4 text-xs text-gray-600">
        <span class="inline-flex items-center gap-1"><span>🏪</span> Lokasi toko</span>
        <span class="inline-flex items-center gap-1"><span>📍</span> Lokasi Anda (bisa digeser)</span>
    </div>
</div>
