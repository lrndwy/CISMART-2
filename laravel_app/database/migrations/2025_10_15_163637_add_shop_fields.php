<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->default(0)->after('status');
            $table->integer('total_reviews')->default(0)->after('rating');
            $table->integer('response_rate')->default(0)->after('total_reviews');
            $table->boolean('is_verified')->default(false)->after('response_rate');
            $table->boolean('is_power_seller')->default(false)->after('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'rating',
                'total_reviews',
                'response_rate',
                'is_verified',
                'is_power_seller'
            ]);
        });
    }
};
