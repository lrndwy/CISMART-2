<?php

use App\Services\DeliveryCostCalculator;

it('charges a full unit for any distance inside the first block', function () {
    expect(DeliveryCostCalculator::units(1, 100))->toBe(1)
        ->and(DeliveryCostCalculator::units(100, 100))->toBe(1)
        ->and(DeliveryCostCalculator::units(101, 100))->toBe(2)
        ->and(DeliveryCostCalculator::units(350, 100))->toBe(4);
});

it('multiplies units by the configured rupiah rate', function () {
    expect(DeliveryCostCalculator::cost(350, 100, 2000))->toBe(8000)
        ->and(DeliveryCostCalculator::cost(0, 100, 2000))->toBe(0)
        ->and(DeliveryCostCalculator::cost(50, 100, 0))->toBe(0);
});

it('measures roughly 111 meters for 0.001 degree of latitude', function () {
    $distance = DeliveryCostCalculator::distanceMeters(-7.7181, 109.0181, -7.7171, 109.0181);

    expect($distance)->toBeGreaterThan(110)
        ->and($distance)->toBeLessThan(113);
});

it('formats short and long distances for display', function () {
    expect(DeliveryCostCalculator::formatDistance(350))->toBe('350 m')
        ->and(DeliveryCostCalculator::formatDistance(1200))->toBe('1,2 km');
});
