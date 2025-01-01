<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/**
 * Check if is array and is not empty
 *
 * @param mixed $array
 * @return bool
 */
function valid_array(mixed $array): bool
{
    return is_array($array) && !empty($array);
}

/**
 * micro to ms
 * @param float $microseconds
 * @return float
 */
function micro_to_ms(float $microseconds): float
{

    return round($microseconds * 1000, 3);
}

/**
 * Format Bytes
 * @param int $size
 * @param int $precision
 * @return string
 */
function formatBytes(int $size, int $precision = 2): string
{
    for ($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {
        ;
    }
    return round($size, $precision) . ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'][$i];
}

/**
 *
 * @param float $megabytes
 * @param int $precision
 * @return float
 */
function mbToGb(float $megabytes, int $precision = 2): float
{
    return round($megabytes / 1024, $precision);
}

/**
 *
 * @param array<array<string, string>> $ary
 * @param string $sortKey
 * @param string $order
 * @return void
 */
function order(array &$ary, string $sortKey, string $order = 'asc'): void
{

    usort($ary, function ($a, $b) use ($sortKey, $order) {
        if (!isset($a[$sortKey]) || !isset($b[$sortKey])) {
            return false;
        }

        $itemA = $a[$sortKey];
        $itemB = $b[$sortKey];

        if ($order === 'desc') {
            return ($itemA < $itemB) ? 1 : -1;
        } else {
            return ($itemA < $itemB) ? -1 : 1;
        }
    });
}

/**
 *
 * @param array<array<string, string>> $ary
 * @return void
 */
function order_by_date(array &$ary): void
{
    usort($ary, function ($a, $b) {
        $itemA = strtotime($a['date']);
        $itemB = strtotime($b['date']);

        return ($itemA < $itemB) ? 1 : -1;
    });
}

/**
 *
 * @param array<array<string, string>> $ary
 * @return void
 */
function order_by_name(array &$ary): void
{
    $elementZero = array_shift($ary);

    usort($ary, function ($a, $b) {
        $itemA = $a['name'];
        $itemB = $b['name'];

        return ($itemA < $itemB) ? -1 : 1;
    });
    array_unshift($ary, $elementZero);
}

/**
 *
 * @param string $url
 * @return string|false
 */
function base_url(string $url): string|false
{
    $parsed_url = parse_url($url);

    if ($parsed_url === false) {
        Log::warning('Cant parse url: ' . $url);
        return false;
    }

    if (isset($parsed_url['fragment'])) {
        unset($parsed_url['fragment']);
    }

    if (empty($parsed_url['scheme']) || empty($parsed_url['host'])) :
        Log::warning('Cant parse url: ' . $url);
        return false;
    endif;

    $base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];

    if (isset($parsed_url['port'])) {
        $base_url .= ':' . $parsed_url['port'];
    }

    return $base_url;
}

/**
 * @param array<int|string, mixed> $ary
 *
 * @return string
 */
function array2string(array $ary): string
{
    /**
     * @var array<string> $result
     */
    $result = [];
    foreach ($ary as $subarray) {
        if (is_array($subarray)) {
            $result[] = array2string($subarray) . '::';
        } else {
            $result[] = $subarray;
        }
    }
    return implode(', ', $result);
}

/**
 *
 * @param User $user
 * @param int $id
 * @param string $img_url
 * @param int $renew
 * @return string|bool
 */
function cached_img(User $user, int $id, string $img_url, int $renew = 0): string|bool
{
    $http_options = [];

    $cache_path = 'cache';
    $http_options['timeout'] = 5; //seconds
    $http_options['max_redirects'] = 2;
    //$http_options['request_fulluri'] = true;
    $http_options['ssl']['verify_peer'] = false;
    $http_options['ssl']['verify_peer_name'] = false;
    $http_options['header'] = "User-agent: Mozilla/5.0 (X11; Fedora;" .
        "Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0";

    if (empty($img_url) || is_dir($img_url) || empty($id)) {
        return false;
    }

    if (!Filters::varImgUrl($img_url)) {
        Log::warning($img_url . ' invalid image url');
        return false;
    }

    if (!is_writeable($cache_path)) {
        Log::warning($cache_path . ' is not writable');
        return $img_url;
    }


    $file_name = basename($img_url);

    $cache_img_path = $cache_path . '/' . $id . '_' . $file_name;

    if (file_exists($cache_img_path) && $renew === 0) {
        return $cache_img_path;
    } else {
        Log::debug("image path NOT exists or renew getting content " . $img_url);
        $img_item_check = $user->getPref($img_url);
        if ($img_item_check) {
            $img_item_check = new DateTime($img_item_check);
            $img_item_check->modify('+48 hours');

            if ($img_item_check > date_now()) :
                return $img_url;
            endif;
        }

        $ctx = stream_context_create(['http' => $http_options]);
        $img_file = @file_get_contents($img_url, false, $ctx);
        if ($img_file !== false) {
            if (file_put_contents($cache_img_path, $img_file) !== false) :
                return $cache_img_path;
            endif;
        } else {
            $user->setPref($img_url, date_now());
            $error = error_get_last();
            Log::err('Error getting image error msg ' . $error['message']);
        }
    }

    return $img_url;
}

/**
 *
 * @param float $latency
 * @param int $precision
 * @return float
 */
function round_latency(float $latency, int $precision = 3): float
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

/**
 *
 * @return string
 */
function create_token(): string
{
    return bin2hex(openssl_random_pseudo_bytes(16));
}

/**
 * Used to dbg
 * @param mixed $var
 * @return void
 */
function dump_in_json(mixed $var): void
{
    echo json_encode([
        'dump' => str_replace(["\n", "  "], " ", print_r($var, true)),
    ]);
    exit();
}

/**
 * Returns true if empty
 * & to avoid !isset error
 * @param mixed $var
 * @return bool
 */
function isEmpty(&$var): bool
{
    if (!isset($var)) :
        return true;
    endif;

    if ($var === '' || (is_array($var) && $var === [])) :
        return true;
    endif;

    return false;
}

/**
 * Devuelve Json decodicado o null si no es valido
 *
 * @param string $string
 * @return mixed
 */
function isJson(string $string): mixed
{
    $decoded = json_decode($string, true);
    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
}

/**
 * Renders a nested array as an HTML unordered list with collapsible functionality.
 *
 * @param array<string,mixed> $array The input array (can be nested).
 * @param bool $omitEmpty Whether to omit keys with null/empty values (default: true).
 * @return string The generated HTML string with collapsible arrays.
 */
function array2Html(array $array, bool $omitEmpty = true): string
{
    static $idCounter = 0; // To ensure unique IDs for toggle buttons and sections
    $html = '<ul>';

    foreach ($array as $key => $value) {
        // Skip empty values if $omitEmpty is true
        if (
            $omitEmpty && (is_null($value) || $value === '' ||
            (is_array($value) && empty(array_filter($value, fn($v) => $v !== '' && $v !== null))))
        ) {
            continue;
        }

        $id = 'section_' . $idCounter++; // Unique ID for collapsible sections

        if (is_array($value)) {
            $html .= '<li>';
            $html .= "<button onclick=\"toggleSection('$id')\">[+] $key</button>";
            $html .= "<div id=\"$id\" class=\"hidden-section\">";
            $html .= array2Html($value, $omitEmpty); // Recursively render nested arrays
            $html .= '</div>';
            $html .= '</li>';
        } elseif (is_string($value) && strpos($value, "\n") !== false) {
            // Handle multiline strings (e.g., stdout content)
            $lines = explode("\n", $value);
            $html .= '<li>';
            $html .= "<button onclick=\"toggleSection('$id')\">[+] $key</button>";
            $html .= "<div id=\"$id\" class=\"hidden-section\"><ul>";
            foreach ($lines as $line) {
                $html .= "<li><pre>" . htmlspecialchars($line) . "</pre></li>";
            }
            $html .= '</ul></div></li>';
        } else {
            $html .= '<li><pre>';
            $html .= "<strong>$key:</strong> " . htmlspecialchars($value);
            $html .= '</pre></li>';
        }
    }

    $html .= '</ul>';
    return $html;
}

/**
 * Convert a float to a 0-100 representation.
 *
 * @param float $value The float value to convert.
 * @param float $min The minimum value of the range.
 * @param float $max The maximum value of the range.
 * @return float The value normalized to the 0-100 range.
 */

function floatToPercentage(float $value, float $min = 0.0, float $max = 100.0): float
{
    if ($min >= $max) :
        throw new InvalidArgumentException("Minimum value must be less than maximum value.");
    endif;

    // Normalize the value
    $normalized = ($value - $min) / ($max - $min);

    // Scale to 0-100
    return max(0, min(100, $normalized * 100));
}

/**
 * Seconds to day/hours/minutes/seconds array
 *
 * @param int $seconds
 * @return array
 */
function secondsToDHMS(int $seconds): array
{
    $days = intdiv($seconds, 86400);
    $seconds %= 86400;

    $hours = intdiv($seconds, 3600);
    $seconds %= 3600;

    $minutes = intdiv($seconds, 60);
    $seconds %= 60;

    return [
        'days' => $days,
        'hours' => $hours,
        'minutes' => $minutes,
        'seconds' => $seconds,
    ];
}
