<?php

namespace App\Services;

use InvalidArgumentException;

class QrisPayload
{
    public static function crc16(string $data): string
    {
        $crc = 0xFFFF;
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $crc ^= ord($data[$i]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }

                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    public static function isValid(string $payload): bool
    {
        $payload = self::normalize($payload);

        if (! str_starts_with($payload, '000201') || strlen($payload) < 20) {
            return false;
        }

        if (! preg_match('/6304[0-9A-Fa-f]{4}$/', $payload)) {
            return false;
        }

        $withoutCrc = substr($payload, 0, -4);

        return self::crc16($withoutCrc) === strtoupper(substr($payload, -4));
    }

    /**
     * @return array<string, string>
     */
    public static function parse(string $payload): array
    {
        return self::parseTlv(self::normalize($payload));
    }

    public static function merchantName(string $payload): ?string
    {
        return self::parse($payload)['59'] ?? null;
    }

    /**
     * @param  string|null  $billNumber  Diterima untuk kompatibilitas pemanggil lama.
     *                                   Tidak disematkan ke tag 62 — e-wallet sering menolak QR karena itu.
     */
    public static function toDynamic(string $staticPayload, float|int|string $amount, ?string $billNumber = null): string
    {
        $staticPayload = self::normalize($staticPayload);

        if (! self::isValid($staticPayload)) {
            throw new InvalidArgumentException('Payload QRIS statis tidak valid.');
        }

        $amountString = self::formatRupiahAmount($amount);
        $items = [];
        $hasAmount = false;

        foreach (self::rootTags($staticPayload) as [$tag, $value]) {
            if ($tag === '63') {
                continue;
            }

            if ($tag === '01') {
                $value = '12';
            }

            if ($tag === '62') {
                $value = self::stripGeneratedBillNumber($value);

                if ($value === '') {
                    continue;
                }
            }

            if ($tag === '54') {
                $items[] = ['54', $amountString];
                $hasAmount = true;

                continue;
            }

            if (! $hasAmount && (int) $tag > 54) {
                $items[] = ['54', $amountString];
                $hasAmount = true;
            }

            $items[] = [$tag, $value];
        }

        if (! $hasAmount) {
            $items[] = ['54', $amountString];
        }

        $body = self::joinRoot($items);

        return $body.'6304'.self::crc16($body.'6304');
    }

    public static function makeStaticDemo(string $merchantName, string $city = 'Cilacap'): string
    {
        $merchantName = substr($merchantName, 0, 25);
        $city = substr($city, 0, 15);

        $merchantAccount = self::buildTlv([
            '00' => 'ID.CO.QRIS.WWW',
            '01' => 'ID102000000000001',
            '02' => 'UMKM',
        ]);

        $body = self::buildTlv([
            '00' => '01',
            '01' => '11',
            '26' => $merchantAccount,
            '52' => '5411',
            '53' => '360',
            '58' => 'ID',
            '59' => $merchantName,
            '60' => $city,
            '61' => '53211',
        ]);

        return $body.'6304'.self::crc16($body.'6304');
    }

    /**
     * @return array<string, string>
     */
    public static function parseTlv(string $payload): array
    {
        $tags = [];
        $offset = 0;
        $length = strlen($payload);

        while ($offset + 4 <= $length) {
            $tag = substr($payload, $offset, 2);
            $valueLength = (int) substr($payload, $offset + 2, 2);
            $value = substr($payload, $offset + 4, $valueLength);
            $offset += 4 + $valueLength;
            $tags[$tag] = $value;

            if ($tag === '63') {
                break;
            }
        }

        return $tags;
    }

    /**
     * @param  array<string, string>  $tags
     */
    public static function buildTlv(array $tags): string
    {
        ksort($tags);

        $out = '';

        foreach ($tags as $tag => $value) {
            $out .= self::encodeTag((string) $tag, $value);
        }

        return $out;
    }

    public static function normalize(string $payload): string
    {
        return preg_replace('/[\r\n\t]+/', '', trim($payload)) ?? '';
    }

    /**
     * E-wallet Indonesia menolak tag 54 berdesimal (50000.00).
     * QRIS dinamis yang lolos scan memakai rupiah utuh.
     */
    public static function formatRupiahAmount(float|int|string $amount): string
    {
        if (is_string($amount)) {
            $amount = str_replace([',', ' '], '', trim($amount));
        }

        $value = (int) round((float) $amount);

        if ($value < 1) {
            throw new InvalidArgumentException('Nominal QRIS harus lebih dari 0.');
        }

        return (string) $value;
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    protected static function rootTags(string $payload): array
    {
        $items = [];
        $offset = 0;
        $length = strlen($payload);

        while ($offset + 4 <= $length) {
            $tag = substr($payload, $offset, 2);
            $valueLength = (int) substr($payload, $offset + 2, 2);
            $value = substr($payload, $offset + 4, $valueLength);
            $offset += 4 + $valueLength;
            $items[] = [$tag, $value];

            if ($tag === '63') {
                break;
            }
        }

        return $items;
    }

    /**
     * @param  list<array{0: string, 1: string}>  $items
     */
    protected static function joinRoot(array $items): string
    {
        $out = '';

        foreach ($items as [$tag, $value]) {
            $out .= self::encodeTag($tag, $value);
        }

        return $out;
    }

    protected static function encodeTag(string $tag, string $value): string
    {
        $tag = str_pad(substr($tag, -2), 2, '0', STR_PAD_LEFT);
        $byteLength = strlen($value);

        if ($byteLength > 99) {
            throw new InvalidArgumentException("Nilai tag QRIS {$tag} terlalu panjang.");
        }

        return $tag.str_pad((string) $byteLength, 2, '0', STR_PAD_LEFT).$value;
    }

    protected static function stripGeneratedBillNumber(string $additionalData): string
    {
        $additional = self::parseTlv($additionalData);
        $bill = $additional['01'] ?? $additional[1] ?? null;

        if (! is_string($bill) || ! str_starts_with($bill, 'ORD-')) {
            return $additionalData;
        }

        unset($additional['01'], $additional[1]);

        return $additional === [] ? '' : self::buildTlv($additional);
    }
}
