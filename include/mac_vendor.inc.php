<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;
/**
 *
 * @param string $mac
 * @return array<string,mixed>|bool
 */
function get_mac_vendor(string $mac): array|bool
{
    $link = 'https://www.macvendorlookup.com/api/v2/';

    $link = $link . $mac;

    $json_response = curl_get($link);
    $response = json_decode($json_response, true);

    if (!valid_array($response)) {
        return false;
    }
    $response = $response[0];

    $response['company'] = $response['company'] . '(' . $response['country'] . ')';

    sleep(1);
    return $response;
}

/**
 *
 * @param string $mac
 * @return array<string,string>|bool
 */
function get_mac_vendor_local(string $mac): array|bool
{

    $formattedMAC = formatMAC($mac);

    if (!$formattedMAC) {
        Log::warning('Invalid mac format: ' . $formattedMAC);
        return false;
    }

    $file = './config/macvendors.txt';

    if (!file_exists($file)) {
        Log::error('File not found: ' . $file);
        return false;
    }

    $content = file_get_contents($file);
    if (empty($content)) {
        Log::warning('get_mac_vendor_local file_got_content empty or false');
        return false;
    }

    $pattern = "/\{MA-[LM]\}\{$formattedMAC\}([^\n]+)/i";

    $matches = [];
    if (preg_match($pattern, $content, $matches)) {
        $info = trim($matches[1]);
        //obtain {$1}{$2}
        $details = [];
        preg_match('/\{([^{}]+)\}\{([^{}]+)\}/', $info, $details);

        $company = !empty($details[1]) ? trim($details[1]) : '';

        if (empty($company)) :
            Log::debug('Mac Lookup fail: Empty mac vendor company');
            return false;
        endif;

        ///obtain country codes
        if (!empty($details[2])) :
            $country_pattern = '/\b([A-Z]{2})\b/';
            $country_matches = [];
            if (preg_match_all($country_pattern, $details[2], $country_matches)) {
                $country = implode('/', $country_matches[1]);
            } else {
                $country = '';
            }
        else :
            $country = '';
        endif;
        $vendor = trim($company) . " (" . trim($country) . ")";
        Log::debug('Mac vendor DB result is ' . $vendor);

        return ['company' => $vendor];
    } else {
        Log::debug('Mac Vendor Local: Failed preg_match file' . $pattern);
        return false;
    }
}

/**
 *
 * @param string $mac
 * @return string|bool
 */
function formatMAC(string $mac): string|bool
{
    // Remove any non-alphanumeric characters
    $mac = preg_replace('/[^a-fA-F0-9]/', '', $mac);

    // Ensure the MAC has at least 6 characters
    if (strlen($mac) < 6) {
        return false;
    }

    $formattedMAC = substr($mac, 0, 6);

    return $formattedMAC;
}
