<?php

namespace App\Filament\Resources\MlPredictionResource\Pages;

use App\Filament\Resources\MlPredictionResource;
use App\Services\MLService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateMlPrediction extends CreateRecord
{
    protected static string $resource = MlPredictionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set user_id to current user
        $data['user_id'] = Auth::id();

        // Try to get prediction from ML service
        try {
            $mlService = app(MLService::class);

            // Check if ML service is available first
            $health = $mlService->healthCheck();

            if ($health['status'] !== 'healthy') {
                // ML Service is not available
                Log::info('ML Service unavailable during prediction', ['health' => $health]);

                $data['predicted_cluster'] = null;
                $data['confidence'] = null;
                $data['notes'] = ($data['notes'] ?? '') . "\n[System] ML Service offline. Data saved without prediction. Run prediction manually when service is available.";

                // Show notification to user
                \Filament\Notifications\Notification::make()
                    ->warning()
                    ->title('ML Service Unavailable')
                    ->body('Data saved successfully, but prediction could not be generated. Please run prediction manually later.')
                    ->persistent()
                    ->send();

                return $data;
            }

            // Prepare data for ML prediction
            $predictionData = [
                'omzet' => $data['omzet'] ?? 0,
                'aset' => $data['aset'] ?? 0,
                'modal_kerja' => $data['modal_kerja'] ?? 0,
                'jumlah_investasi' => $data['jumlah_investasi'] ?? 0,
                'jumlah_tenaga_kerja' => $data['jumlah_tenaga_kerja'] ?? 1,
                'bangunan_gedung' => $data['bangunan_gedung'] ?? 0,
                'mesin_peralatan' => $data['mesin_peralatan'] ?? 0,
                'mesin_peralatan_impor' => $data['mesin_peralatan_impor'] ?? 0,
                'pembelian_pematangan_tanah' => $data['pembelian_pematangan_tanah'] ?? 0,
                'lain_lain' => $data['lain_lain'] ?? 0,
                'tki' => $data['tki'] ?? 0,
                'jenis_perusahaan' => $data['jenis_perusahaan'] ?? '',
                'risiko_proyek' => $data['risiko_proyek'] ?? '',
                'skala_usaha' => $data['skala_usaha'] ?? '',
                'status_penanaman_modal' => $data['status_penanaman_modal'] ?? '',
                'kecamatan_usaha' => $data['kecamatan_usaha'] ?? '',
                'kelurahan_usaha' => $data['kelurahan_usaha'] ?? '',
                'kl_sektor_pembina' => $data['kl_sektor_pembina'] ?? '',
                'judul_kbli' => $data['judul_kbli'] ?? '',
            ];

            $result = $mlService->predictSingle($predictionData);

            if ($result['success'] && isset($result['data']['prediction'])) {
                $prediction = $result['data']['prediction'];

                $data['predicted_cluster'] = $prediction['predicted_cluster'];
                $data['confidence'] = $prediction['confidence'];
                $data['probabilities'] = $prediction['probabilities'];
                $data['cluster_profile'] = $prediction['cluster_profile'] ?? null;
                $data['model_version'] = $prediction['model_version'] ?? null;

                // Show success notification
                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('Prediction Generated Successfully')
                    ->body("Cluster: " . $this->getClusterLabel($prediction['predicted_cluster']) . " | Confidence: " . number_format($prediction['confidence'] * 100, 2) . "%")
                    ->send();
            } else {
                // ML service responded but prediction failed (model not trained, etc)
                Log::warning('ML prediction failed', ['result' => $result]);

                $data['predicted_cluster'] = null;
                $data['confidence'] = null;
                $data['notes'] = ($data['notes'] ?? '') . "\n[System] Model not trained yet. Please train the model first, then run prediction manually.";

                \Filament\Notifications\Notification::make()
                    ->warning()
                    ->title('Model Not Trained')
                    ->body('Data saved, but ML model needs to be trained first. Upload training data to the ML service.')
                    ->persistent()
                    ->send();
            }
        } catch (\Exception $e) {
            // ML service error - log and continue with manual data
            Log::error('ML prediction exception during create', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $data['predicted_cluster'] = null;
            $data['confidence'] = null;
            $data['notes'] = ($data['notes'] ?? '') . "\n[Error] ML service error: " . $e->getMessage();

            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Prediction Error')
                ->body('Data saved, but prediction failed: ' . $e->getMessage())
                ->persistent()
                ->send();
        }

        return $data;
    }

    /**
     * Get human-readable cluster label
     */
    private function getClusterLabel(int $cluster): string
    {
        return match ($cluster) {
            0 => 'Cluster A - UMKM Skala Kecil',
            1 => 'Cluster B - UMKM Skala Menengah',
            2 => 'Cluster C - UMKM Skala Besar',
            default => 'Unknown Cluster'
        };
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
