<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Batas waktu pembayaran
    |--------------------------------------------------------------------------
    | Pesanan QRIS yang belum dibayar akan dibatalkan otomatis setelah jendela
    | waktu ini lewat (lihat App\Console\Commands\ExpireUnpaidOrders).
    */
    'payment_window_hours' => env('ORDER_PAYMENT_WINDOW_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Masa berlaku pesanan COD / pickup
    |--------------------------------------------------------------------------
    | Pesanan yang tidak pernah dikonfirmasi penjual diproses lewat perintah
    | orders:expire juga, tapi dengan batas yang lebih longgar.
    */
    'confirmation_window_hours' => env('ORDER_CONFIRMATION_WINDOW_HOURS', 72),
];