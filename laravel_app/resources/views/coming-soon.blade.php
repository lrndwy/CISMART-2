<x-layouts.app>
    <div class="max-w-xl mx-auto px-4 py-24 text-center">
        <p class="text-xs tracking-[0.25em] uppercase text-green-700 font-semibold mb-3">CISMART</p>
        <h1 class="text-2xl font-bold text-stone-900 mb-3">{{ $title }}</h1>
        <p class="text-stone-600 mb-8">Halaman ini segera hadir. Sementara itu, jelajahi produk UMKM Cilacap.</p>
        <a href="{{ route('home') }}"
            class="inline-block bg-green-700 text-white px-5 py-2.5 font-medium hover:bg-green-800">
            Kembali ke beranda
        </a>
    </div>
</x-layouts.app>
