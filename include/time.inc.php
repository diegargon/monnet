<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
function valid_timezone(string $timezone): bool
{
    if (empty($timezone)) {
        return false;
    }
    $valid_time_zones = timezone_identifiers_list();

    return in_array($timezone, $valid_time_zones);
}

function date_now(string $timezone = 'UTC'): string|bool
{
    if (!valid_timezone($timezone)) {
        return false;
    }
    $date_timezone = new DatetimeZone($timezone);
    $date_now = new DateTime('now', $date_timezone);

    return $date_now->format('Y-m-d H:i:s');
}

function utc_date_now(): string|bool
{
    return date_now();
}

function utc_to_user_tz(string $utc_date, string $timezone, string $time_format = 'Y-m-d H:i:s'): string
{
    $date = new DateTime($utc_date, new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone($timezone));

    return $date->format($time_format);
}

function formatted_date_now(string $timezone = 'UTC', string $time_format = 'Y-m-d H:i:s'): string|bool
{
    if (!valid_timezone($timezone)) {
        return false;
    }
    $date_timezone = new DatetimeZone($timezone);
    $date_now = new DateTime('now', $date_timezone);

    return $date_now->format($time_format);
}

function datetime_string_format(string $date, string $time_format = 'Y-m-d H:i:s'): string
{
    $timestamp = strtotime($date);

    return ($timestamp) ? date($time_format, $timestamp) : false;
}

function datetime_machine(): string
{
    $now = new DateTime();
    return $now->format('Y-m-d H:i:s');
}
