<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;


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

function request_weather(array $cfg): mixed
{

    $ApiUrl = 'http://api.openweathermap.org/data/2.5/weather?q=' .
            $cfg['weather_widget']['country']
            . '&appid=' . $cfg['weather_widget']['weather_api']
            . '&lang=es&units=metric';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $ApiUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    curl_close($ch);
    $data = json_decode($response);

    return $data;
}
