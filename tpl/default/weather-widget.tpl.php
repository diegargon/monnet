<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="weather-container">
    <div id="clock"></div>

    <div class="time">
        <div><?= $tdata['weather_widget']['desc'] ?></div>
    </div>

    <div class="city-details">
        <div class="city-name"><?= $tdata['weather_widget']['city_name'] ?></div>
        <div class="weather-image">
            <img src="<?= $tdata['weather_widget']['weather_icon'] ?>" alt="" class="weather-icon" />
        </div>
    </div>

    <div><?= $tdata['weather_widget']['weather_l_humidity'] ?>:<?= $tdata['weather_widget']['weather_humidity'] ?></div>
    <div><?= $tdata['weather_widget']['weather_l_wind'] ?>:<?= $tdata['weather_widget']['weather_wind'] ?></div>

</div>