<?php

use App\Services\QrisImageDecoder;
use App\Services\QrisPayload;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

it('decodes a qris payload from an uploaded qr image', function () {
    $payload = QrisPayload::makeStaticDemo('Toko Upload');
    $path = sys_get_temp_dir().'/qris-decode-'.uniqid().'.png';

    $result = (new PngWriter)->write(new QrCode($payload));
    file_put_contents($path, $result->getString());

    $decoded = QrisImageDecoder::decode($path);

    expect($decoded)->toBe($payload)
        ->and(QrisPayload::merchantName($decoded))->toBe('Toko Upload');

    @unlink($path);
});

it('round-trips a dynamic qris image encoded as iso-8859-1', function () {
    $payload = QrisPayload::toDynamic(QrisPayload::makeStaticDemo('TOKO SCAN', 'CILACAP'), 88000);
    $path = sys_get_temp_dir().'/qris-pay-'.uniqid().'.png';

    $qrCode = new QrCode(
        data: $payload,
        encoding: new Encoding('ISO-8859-1'),
        errorCorrectionLevel: ErrorCorrectionLevel::Medium,
        size: 300,
        margin: 16,
    );
    file_put_contents($path, (new PngWriter)->write($qrCode)->getString());

    $decoded = QrisImageDecoder::decode($path);

    expect($decoded)->toBe($payload)
        ->and(QrisPayload::parse($decoded)['01'])->toBe('12')
        ->and(QrisPayload::parse($decoded)['54'])->toBe('88000');

    @unlink($path);
});

it('rejects a non-qris qr image', function () {
    $path = sys_get_temp_dir().'/qris-bad-'.uniqid().'.png';

    $result = (new PngWriter)->write(new QrCode('https://cismart.test/bukan-qris'));
    file_put_contents($path, $result->getString());

    expect(fn () => QrisImageDecoder::decode($path))
        ->toThrow(InvalidArgumentException::class, 'bukan QRIS');

    @unlink($path);
});
