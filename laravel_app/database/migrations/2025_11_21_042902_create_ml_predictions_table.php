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
        Schema::create('ml_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Input data (UMKM features)
            $table->decimal('omzet', 20, 2)->nullable();
            $table->decimal('aset', 20, 2)->nullable();
            $table->decimal('modal_kerja', 20, 2)->nullable();
            $table->decimal('jumlah_investasi', 20, 2)->nullable();
            $table->integer('jumlah_tenaga_kerja')->nullable();
            $table->decimal('bangunan_gedung', 20, 2)->nullable();
            $table->decimal('mesin_peralatan', 20, 2)->nullable();
            $table->decimal('mesin_peralatan_impor', 20, 2)->nullable();
            $table->decimal('pembelian_pematangan_tanah', 20, 2)->nullable();
            $table->decimal('lain_lain', 20, 2)->nullable();
            $table->integer('tki')->nullable();

            // Categorical features
            $table->string('jenis_perusahaan')->nullable();
            $table->string('risiko_proyek')->nullable();
            $table->string('skala_usaha')->nullable();
            $table->string('status_penanaman_modal')->nullable();
            $table->string('kecamatan_usaha')->nullable();
            $table->string('kelurahan_usaha')->nullable();
            $table->string('kl_sektor_pembina')->nullable();
            $table->string('judul_kbli')->nullable();

            // Prediction results
            $table->integer('predicted_cluster');
            $table->decimal('confidence', 5, 4);
            $table->json('probabilities')->nullable();
            $table->json('cluster_profile')->nullable();

            // Metadata
            $table->string('prediction_type')->default('single'); // single, batch
            $table->string('model_version')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('shop_id');
            $table->index('predicted_cluster');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_predictions');
    }
};
