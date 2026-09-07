<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('qris_image')->nullable()->after('description');
            $table->text('qris_static_payload')->nullable()->after('qris_image');
            $table->enum('qris_mode', ['dynamic', 'static'])->default('static')->after('qris_static_payload');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['qris_image', 'qris_static_payload', 'qris_mode']);
        });
    }
};
