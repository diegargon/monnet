<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function valid_array($array) {
    if (!empty($array) && is_array($array) && count($array) > 0) {
        return true;
    }

    return false;
}

function timestamp_to_date(int $timestamp) {
    //TODO custom user format

    if (!is_numeric($timestamp)) {
        return false;
    }
    $date = date("d/m/y H:i", $timestamp);

    return $date;
}

function formated_date($date) {
    $fdate = strtotime($date);

    return date("d/m/y H:i", $fdate);
}

function micro_to_ms(float $microseconds) {
    return round($microseconds * 1000, 3);
}

function formatBytes($size, $precision = 2) {
    for ($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {

    }
    return round($size, $precision) . ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'][$i];
}
