<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

$cfg['monnet_version'] = 0.36;
$cfg['monnet_revision'] = 19;
$cfg['monnet_homepage'] = "https://github.com/diegargon/monnet";
$cfg['app_name'] = 'monnet';

/* Manufacture */
$cfg['manufacture'] = [
    0 => ['id' => 0, 'name' => 'Unknown', 'img' => 'unknown.png'],
    1 => ['id' => 1, 'name' => 'Microsoft', 'img' => 'microsoft.png'],
    2 => ['id' => 2, 'name' => 'Apple Inc.', 'img' => 'apple.png'],
    3 => ['id' => 3, 'name' => 'Canonical Ltd.', 'img' => 'canonical.png'],
    4 => ['id' => 4, 'name' => 'Google', 'img' => 'google.png'],
    5 => ['id' => 5, 'name' => 'The Linux Foundation', 'img' => 'linuxfoundation.png'],
    6 => ['id' => 6, 'name' => 'Red Hat', 'img' => 'redhat.png'],
    7 => ['id' => 7, 'name' => 'IBM', 'img' => 'ibm.png'],
    8 => ['id' => 8, 'name' => 'Hewlett Packard', 'img' => 'hp.png'],
    9 => ['id' => 9, 'name' => 'FreeBSD Foundation', 'img' => 'freebsd.png'],
    10 => ['id' => 10, 'name' => 'Oracle Corporation', 'img' => 'oracle.png'],
    11 => ['id' => 11, 'name' => 'OpenBSD Project', 'img' => 'openbsd.png'],
    12 => ['id' => 12, 'name' => 'Samsung Electronics', 'img' => 'samsung.png'],
    13 => ['id' => 13, 'name' => 'Sony Corporation', 'img' => 'sony.png'],
    14 => ['id' => 14, 'name' => 'FreeRTOS', 'img' => 'freertos.png'],
    15 => ['id' => 15, 'name' => 'Meta', 'img' => 'Meta.png'],
    16 => ['id' => 16, 'name' => 'Ubuntu', 'img' => 'ubuntu.png'],
    17 => ['id' => 17, 'name' => 'Debian', 'img' => 'debian.png'],
    18 => ['id' => 18, 'name' => 'CentOS', 'img' => 'centos.png'],
    19 => ['id' => 19, 'name' => 'FreeBSD', 'img' => 'freebsd.png'],
    20 => ['id' => 20, 'name' => 'OPNSense', 'img' => 'opnsense.png'],
    21 => ['id' => 21, 'name' => 'Dlink', 'img' => 'dlink.png'],
    22 => ['id' => 22, 'name' => 'Tplink', 'img' => 'tplink.png'],
    23 => ['id' => 23, 'name' => 'Mikrotik', 'img' => 'mikrotik.png'],
    24 => ['id' => 24, 'name' => 'Supermicro', 'img' => 'supermicro.png'],
    25 => ['id' => 25, 'name' => 'free', 'img' => 'unknown.png'],
    26 => ['id' => 26, 'name' => 'Proxmox', 'img' => 'proxmox.png'],
    27 => ['id' => 27, 'name' => 'Tenda', 'img' => 'tenda.png'],
    28 => ['id' => 28, 'name' => 'Fortinet', 'img' => 'fortinet.png'],
    29 => ['id' => 29, 'name' => 'Cisco Systems', 'img' => 'cisco_systems.png'],
    30 => ['id' => 30, 'name' => 'Huawei', 'img' => 'huawei.png'],
    31 => ['id' => 31, 'name' => 'Juniper Networks', 'img' => 'juniper.png'],
    32 => ['id' => 32, 'name' => 'NetGear', 'img' => 'netgear.png'],
    33 => ['id' => 33, 'name' => 'Asus', 'img' => 'asus.png'],
    34 => ['id' => 34, 'name' => 'Linksys ', 'img' => 'linksys.png'],
    35 => ['id' => 35, 'name' => 'Zyxel', 'img' => 'zyxel.png'],
    36 => ['id' => 36, 'name' => 'Synology', 'img' => 'synology.png'],
    37 => ['id' => 37, 'name' => 'Ubiquiti Networks', 'img' => 'ubiquiti.png'],
    38 => ['id' => 38, 'name' => 'Palo Alto Networks', 'img' => 'paloalto.png'],
    39 => ['id' => 39, 'name' => 'Check Point Software Technologies', 'img' => 'checkpoint.png'],
    40 => ['id' => 40, 'name' => 'Aruba Networks', 'img' => 'aruba.png'],
    41 => ['id' => 41, 'name' => 'SonicWall', 'img' => 'sonicwall.png'],
    42 => ['id' => 42, 'name' => 'Truenas', 'img' => 'truenas.png'],
    43 => ['id' => 43, 'name' => 'Espressif Inc', 'img' => 'espressif.png'],
    44 => ['id' => 44, 'name' => 'Kyocera', 'img' => 'unknown.png'],
    45 => ['id' => 45, 'name' => 'Canon', 'img' => 'unknown.png'],
    46 => ['id' => 46, 'name' => 'Brother', 'img' => 'unknown.png'],
    47 => ['id' => 47, 'name' => 'Epson', 'img' => 'unknown.png'],
    48 => ['id' => 48, 'name' => 'Xerox', 'img' => 'unknown.png'],
    49 => ['id' => 49, 'name' => 'Lexmark', 'img' => 'unknown.png'],
    50 => ['id' => 50, 'name' => 'Ricoh', 'img' => 'unknown.png'],
    51 => ['id' => 51, 'name' => 'Konica Minolta', 'img' => 'unknown.png'],
    52 => ['id' => 52, 'name' => 'Chinese', 'img' => 'unknown.png'],
    53 => ['id' => 53, 'name' => 'OpenWrt', 'img' => 'openwrt.png'],
];

/* OS */
$cfg['os'] = [
    0 => ['id' => 0, 'name' => 'Unknown', 'img' => 'unknown.png'],
    1 => ['id' => 1, 'name' => 'Linux', 'img' => 'linux.png'],
    2 => ['id' => 2, 'name' => 'Windows 10', 'img' => 'windows10.png'],
    3 => ['id' => 3, 'name' => 'FreeBSD', 'img' => 'freebsd.png'],
    4 => ['id' => 4, 'name' => 'Android', 'img' => 'android.png'],
    5 => ['id' => 5, 'name' => 'iOS', 'img' => 'ios.png'],
    6 => ['id' => 6, 'name' => 'macOS', 'img' => 'macos.png'],
    7 => ['id' => 7, 'name' => 'Windows 7', 'img' => 'windows10.png'],
    8 => ['id' => 8, 'name' => 'Windows 8/8.1', 'img' => 'windows8.png'],
    9 => ['id' => 9, 'name' => 'Unix', 'img' => 'unix.png'],
    10 => ['id' => 10, 'name' => 'OpenBSD', 'img' => 'openbsd.png'],
    11 => ['id' => 11, 'name' => 'Solaris', 'img' => 'solaris.png'],
    12 => ['id' => 12, 'name' => 'AIX', 'img' => 'aix.png'],
    13 => ['id' => 13, 'name' => 'HP-UX', 'img' => 'hpux.png'],
    14 => ['id' => 14, 'name' => 'Chrome OS', 'img' => 'chromeos.png'],
    15 => ['id' => 15, 'name' => 'IBM i', 'img' => 'ibmi.png'],
    16 => ['id' => 16, 'name' => 'z/OS', 'img' => 'zos.png'],
    17 => ['id' => 17, 'name' => 'DOS', 'img' => 'dos.png'],
    18 => ['id' => 18, 'name' => 'RTOS', 'img' => 'rtos.png'],
    19 => ['id' => 19, 'name' => 'Plan 9 from Bell Labs', 'img' => 'plan9.png'],
    20 => ['id' => 20, 'name' => 'RouterOS', 'img' => 'mikrotik.png'],
    21 => ['id' => 21, 'name' => 'SwitchOS', 'img' => 'mikrotik.png'],
    22 => ['id' => 22, 'name' => 'FortiOS', 'img' => 'fortinet.png'],
    23 => ['id' => 23, 'name' => 'Truenas Core', 'img' => 'truenas.png'],
    24 => ['id' => 24, 'name' => 'Truenas Scale', 'img' => 'truenas.png'],
    25 => ['id' => 25, 'name' => 'Freenas', 'img' => 'freenas.png'],
    26 => ['id' => 26, 'name' => 'Windows 2019', 'img' => 'windows-server.png'],
    27 => ['id' => 27, 'name' => 'Windows 2016', 'img' => 'windows-server.png'],
    28 => ['id' => 28, 'name' => 'Windows 2022', 'img' => 'windows-server.png'],
    29 => ['id' => 29, 'name' => 'Windows 11', 'img' => 'windows-desktop.png'],
    30 => ['id' => 30, 'name' => 'DSM', 'img' => 'dsm.png'],
    31 => ['id' => 31, 'name' => 'ArubaOS', 'img' => 'unknown.png'],
    32 => ['id' => 32, 'name' => 'VMware', 'img' => 'vmware.png'],
    33 => ['id' => 33, 'name' => 'Propietary', 'img' => 'unknown.png'],
    34 => ['id' => 34, 'name' => 'Linux Embebbed', 'img' => 'embedded.png'],
    35 => ['id' => 35, 'name' => 'Tasmota', 'img' => 'embedded.png'],
];

/* Systems */

$cfg['system_type'] = [
    0 => ['id' => 0, 'name' => 'Unknown', 'img' => 'unknown.png'],
    1 => ['id' => 1, 'name' => 'Window Desktop', 'img' => 'windows-desktop.png'],
    2 => ['id' => 2, 'name' => 'Linux Desktop', 'img' => 'linux-desktop.png'],
    3 => ['id' => 3, 'name' => 'Iphone', 'img' => 'iphone.png'],
    4 => ['id' => 4, 'name' => 'Android Phone', 'img' => 'android-phone.png'],
    5 => ['id' => 5, 'name' => 'Apple Tablet', 'img' => 'ipad.png'],
    6 => ['id' => 6, 'name' => 'Android Tablet', 'img' => 'android-tablet.png'],
    7 => ['id' => 7, 'name' => 'TV', 'img' => 'tv.png'],
    8 => ['id' => 8, 'name' => 'Windows Server', 'img' => 'windows-server.png'],
    9 => ['id' => 9, 'name' => 'Linux Server', 'img' => 'linux-server.png'],
    10 => ['id' => 10, 'name' => 'Embedded', 'img' => 'embedded.png'],
    11 => ['id' => 11, 'name' => 'IOT', 'img' => 'iot.png'],
    12 => ['id' => 12, 'name' => 'Smart Home', 'img' => 'smart-home.png'],
    13 => ['id' => 13, 'name' => 'Wifi AP', 'img' => 'wifi-ap.png'],
    14 => ['id' => 14, 'name' => 'Security Camera', 'img' => 'cam2.png'],
    15 => ['id' => 15, 'name' => 'NVR', 'img' => 'nvr.png'],
    16 => ['id' => 16, 'name' => 'IPTV/TVBox', 'img' => 'tvbox.png'],
    17 => ['id' => 17, 'name' => 'Hypervisor', 'img' => 'hypervisor.png'],
    18 => ['id' => 18, 'name' => 'Router', 'img' => 'router.png'],
    19 => ['id' => 19, 'name' => 'Switch', 'img' => 'switch.png'],
    20 => ['id' => 20, 'name' => 'Mediacenter', 'img' => 'mediacenter.png'],
    21 => ['id' => 21, 'name' => 'NAS', 'img' => 'nas.png'],
    22 => ['id' => 22, 'name' => 'IOT Router', 'img' => 'iot-router.png'],
    23 => ['id' => 23, 'name' => 'P2P', 'img' => 'p2p.png'],
    24 => ['id' => 24, 'name' => 'Game Console', 'img' => 'gameconsole.png'],
    25 => ['id' => 25, 'name' => 'Printer', 'img' => 'printer.png'],
    26 => ['id' => 26, 'name' => 'Printer Server', 'img' => 'printer-server.png'],
    27 => ['id' => 27, 'name' => 'Log Server', 'img' => 'printer-server.png'],
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

$cfg['access_link_types'] = [
    1 => 'www',
];
