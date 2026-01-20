<?php

namespace App\Helpers;

class LocationHelper
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     *
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in kilometers
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get SQL for distance calculation (for use in queries)
     *
     * @param float $lat User's latitude
     * @param float $lon User's longitude
     * @param string $latColumn Name of latitude column in table
     * @param string $lonColumn Name of longitude column in table
     * @return string SQL expression
     */
    public static function getDistanceSQL(float $lat, float $lon, string $latColumn = 'latitude', string $lonColumn = 'longitude'): string
    {
        return "(6371 * acos(
            cos(radians($lat))
            * cos(radians($latColumn))
            * cos(radians($lonColumn) - radians($lon))
            + sin(radians($lat))
            * sin(radians($latColumn))
        ))";
    }

    /**
     * Check if a coordinate is within radius of another coordinate
     *
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @param float $radius Maximum distance in kilometers (default 30)
     * @return bool
     */
    public static function isWithinRadius(float $lat1, float $lon1, float $lat2, float $lon2, float $radius = 30): bool
    {
        $distance = self::calculateDistance($lat1, $lon1, $lat2, $lon2);
        return $distance <= $radius;
    }
}
