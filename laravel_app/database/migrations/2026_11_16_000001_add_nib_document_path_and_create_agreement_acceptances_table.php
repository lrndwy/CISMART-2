<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Izin usaha (NIB) disimpan terpisah dari `business_documents` supaya admin
        // bisa membedakan syarat izin usaha dari lampiran tambahan (KTP/SIUP).
        // Nullable: permohonan lama tidak di-backfill dan baru wajib saat diajukan ulang.
        Schema::table('seller_applications', function (Blueprint $table) {
            $table->string('nib_document_path')->nullable()->after('business_documents');
        });

        // Bukti persetujuan kontrak. Satu baris per (user, jenis dokumen, versi):
        // pengajuan ulang tidak boleh menimpa riwayat, dan versi baru menghasilkan
        // baris baru. Tidak ada timestamps — baris tidak pernah diperbarui.
        Schema::create('agreement_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('version', 20);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accepted_at');

            $table->unique(['user_id', 'document_type', 'version']);
            $table->index(['document_type', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_acceptances');

        Schema::table('seller_applications', function (Blueprint $table) {
            $table->dropColumn('nib_document_path');
        });
    }
};