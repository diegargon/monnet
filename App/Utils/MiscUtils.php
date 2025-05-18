<?php
namespace App\Utils;

class MiscUtils
{
    public static function validArray($array): bool
    {
        return is_array($array) && !empty($array);
    }

    public static function microToMs(float $microseconds): float
    {
        return round($microseconds * 1000, 3);
    }

    public static function formatBytes(int $size, int $precision = 2): string
    {
        for ($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {;}
        return round($size, $precision) . ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'][$i];
    }

    public static function mbToGb(float $megabytes, int $precision = 2): float
    {
        return round($megabytes / 1024, $precision);
    }

    public static function roundLatency(float $latency, int $precision = 3): float
    {
        if ($latency > 0 && $latency <= 0.001) {
            $latency = 0.001;
        } elseif ($latency < 0) {
            $latency = $latency;
        } else {
            $latency = round($latency, $precision);
        }
        return $latency;
    }

    public static function createToken(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    public static function isEmpty(&$var): bool
    {
        if (!isset($var) || $var === null) {
            return true;
        }
        if ($var === '' || (is_array($var) && empty($var))) {
            return true;
        }
        return false;
    }

    public static function isJson(string $string): mixed
    {
        $decoded = json_decode($string, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    public static function floatToPercentage(float $value, float $min = 0.0, float $max = 100.0): float
    {
        if ($min >= $max) {
            throw new \InvalidArgumentException("Minimum value must be less than maximum value.");
        }
        $normalized = ($value - $min) / ($max - $min);
        return max(0, min(100, $normalized * 100));
    }

    public static function dumpInJson(mixed $var): void
    {
        echo json_encode([
            'dump' => str_replace(["\n", "  "], " ", print_r($var, true)),
        ]);
        exit();
    }
}