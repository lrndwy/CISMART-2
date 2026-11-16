<?php

use App\Console\Commands\ExpireUnpaidOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Batalkan otomatis pesanan QRIS yang tak dibayar & COD yang tak pernah dikonfirmasi.
Schedule::command(ExpireUnpaidOrders::class)->hourly()->withoutOverlapping();