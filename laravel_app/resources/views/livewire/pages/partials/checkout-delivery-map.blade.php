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
            class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800 active:scale-[0.98] transition">
            <span x-show="!locating">Gunakan lokasi saya</span>
            <span x-show="locating">Membaca lokasi...</span>
        </button>
        <button type="button" @click="$wire.fillAddressFromMap()"
            class="inline-flex items-center gap-2 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 hover:border-green-700 hover:text-green-800 active:scale-[0.98] transition">
            Isi alamat dari titik
        </button>
        <p class="self-center text-xs text-stone-500">Atau klik peta, lalu geser pin.</p>
    </div>

    <p class="text-xs text-stone-500">
        Alamat lengkap pada formulir terisi otomatis dari titik yang Anda pilih. Geser pin bila titiknya kurang tepat.
    </p>

    <p x-show="locateError" x-text="locateError" class="text-sm text-red-600"></p>

    <div x-ref="mapContainer" class="w-full h-72 rounded-xl border border-stone-200 bg-stone-100" style="z-index: 0;"></div>

    <div class="flex flex-wrap gap-4 text-xs text-stone-600">
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-green-700 inline-block"></span> Lokasi toko</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-stone-900 inline-block"></span> Lokasi Anda (bisa digeser)</span>
    </div>
</div>
