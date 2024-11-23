<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function curl_get(string $url): mixed
{
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Monnet)';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // Try connect (s)
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Return (s)

    return curl_exec($ch);
}
