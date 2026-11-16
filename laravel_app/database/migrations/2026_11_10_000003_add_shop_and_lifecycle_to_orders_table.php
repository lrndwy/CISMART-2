<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Order kini milik satu toko (checkout multi-toko dipecah per toko).
            $table->foreignId('shop_id')->nullable()->after('user_id')
                ->constrained()->nullOnDelete();
            // Batas waktu pembayaran untuk QRIS unpaid.
            $table->timestamp('expires_at')->nullable()->after('cancelled_at');
            $table->softDeletes();

            $table->index(['shop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'status']);
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('shop_id');
            $table->dropColumn('expires_at');
        });
    }
};