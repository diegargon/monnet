<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

use App\Core\ConfigService;
use App\Services\CurlService;

/**
 *
 * @param ConfigService $ncfg
 * @param array<string,string> $lng
 * @return array<string,string|int>|null
 */
function weather_widget($ctx): ?array
{
    $lng = $ctx->get('lng');
    $ncfg = $ctx->get(ConfigService::class);

    $weather = [];
    $weather_data = request_weather($ncfg);
    if ($weather_data === null) {
        return null;
    }
    $weather['desc'] = ucwords($weather_data->weather[0]->description);
    $weather['city_name'] = $weather_data->name;
    $weather['weather_icon'] = 'https://openweathermap.org/img/wn/' . $weather_data->weather[0]->icon . '.png';
    $weather['weather_temp'] = round($weather_data->main->temp) . '°C';
    $weather['weather_l_humidity'] = $lng['L_HUMIDITY'];
    $weather['weather_humidity'] = $weather_data->main->humidity . '%';
    $weather['weather_l_wind'] = $lng['L_WINDSPEED'];
    $weather['weather_wind'] = $weather_data->wind->speed . 'km/h';

    return [
        'add_scriptlink' => ['./modules/weather_widget/weather_widget.js'],
        'add_load_tpl' => [
            [
                'file' => 'weather-widget',
                'place' => 'head-right',
            ]
        ],
        'weather_widget' => $weather
    ];
}

/**
 *
 * @param ConfigService $ncfg
 *
 * @return mixed
 */
function request_weather(ConfigService $ncfg): mixed
{
    $api = $ncfg->get('weather_api');
    $country = $ncfg->get('weather_country');

    if (empty($api) || empty($country)) {
        return null;
    }
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
