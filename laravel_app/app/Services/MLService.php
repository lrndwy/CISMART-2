<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MLService
{
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ml.url', 'http://ml_service:5000');
        $this->timeout = config('services.ml.timeout', 60);
    }

    /**
     * Check if ML service is healthy
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => 'unhealthy',
                'error' => 'Service returned error status'
            ];
        } catch (Exception $e) {
            Log::error('ML Service health check failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'unreachable',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get ML service information
     */
    public function getInfo(): ?array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/info");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to get ML service info', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Train ML models with uploaded data
     */
    public function trainModel(string $filePath): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->attach('file', file_get_contents($filePath), basename($filePath))
                ->post("{$this->baseUrl}/api/train");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Training failed'
            ];
        } catch (Exception $e) {
            Log::error('ML training failed', [
                'error' => $e->getMessage(),
                'file' => $filePath
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Predict cluster for single UMKM
     */
    public function predictSingle(array $data): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/predict", $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Prediction failed'
            ];
        } catch (Exception $e) {
            Log::error('ML prediction failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Predict clusters for batch of UMKMs
     */
    public function predictBatch(string $filePath): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->attach('file', file_get_contents($filePath), basename($filePath))
                ->post("{$this->baseUrl}/api/predict-batch");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Batch prediction failed'
            ];
        } catch (Exception $e) {
            Log::error('ML batch prediction failed', [
                'error' => $e->getMessage(),
                'file' => $filePath
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get trained model information
     */
    public function getModelInfo(): ?array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/model-info");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to get model info', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Download result file from ML service
     */
    public function downloadFile(string $filename): ?string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/download/{$filename}");

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to download file', [
                'error' => $e->getMessage(),
                'filename' => $filename
            ]);
            return null;
        }
    }
}
