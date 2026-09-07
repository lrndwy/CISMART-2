<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Zxing\QrReader;

class QrisImageDecoder
{
    /**
     * Resolve an absolute filesystem path from a Filament/Livewire upload state.
     */
    public static function absolutePathFromUpload(mixed $state, string $disk = 'public'): ?string
    {
        if (blank($state)) {
            return null;
        }

        if (is_array($state)) {
            $state = array_values($state)[0] ?? null;
        }

        if ($state instanceof TemporaryUploadedFile) {
            $path = $state->getRealPath();

            return ($path && is_file($path)) ? $path : null;
        }

        if (! is_string($state)) {
            return null;
        }

        if (is_file($state)) {
            return $state;
        }

        $stored = Storage::disk($disk)->path($state);

        return is_file($stored) ? $stored : null;
    }

    public static function decode(string $absolutePath): string
    {
        if (! is_file($absolutePath)) {
            throw new InvalidArgumentException('File QRIS tidak ditemukan.');
        }

        $reader = new QrReader($absolutePath);
        $text = $reader->text();

        if (! is_string($text) || trim($text) === '') {
            throw new InvalidArgumentException('QR tidak terbaca. Unggah ulang foto QRIS yang lebih jelas dan tidak buram.');
        }

        $payload = QrisPayload::normalize($text);

        if (! QrisPayload::isValid($payload)) {
            throw new InvalidArgumentException('Gambar terbacanya bukan QRIS yang valid. Pastikan yang diunggah adalah QRIS statis toko Anda.');
        }

        return $payload;
    }

    public static function decodeFromUpload(mixed $state, string $disk = 'public'): string
    {
        $path = self::absolutePathFromUpload($state, $disk);

        if (! $path) {
            throw new InvalidArgumentException('File QRIS tidak ditemukan.');
        }

        return self::decode($path);
    }
}
