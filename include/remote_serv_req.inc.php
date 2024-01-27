<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function get_mac_vendor(string $mac) {
    $link = "http://macvendors.co/api/";

    $link = $link . $mac . '/json';
    $response_data = curl_get($link);
    $json_response = json_decode($response_data, true);

    return empty($json_response['result']) ? false : $json_response['result'];
}
