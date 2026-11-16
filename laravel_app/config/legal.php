<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Versi dokumen legal
    |--------------------------------------------------------------------------
    | Teks dokumennya ada di resources/views/legal/*.blade.php. Yang tinggal di
    | sini hanya versi dan tanggal berlakunya, karena dua nilai itu dipakai di
    | tiga tempat sekaligus: halaman legal, label checkbox, dan baris
    | persetujuan (agreement_acceptances).
    |
    | Menaikkan `version` membuat penjual diminta menyetujui ulang pada submit
    | berikutnya. Jangan ubah nilainya tanpa mengubah teks dokumen.
    */
    'terms' => [
        'version' => '1.0',
        'effective_at' => '2026-12-01',
    ],

    'seller_agreement' => [
        'version' => '1.0',
        'effective_at' => '2026-12-01',
    ],
];