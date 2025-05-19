<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class CurlService
{

    /**
     *
     * @param string $url
     * @return mixed
     * @throws \RuntimeException
     */
    static function curlGet(string $url, int $timeout = 2): mixed
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
            $errorMsg = "Curl Error ($errno): $error on $url";
            curl_close($ch);
            throw new \RuntimeException($errorMsg);
        }

        curl_close($ch);

        return $response;
    }
}
