<?php

namespace App\Services;

use App\Models\Shop;

class DeliveryCostCalculator
{
    private const EARTH_RADIUS_METERS = 6371000;

    public static function distanceMeters(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $fromLatRad = deg2rad($fromLat);
        $toLatRad = deg2rad($toLat);
        $deltaLat = deg2rad($toLat - $fromLat);
        $deltaLng = deg2rad($toLng - $fromLng);

        $haversine = sin($deltaLat / 2) ** 2
            + cos($fromLatRad) * cos($toLatRad) * sin($deltaLng / 2) ** 2;

        return 2 * self::EARTH_RADIUS_METERS * atan2(sqrt($haversine), sqrt(1 - $haversine));
    }

    public static function units(float $distanceMeters, int $unitMeters): int
    {
        if ($distanceMeters <= 0 || $unitMeters <= 0) {
            return 0;
        }

        return (int) ceil($distanceMeters / $unitMeters);
    }

    public static function cost(float $distanceMeters, int $unitMeters, int $rate): int
    {
        if ($rate <= 0) {
            return 0;
        }

        return self::units($distanceMeters, $unitMeters) * $rate;
    }

    /**
     * @return array{distance_meters: int, units: int, cost: int, unit_meters: int, rate: int}|null
     */
    public static function quote(Shop $shop, float $customerLat, float $customerLng): ?array
    {
        if (! $shop->hasDelivery()) {
            return null;
        }

        $distance = self::distanceMeters(
            (float) $shop->latitude,
            (float) $shop->longitude,
            $customerLat,
            $customerLng,
        );

        $unitMeters = max(1, (int) $shop->delivery_unit_meters);
        $rate = (int) $shop->delivery_rate;

        return [
            'distance_meters' => (int) round($distance),
            'units' => self::units($distance, $unitMeters),
            'cost' => self::cost($distance, $unitMeters, $rate),
            'unit_meters' => $unitMeters,
            'rate' => $rate,
        ];
    }

    public static function formatDistance(int $meters): string
    {
        if ($meters < 1000) {
            return number_format($meters, 0, ',', '.').' m';
        }

        return number_format($meters / 1000, 1, ',', '.').' km';
    }
}
