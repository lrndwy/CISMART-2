<?php

use App\Services\QrisPayload;

it('builds a valid static demo payload', function () {
    $payload = QrisPayload::makeStaticDemo('Sari Handmade');

    expect(QrisPayload::isValid($payload))->toBeTrue()
        ->and(QrisPayload::parse($payload)['01'])->toBe('11')
        ->and(QrisPayload::merchantName($payload))->toBe('Sari Handmade')
        ->and(QrisPayload::parse($payload))->not->toHaveKey('54');
});

it('converts static qris into dynamic qris with whole-rupiah amount', function () {
    $static = QrisPayload::makeStaticDemo('Budi Kuliner', 'CILACAP');

    $dynamic = QrisPayload::toDynamic($static, 125000, 'ORD-20260827-TEST');
    $tags = QrisPayload::parse($dynamic);

    expect(QrisPayload::isValid($dynamic))->toBeTrue()
        ->and($tags['01'])->toBe('12')
        ->and($tags['54'])->toBe('125000')
        ->and($dynamic)->not->toContain('125000.00')
        ->and($dynamic)->toStartWith('000201010212')
        ->and($dynamic)->not->toBe($static);
});

it('keeps merchant account bytes intact and does not invent additional data', function () {
    $static = QrisPayload::makeStaticDemo('WARUNG BU TINI', 'CILACAP');
    $staticTags = QrisPayload::parse($static);

    $dynamic = QrisPayload::toDynamic($static, '75000.00');
    $dynamicTags = QrisPayload::parse($dynamic);

    expect($dynamicTags['26'])->toBe($staticTags['26'])
        ->and($dynamicTags['59'])->toBe('WARUNG BU TINI')
        ->and($dynamicTags)->not->toHaveKey('62')
        ->and($dynamic)->toContain('540575000')
        ->and(QrisPayload::isValid($dynamic))->toBeTrue();
});

it('does not replace 010211 that appears inside merchant account data', function () {
    $merchantAccount = QrisPayload::buildTlv([
        '00' => 'ID.CO.QRIS.WWW',
        '01' => 'ID10200001021199',
        '02' => 'UMKM',
    ]);

    $body = QrisPayload::buildTlv([
        '00' => '01',
        '01' => '11',
        '26' => $merchantAccount,
        '52' => '5411',
        '53' => '360',
        '58' => 'ID',
        '59' => 'TOKO TES',
        '60' => 'CILACAP',
    ]);
    $static = $body.'6304'.QrisPayload::crc16($body.'6304');

    $dynamic = QrisPayload::toDynamic($static, 10000);
    $tags = QrisPayload::parse($dynamic);

    expect($tags['01'])->toBe('12')
        ->and($tags['26'])->toBe($merchantAccount)
        ->and($tags['26'])->toContain('010211')
        ->and(QrisPayload::isValid($dynamic))->toBeTrue();
});

it('strips previously embedded order bill numbers when refreshing a payload', function () {
    $static = QrisPayload::makeStaticDemo('TOKO LAMA', 'CILACAP');
    $withoutCrc = substr($static, 0, -4);
    $withoutCrc = preg_replace('/^000201010211/', '000201010212', $withoutCrc, 1);
    $parts = explode('5802ID', $withoutCrc, 2);
    $old = $parts[0].'540850000.00'.'5802ID'.$parts[1];
    $old = str_replace('6304', '62270123ORD-20260905123456-AB126304', $old);
    $old .= QrisPayload::crc16($old);

    expect(QrisPayload::parse($old))->toHaveKey('62');

    $fixed = QrisPayload::toDynamic($old, 50000);

    expect(QrisPayload::parse($fixed))->not->toHaveKey('62')
        ->and(QrisPayload::parse($fixed)['54'])->toBe('50000')
        ->and(QrisPayload::isValid($fixed))->toBeTrue();
});

it('can refresh an already-dynamic payload that used decimal amount', function () {
    $static = QrisPayload::makeStaticDemo('TOKO LAMA', 'CILACAP');
    $withoutCrc = substr($static, 0, -4);
    $withoutCrc = preg_replace('/^000201010211/', '000201010212', $withoutCrc, 1);
    $parts = explode('5802ID', $withoutCrc, 2);
    $decimalPayload = $parts[0].'540850000.00'.'5802ID'.$parts[1];
    $decimalPayload .= QrisPayload::crc16($decimalPayload);

    expect(QrisPayload::isValid($decimalPayload))->toBeTrue()
        ->and(QrisPayload::parse($decimalPayload)['54'])->toBe('50000.00');

    $fixed = QrisPayload::toDynamic($decimalPayload, 50000);

    expect(QrisPayload::parse($fixed)['54'])->toBe('50000')
        ->and($fixed)->not->toContain('50000.00')
        ->and(QrisPayload::isValid($fixed))->toBeTrue();
});

it('rejects an invalid qris payload', function () {
    expect(QrisPayload::isValid('bukan-qris'))->toBeFalse();

    expect(fn () => QrisPayload::toDynamic('bukan-qris', 1000))
        ->toThrow(InvalidArgumentException::class);
});
