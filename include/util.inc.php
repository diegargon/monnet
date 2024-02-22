<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function valid_array($array) {
    if (!empty($array) && is_array($array) && count($array) > 0) {
        return true;
    }

    return false;
}

function micro_to_ms(float $microseconds) {

    return round($microseconds * 1000, 3);
}

function formatBytes(int $size, int $precision = 2) {
    for ($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {

    }

    return round($size, $precision) . ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'][$i];
}

function order(array &$ary, $sortKey = 'weight', $order = 'asc') {
    if (empty($sortKey)) {
        $sortKey = 'weight';
    }
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

function order_date(array &$ary) {
    usort($ary, function ($a, $b) {
        $itemA = strtotime($a['date']);
        $itemB = strtotime($b['date']);

        return ($itemA < $itemB) ? 1 : -1;
    });
}

function order_name(array &$ary) {
    usort($ary, function ($a, $b) {
        $itemA = $a['name'];
        $itemB = $b['name'];

        return ($itemA < $itemB) ? -1 : 1;
    });
}

function base_url(string $url) {
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

function array2string(array $array) {
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

function cached_img(Log $log, User $user, int $id, string $img_url, $renew = 0) {
    $http_options = [];

    $cache_path = 'cache';
    $http_options['timeout'] = 5; //seconds
    $http_options['max_redirects'] = 2;
    //$http_options['request_fulluri'] = true;
    $http_options['ssl']['verify_peer'] = false;
    $http_options['ssl']['verify_peer_name'] = false;
    $http_options['header'] = "User-agent: Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0";

    if (empty($img_url) || is_dir($img_url) || empty($id) || !is_numeric($id)) {
        return false;
    }

    if (!Filters::varImgUrl($img_url)) {
        $log->warning($img_url . ' invalid image url');
        return false;
    }

    if (!is_writeable($cache_path)) {
        $log->warning($cache_path . ' is not writable');
        return $img_url;
    }


    $file_name = basename($img_url);

    $cache_img_path = $cache_path . '/' . $id . '_' . $file_name;

    if (file_exists($cache_img_path) && $renew === 0) {
        return $cache_img_path;
    } else {
        $log->debug("image path NOT exists or renew getting content " . $img_url);
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
            $log->err('Error getting image error msg ' . $error['message']);
        }
    }

    return $img_url;
}

function round_latency(float $latency, int $precision = 3) {
    if ($latency < 0.001 && $latency > 0) {
        $latency = 0.001;
    } else {
        $latency = round($latency, $precision);
    }
    return $latency;
}
