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
