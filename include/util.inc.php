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

function utc_date_now() {
    $date_timezone = new DatetimeZone('UTC');
    $date_now = new DateTime('now', $date_timezone);

    return $date_now->format('Y-m-d H:i:s');
}

function formatted_date_now($timezone = 'UTC', $time_format = 'Y-m-d H:i:s') {
    $date_timezone = new DatetimeZone($timezone);
    $date_now = new DateTime('now', $date_timezone);

    return $date_now->format($time_format);
}

function formatted_user_date($date, $timezone = 'UTC', $time_format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return false;
    }
    $utc_date = new DateTime($date, new DateTimeZone('UTC'));

    $utc_date->setTimezone(new DateTimeZone($timezone));

    return $utc_date->format($time_format);
}
