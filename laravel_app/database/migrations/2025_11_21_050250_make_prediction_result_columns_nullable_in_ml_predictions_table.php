<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ml_predictions', function (Blueprint $table) {
            // Make prediction result columns nullable
            // This allows saving data even when ML service is offline
            $table->integer('predicted_cluster')->nullable()->change();
            $table->decimal('confidence', 5, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ml_predictions', function (Blueprint $table) {
            // Revert to NOT NULL (but only if no null values exist)
            $table->integer('predicted_cluster')->nullable(false)->change();
            $table->decimal('confidence', 5, 4)->nullable(false)->change();
        });
    }
};
