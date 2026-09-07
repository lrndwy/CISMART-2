<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('delivery_enabled')->default(false)->after('longitude');
            $table->unsignedInteger('delivery_unit_meters')->default(100)->after('delivery_enabled');
            $table->unsignedInteger('delivery_rate')->default(0)->after('delivery_unit_meters');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_enabled',
                'delivery_unit_meters',
                'delivery_rate',
            ]);
        });
    }
};
