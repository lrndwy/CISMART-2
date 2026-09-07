<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('qris_type')->nullable()->after('payment_status');
            $table->text('qris_payload')->nullable()->after('qris_type');
            $table->string('payment_proof_path')->nullable()->after('qris_payload');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_proof_path');
            $table->timestamp('payment_rejected_at')->nullable()->after('payment_verified_at');
            $table->text('payment_rejection_note')->nullable()->after('payment_rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'qris_type',
                'qris_payload',
                'payment_proof_path',
                'payment_verified_at',
                'payment_rejected_at',
                'payment_rejection_note',
            ]);
        });
    }
};
