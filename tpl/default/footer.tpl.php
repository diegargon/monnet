<?php
/**
*
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
 */
/**
 * In frontend->getTpl()
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
$memory_usage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
$start_time = $_SERVER["REQUEST_TIME_FLOAT"];
$execution_time = round(microtime(true) - $start_time, 2);
$load = sys_getloadavg();
$cpu_usage = round($load[0], 2);
?>
<div class="footer-bar">
    <div class="footer-left-element">
        <div>Memory: <span id="memory_usage"><?= $memory_usage?></span> MB&nbsp</div>
        <div>Execution Time: <span id="execution_time"><?= $execution_time ?></span>s&nbsp </div>
        <div>CPU Usage: <span id="cpu_usage"><?= $cpu_usage ?></span></div>
    </div>
    <div class="footer-right-element">
        <a href="<?= $ncfg->get('monnet_homepage') ?>" target="_blank">
            v<?= $ncfg->get('monnet_version') ?>.<?= $ncfg->get('monnet_revision') ?>
        </a>
    </div>
    <div class="clearfix"></div>
</div>
