<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

use App\Services\CurlService;

/**
 *
 * @param Config $ncfg
 * @param array<string,string> $lng
 * @return array<string,string|int>|null
 */
function weather_widget(\Config $ncfg, array $lng): ?array
{

    $page_data = [];

    $weather_data = request_weather($ncfg);
    if ($weather_data === null) {
        return null;
    }
    $page_data['desc'] = ucwords($weather_data->weather[0]->description);
    $page_data['city_name'] = $weather_data->name;
    $page_data['weather_icon'] = 'https://openweathermap.org/img/wn/' . $weather_data->weather[0]->icon . '.png';
    $page_data['weather_temp'] = round($weather_data->main->temp) . '°C';
    $page_data['weather_l_humidity'] = $lng['L_HUMIDITY'];
    $page_data['weather_humidity'] = $weather_data->main->humidity . '%';
    $page_data['weather_l_wind'] = $lng['L_WINDSPEED'];
    $page_data['weather_wind'] = $weather_data->wind->speed . 'km/h';

    return $page_data;
}

/**
 *
 * @param Config $ncfg
 *
 * @return mixed
 */
function request_weather(\Config $ncfg): mixed
{
    $weather_config = $ncfg->get('weather_widget');
    $api = $weather_config['weather_api'];
    $country = $weather_config['country'];

    $ApiUrl = 'http://api.openweathermap.org/data/2.5/weather?q=' .
            $country
            . '&appid=' . $api
            . '&lang=es&units=metric';

    $response = CurlService::curlGet($ApiUrl);

    if ($response) {
        $response = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Weather: Error al decodificar JSON: " . json_last_error_msg());
        }

    } else {
        $response = null;
    }

    return $response;
}
