<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/* Systems */

$cfg['os'] = [
    1 => ['name' => 'windows', 'img' => 'windows.png'],
    2 => ['name' => 'linux', 'img' => 'linux.png'],
];
$cfg['os_distributions'] = [
    1 => 'microsoft',
    2 => 'ubuntu',
    3 => 'debian',
];

$cfg['access_methods'] = [
    1 => 'ssh',
];
