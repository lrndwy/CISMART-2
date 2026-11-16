<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Ulasan produk: produk dinilai per pembelian, sedangkan ulasan
            // lama (level toko) tetap valid dengan product_id = null.
            $table->foreignId('product_id')->nullable()->after('shop_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->after('product_id')
                ->constrained()->nullOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'shop_id']);
        });

        // Satu ulasan per pembeli per produk.
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id']);
            $table->index(['product_id', 'created_at']);
        });

        // Ulasan level toko (product_id null) tetap satu per pembeli per toko.
        DB::statement('CREATE UNIQUE INDEX reviews_user_shop_level_unique
            ON reviews (user_id, shop_id) WHERE product_id IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS reviews_user_shop_level_unique');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
            $table->dropIndex(['product_id', 'created_at']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropConstrainedForeignId('order_item_id');
            $table->unique(['user_id', 'shop_id']);
        });
    }
};