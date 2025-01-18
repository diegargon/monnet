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
 *
 * @param string $url
 * @return mixed
 */
function curl_get(string $url, int $timeout = 2): mixed
{
    if (empty($url)) :
        return false;
    endif;
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Monnet)';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout * 2);

    $response = curl_exec($ch);

    if ($response == false) {
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        $error = "Curl Error ($errno): $error";
        Log::warning($error);
    }

    curl_close($ch);

    return $response;
}

/**
 *
 * @param string $url
 * @param bool $https
 * @param bool $allowSelfSigned
 * @param float $timeout
 * @return array<string,string>
 */
function curl_check_webport(string $url, bool $https = true, bool $allowSelfSigned = false, float $timeout = 5): array
{
    $result = [];

    Log::debug('curl_check_webport'. $url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, ($timeout * 2));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);

    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$allowSelfSigned);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $allowSelfSigned ? 0 : 2);
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }
    $response = curl_exec($ch);
    if ($response === false) {
        $result['error'] = curl_error($ch);
        $result['errno'] = curl_errno($ch);
        $result['http_code'] = 0;
    } else {
        $result['msg'] = $response;
        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['error'] = null;
        $result['errno'] = 0;
    }
    curl_close($ch);

    return $result;
}
