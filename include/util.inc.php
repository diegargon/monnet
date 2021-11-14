<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
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

function micro_to_ms(float $microseconds) {
    return round($microseconds * 1000, 3);
}
