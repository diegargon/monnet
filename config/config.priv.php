<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

$DEBUG = 1;

$cfg['monnet_version'] = 0.33;
$cfg['app_name'] = 'monnet';

/* Manufacture */
$cfg['manufacture'] = [
    0 => ['name' => 'Unknown', 'img' => 'unknown.png'],
    1 => ['name' => 'Microsoft', 'img' => 'microsoft.png'],
    2 => ['name' => 'Apple Inc.', 'img' => 'apple.png'],
    3 => ['name' => 'Canonical Ltd.', 'img' => 'canonical.png'],
    4 => ['name' => 'Google', 'img' => 'google.png'],
    5 => ['name' => 'The Linux Foundation', 'img' => 'linuxfoundation.png'],
    6 => ['name' => 'Red Hat', 'img' => 'redhat.png'],
    7 => ['name' => 'IBM', 'img' => 'ibm.png'],
    8 => ['name' => 'Hewlett Packard Enterprise', 'img' => 'hpe.png'],
    9 => ['name' => 'FreeBSD Foundation', 'img' => 'freebsd.png'],
    10 => ['name' => 'Oracle Corporation', 'img' => 'oracle.png'],
    11 => ['name' => 'OpenBSD Project', 'img' => 'openbsd.png'],
    12 => ['name' => 'Samsung Electronics', 'img' => 'samsung.png'],
    13 => ['name' => 'Sony Corporation', 'img' => 'sony.png'],
    14 => ['name' => 'FreeRTOS', 'img' => 'freertos.png'],
    15 => ['name' => 'Meta', 'img' => 'Meta.png'],
    16 => ['name' => 'Ubuntu', 'img' => 'ubuntu.png'],
    17 => ['name' => 'Debian', 'img' => 'debian.png'],
    18 => ['name' => 'CentOS', 'img' => 'centos.png'],
    19 => ['name' => 'FreeBSD', 'img' => 'freebsd.png'],
    20 => ['name' => 'OPNSense', 'img' => 'opnsense.png'],
    21 => ['name' => 'Dlink', 'img' => 'dlink.png'],
    22 => ['name' => 'Tplink', 'img' => 'tplink.png'],
    23 => ['name' => 'Mikrotik', 'img' => 'mikrotik.png'],
    24 => ['name' => 'Supermicro', 'img' => 'supermicro.png'],
    25 => ['name' => 'VMWare', 'img' => 'wmware.png'],
    25 => ['name' => 'Proxmox', 'img' => 'proxmox.png'],
];

/* OS */
$cfg['os'] = [
    0 => ['name' => 'Unknown', 'img' => 'unknown.png'],
    1 => ['name' => 'Linux', 'img' => 'linux.png'],
    2 => ['name' => 'Windows 10', 'img' => 'windows10.png'],
    3 => ['name' => 'FreeBSD', 'img' => 'freebsd.png'],
    4 => ['name' => 'Android', 'img' => '.png'],
    5 => ['name' => 'iOS', 'img' => 'ios.png'],
    6 => ['name' => 'macOS', 'img' => 'macos.png'],
    7 => ['name' => 'Windows 7', 'img' => 'windows7.png'],
    8 => ['name' => 'Windows 8/8.1', 'img' => 'windows8.png'],
    9 => ['name' => 'Unix', 'img' => 'unix.png'],
    10 => ['name' => 'OpenBSD', 'img' => 'openbsd.png'],
    11 => ['name' => 'Solaris', 'img' => 'solaris.png'],
    12 => ['name' => 'AIX', 'img' => 'aix.png'],
    13 => ['name' => 'HP-UX', 'img' => 'hpux.png'],
    14 => ['name' => 'Chrome OS', 'img' => 'chromeos.png'],
    15 => ['name' => 'IBM i', 'img' => 'ibmi.png'],
    16 => ['name' => 'z/OS', 'img' => 'zos.png'],
    17 => ['name' => 'DOS', 'img' => 'dos.png'],
    18 => ['name' => 'RTOS', 'img' => 'rtos.png'],
    19 => ['name' => 'Plan 9 from Bell Labs', 'img' => 'plan9.png'],
];

/* Systems */

$cfg['system_type'] = [
    0 => ['name' => 'Unknown', 'img' => 'unknown.png'],
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
    15 => ['name' => 'FreeBSD', 'img' => 'freebsd.png'],
    16 => ['name' => 'OPNSense', 'img' => 'opnsense.png'],
    17 => ['name' => 'Hypevisor', 'img' => 'hypervisor.png'],
];

$cfg['versions'] = [
    0 => ['name' => 'Unknown', 'img' => 'unknown.png'],
    1 => '20.04',
    2 => '21H1'
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
    ],
    3 => ['name' => 'Logrotate',
        'comment' => 'Rotate logs',
        'os_distribution' => 2,
        'file' => ''
    ]
];

$cfg['ssh_profile'] = [
    'os_profile_id' => 2,
    'motd_end_root' => '~#',
    'motd_end_user' => '~$',
];

$cfg['commands'] = [
    1 => 'sudo shutdown -r now;exit',
    2 => 'sudo shutdown -h now;exit',
];

$cfg['cat_types'] = [
    1 => 'L_HOSTS',
    2 => 'L_LINKS',
    3 => 'L_SEARCH_ENGINE',
];
