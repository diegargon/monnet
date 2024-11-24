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
 * @param array<string|array> $ary
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
 * @param array<string|array> $ary
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
 * @param array<string|array> $ary
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
 * @return string|bool
 */
function base_url(string $url): string|bool
{
    $parsed_url = parse_url($url);

    if ($parsed_url === false) {
        return false; // Si no se pudo parsear, retornamos false
    }

    if (isset($parsed_url['fragment'])) {
        unset($parsed_url['fragment']);
    }

    $base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];

    if (isset($parsed_url['port'])) {
        $base_url .= ':' . $parsed_url['port'];
    }

    return $base_url;
}

/**
 *
 * @param array<int, mixed>
 * @return string
 */
function array2string(array $array): string
{
    /**
     * @var array<string> $result
     */
    $result = [];
    foreach ($array as $subarray) {
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

            if ($img_item_check > utc_date_now()) {
                return $img_url;
            }
        }

        $ctx = stream_context_create(['http' => $http_options]);
        $img_file = @file_get_contents($img_url, false, $ctx);
        if ($img_file !== false) {
            if (file_put_contents($cache_img_path, $img_file) !== false) {
                return $cache_img_path;
            }
        } else {
            $user->setPref($img_url, utc_date_now());
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
