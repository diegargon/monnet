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
 * @param array<int|string, mixed> $cfg
 * @param array<string,string> $lng
 * @return array<string,string|int>|null
 */
function weather_widget(array $cfg, array $lng): ?array
{

    $page_data = [];

    $weather_data = request_weather($cfg);
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
 * @param array<int|string, mixed> $cfg
 *
 * @return array<string, mixed>|null
 */
function request_weather(array $cfg): mixed
{

    $ApiUrl = 'http://api.openweathermap.org/data/2.5/weather?q=' .
            $cfg['weather_widget']['country']
            . '&appid=' . $cfg['weather_widget']['weather_api']
            . '&lang=es&units=metric';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $ApiUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $httpCode !== 200) {
        $error = curl_error($ch) ?: "HTTP code: $httpCode";
        curl_close($ch);
        Log::warning($error);
        return null;
    }

    curl_close($ch);

    $data = json_decode($response);

    if (json_last_error() !== JSON_ERROR_NONE) {
        Log::warning("Weather: Error al decodificar JSON: " . json_last_error_msg());
        return null;
    }

    return $data;
}
