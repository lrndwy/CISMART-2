{{-- Tombol kembali ke situs publik. Panel Filament tidak memuat utility Tailwind aplikasi,
     jadi gunakan komponen icon-button Filament agar ukuran/alignment konsisten dengan topbar. --}}
<x-filament::icon-button tag="a" :href="route('home')" color="gray" size="sm" outlined
    icon="heroicon-m-arrow-left-on-rectangle"
    label="Kembali ke Situs" tooltip="Kembali ke Situs" />
