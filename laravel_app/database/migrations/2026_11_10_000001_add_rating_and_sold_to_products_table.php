<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Agregat ulasan & penjualan nyata. Sebelumnya ProductsPage
            // mengurutkan/filter memakai kolom rating & sold yang tidak ada.
            $table->decimal('rating', 3, 2)->default(0)->after('status');
            $table->unsignedInteger('total_reviews')->default(0)->after('rating');
            $table->unsignedInteger('sold_count')->default(0)->after('total_reviews');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['rating', 'total_reviews', 'sold_count']);
        });
    }
};