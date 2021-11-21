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

$cfg['system'] = [
    1 => ['name' => 'Window Desktop', 'img' => 'windows-desktop.png'],
    2 => ['name' => 'Linux Desktop', 'img' => 'linux-desktop.png'],
    3 => ['name' => 'Iphone', 'img' => 'iphone.png'],
    4 => ['name' => 'Android Phone', 'img' => 'android-phone.png'],
    5 => ['name' => 'Ipad', 'img' => 'ipad.png'],
    6 => ['name' => 'Android Tablet', 'img' => 'android-tablet.png'],
    7 => ['name' => 'TV', 'img' => 'tv.png'],
    8 => ['name' => 'Windows Server', 'img' => 'windows-server.png'],
    9 => ['name' => 'Linux Server', 'img' => 'linux-server.png'],
    10 => ['name' => 'Embedded', 'img' => 'embedded.png'],
    11 => ['name' => 'IOT', 'img' => 'iot.png'],
    12 => ['name' => 'Smart Home', 'img' => 'smart-home.png'],
    13 => ['name' => 'Wifi AP', 'img' => 'wifi-ap.png'],
    14 => ['name' => 'Camera', 'img' => 'cam2.png'],
];
$cfg['os_distributions'] = [
    1 => 'microsoft',
    2 => 'ubuntu',
    3 => 'debian',
    4 => 'centos'
];
$cfg['versions'] = [
    1 => '20.04',
    1 => '21H1'
];

$cfg['access_methods'] = [
    1 => 'ssh',
];

$cfg['check_method'] = [
    1 => 'ping',
    2 => 'port',
];

$cfg['deploys'] = [
    1 => ['name' => 'Apache',
        'comment' => 'Installation apache on Ubuntu',
        'os_distribution' => 2,
        'file' => 'scripts/ubuntu-apache.sh'
    ],
    2 => ['name' => 'LAMP',
        'comment' => 'Installation apache, mysql php on Ubuntu',
        'os_distribution' => 2,
        'file' => 'scripts/ubuntu-lamp.sh'
    ]
];

$cfg['ssh_profile'] = [
    'os_profile_id' => 2,
    'motd_end_root' => '~#',
    'motd_end_user' => '~$',
];
