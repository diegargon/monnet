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

function utc_to_user_timezone($utc_date, $timezone, $time_format = 'Y-m-d H:i:s') {
    $date = new DateTime($utc_date, new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone($timezone));
    return $date->format($time_format);
}

function formatted_date_now($timezone = 'UTC', $time_format = 'Y-m-d H:i:s') {
    $date_timezone = new DatetimeZone($timezone);
    $date_now = new DateTime('now', $date_timezone);

    return $date_now->format($time_format);
}

function datetime_string_format(string $date, string $time_format = 'Y-m-d H:i:s') {

    $timestamp = strtotime($date);

    // Verificar si la conversión fue exitosa
    if ($timestamp !== false) {

        return date($time_format, $timestamp);
    } else {
        return 'Error';
    }

    return $dateTime;
}
