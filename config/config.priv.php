<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

$cfg['monnet_version'] = 0.46;
$cfg['monnet_revision'] = 44;
$cfg['monnet_homepage'] = "https://github.com/diegargon/monnet";
$cfg['agent_min_version'] = 0.105;
$cfg['agent_latest_version'] = 0.112;
$cfg['app_name'] = 'monnet';

$cfg['log_type_constants'] = [
    'LT_DEFAULT' => 0,
    'LT_EVENT' => 1,
    'LT_REMOTE_PORT_STATUS' => 2,
    'LT_ALERT' => 3,
    'LT_WARN' => 4,
    'LT_EVENT_ALERT' => 5,
    'LT_EVENT_WARN' => 6,
];

foreach ($cfg['log_type_constants'] as $key => $value) {
    define($key, $value);
}
/* Hardware Manufacture */
$cfg['manufacture'] = [
    0 => ['id' => 0, 'name' => 'Unknown', 'img' => 'unknown.png'],
    10 => ['id' => 10, 'name' => 'Microsoft', 'img' => 'microsoft.png'],
    11 => ['id' => 11, 'name' => 'OpenBSD Project', 'img' => 'openbsd.png'],
    12 => ['id' => 12, 'name' => 'Samsung Electronics', 'img' => 'samsung.png'],
    13 => ['id' => 13, 'name' => 'Sony Corporation', 'img' => 'sony.png'],
    15 => ['id' => 15, 'name' => 'Meta', 'img' => 'Meta.png'],
    20 => ['id' => 20, 'name' => 'OPNSense', 'img' => 'opnsense.png'],
    21 => ['id' => 21, 'name' => 'Dlink', 'img' => 'dlink.png'],
    22 => ['id' => 22, 'name' => 'Tplink', 'img' => 'tplink.png'],
    23 => ['id' => 23, 'name' => 'Mikrotik', 'img' => 'mikrotik.png'],
    24 => ['id' => 24, 'name' => 'Supermicro', 'img' => 'supermicro.png'],
    25 => ['id' => 25, 'name' => 'Apple Inc.', 'img' => 'apple.png'],
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
    52 => ['id' => 52, 'name' => 'China', 'img' => 'china.png'],
    53 => ['id' => 53, 'name' => 'OpenWrt', 'img' => 'openwrt.png'],
    54 => ['id' => 54, 'name' => 'Google', 'img' => 'google.png'],
    55 => ['id' => 55, 'name' => 'IBM', 'img' => 'ibm.png'],
    56 => ['id' => 56, 'name' => 'Hewlett Packard', 'img' => 'hp.png'],
    57 => ['id' => 57, 'name' => 'Red Hat', 'img' => 'redhat.png'],
    58 => ['id' => 58, 'name' => 'FreeBSD Foundation', 'img' => 'freebsd.png'],
    59 => ['id' => 59, 'name' => 'Oracle Corporation', 'img' => 'oracle.png'],
    60 => ['id' => 60, 'name' => 'Arista', 'img' => 'unknown.png'],
    61 => ['id' => 61, 'name' => 'F5 Networks', 'img' => 'unknown.png'],
    62 => ['id' => 62, 'name' => 'Vyos', 'img' => 'unknown.png'],
    63 => ['id' => 63, 'name' => 'Unraid', 'img' => 'unknown.png'],
    64 => ['id' => 64, 'name' => 'Debian', 'img' => 'debian.png'],
    65 => ['id' => 65, 'name' => 'Ubuntu', 'img' => 'ubuntu.png'],
    66 => ['id' => 66, 'name' => 'Fedora', 'img' => 'fedora.png'],
    67 => ['id' => 67, 'name' => 'openSuse', 'img' => 'suse.png'],
    68 => ['id' => 68, 'name' => 'Arch Linux', 'img' => 'arch.png'],
    69 => ['id' => 69, 'name' => 'Manjaro', 'img' => 'manjaro.png'],
    70 => ['id' => 70, 'name' => 'CentoOS', 'img' => 'manjaro.png'],
    71 => ['id' => 71, 'name' => 'RHEL', 'img' => 'rhel.png'],
   /* CLOUD  SYSTEMS */
   100 => ['id' => 100, 'name' => 'AWS', 'img' => 'unknown.png'],
   101 => ['id' => 101, 'name' => 'Azure', 'img' => 'unknown.png'],
   102 => ['id' => 102, 'name' => 'Google Cloud', 'img' => 'unknown.png'],
   /* Hypervisors */
   150 => ['id' => 150, 'name' => 'Proxmox VE', 'img' => 'proxmox.png'],
   151 => ['id' => 151, 'name' => 'VMWare ESXI', 'img' => 'vmware.png'],
   152 => ['id' => 152, 'name' => 'Hyper-V', 'img' => 'unknown.png'],
   153 => ['id' => 153, 'name' => 'Oracle Virtualbox', 'img' => 'unknown.png'],
   154 => ['id' => 154, 'name' => 'KVM', 'img' => 'unknown.png'],
   155 => ['id' => 155, 'name' => 'Citrix Hypervisor', 'img' => 'unknown.png'],
   156 => ['id' => 156, 'name' => 'Xen', 'img' => 'unknown.png'],
   157 => ['id' => 157, 'name' => 'Parallels', 'img' => 'unknown.png'],
   158 => ['id' => 158, 'name' => 'QEMU', 'img' => 'unknown.png'],
   159 => ['id' => 159, 'name' => 'OpenVZ', 'img' => 'unknown.png'],
   160 => ['id' => 160, 'name' => 'XCP-ng', 'img' => 'unknown.png'],
   /* Container Orchestration */
   200 => ['id' => 200, 'name' => 'Docker', 'img' => 'unknown.png'],
   201 => ['id' => 201, 'name' => 'Podman', 'img' => 'unknown.png'],
   202 => ['id' => 202, 'name' => 'Kubernetes', 'img' => 'unknown.png'],
   203 => ['id' => 203, 'name' => 'Docker Swarm', 'img' => 'unknown.png'],
   204 => ['id' => 204, 'name' => 'Apache Mesos', 'img' => 'unknown.png'],
   205 => ['id' => 205, 'name' => 'Nomad', 'img' => 'unknown.png'],
   206 => ['id' => 206, 'name' => 'Rancher', 'img' => 'unknown.png'],
   207 => ['id' => 207, 'name' => 'OpenShift', 'img' => 'unknown.png'],

];

/* Macbhine Type */
$cfg['machine_type'] = [
    0 => ['id' => 0, 'name' => 'Unknown', 'img' => 'unknown.png'],
    1 => ['id' => 1, 'name' => 'Physical/On-Premises', 'img' => 'unknown.png'],
    2 => ['id' => 2, 'name' => 'Virtual', 'img' => 'unknown.png'],
    4 => ['id' => 4, 'name' => 'Container', 'img' => 'unknown.png'],
    5 => ['id' => 5, 'name' => 'Cloud', 'img' => 'unknown.png'],
    6 => ['id' => 6, 'name' => 'Bare Metal Cloud', 'img' => 'unknown.png'],
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
    33 => ['id' => 33, 'name' => 'Other Proprietary', 'img' => 'proprietary.png'],
    34 => ['id' => 34, 'name' => 'Linux Embebbed', 'img' => 'embedded.png'],
    35 => ['id' => 35, 'name' => 'Tasmota', 'img' => 'embedded.png'],
    37 => ['id' => 37, 'name' => 'Cisco IOS', 'img' => 'unknown.png'],
    38 => ['id' => 38, 'name' => 'Cisco NXOS', 'img' => 'unknown.png'],
    39 => ['id' => 39, 'name' => 'JunOS', 'img' => 'unknown.png'],
    40 => ['id' => 40, 'name' => 'PanOS', 'img' => 'unknown.png'],
    41 => ['id' => 41, 'name' => 'VyOS', 'img' => 'unknown.png'],
    42 => ['id' => 42, 'name' => 'AMI', 'img' => 'unknown.png'],
    43 => ['id' => 43, 'name' => 'Opnsense', 'img' => 'opnsense.png'],
];

/* System ROL */

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
    22 => ['id' => 22, 'name' => 'IOT Gateway', 'img' => 'iot-router.png'],
    23 => ['id' => 23, 'name' => 'P2P', 'img' => 'p2p.png'],
    24 => ['id' => 24, 'name' => 'Game Console', 'img' => 'gameconsole.png'],
    25 => ['id' => 25, 'name' => 'Printer', 'img' => 'printer.png'],
    26 => ['id' => 26, 'name' => 'Printer Server', 'img' => 'printer-server.png'],
    27 => ['id' => 27, 'name' => 'Log Server', 'img' => 'nas.png'],
    28 => ['id' => 28, 'name' => 'Database Server', 'img' => 'database-server.png'],
    29 => ['id' => 29, 'name' => 'VPN Server', 'img' => 'vpn-server.png'],
    30 => ['id' => 30, 'name' => 'Load Balancer', 'img' => 'load-balancer.png'],
    31 => ['id' => 31, 'name' => 'Container Orchestation', 'img' => 'container.png'],
    32 => ['id' => 32, 'name' => 'Version Control System', 'img' => 'vcs.png'],
    33 => ['id' => 33, 'name' => 'Mail Server/Gateway', 'img' => 'mail-server.png'],
    34 => ['id' => 34, 'name' => 'Web Server', 'img' => 'www.png'],
    35 => ['id' => 35, 'name' => 'File Server', 'img' => 'file-server.png'],
    36 => ['id' => 36, 'name' => 'Proxy', 'img' => 'balancer-balancer.png'],
    37 => ['id' => 37, 'name' => 'Management', 'img' => 'management.png'],
    38 => ['id' => 38, 'name' => 'Connectivity', 'img' => 'connectivity.png'],
];

$cfg['sys_availability'] = [
    0 => ['id' => 0, 'name' => 'None'],
    1 => ['id' => 1, 'name' => 'HA Active-Passive'],
    2 => ['id' => 1, 'name' => 'HA Active-Active'],
    3 => ['id' => 3, 'name' => 'Fault Tolerant'],
    4 => ['id' => 4, 'name' => 'Disaster Recovery'],
    5 => ['id' => 5, 'name' => 'Load Balancer'],
    7 => ['id' => 7, 'name' => 'Cold Storage'],
    8 => ['id' => 8, 'name' => 'Scalable'],
    9 => ['id' => 9, 'name' => 'Geographical Redundancy'],
   10 => ['id' => 10, 'name' => 'Real-Time'],
   11 => ['id' => 11, 'name' => 'Maintainable'],
];

$cfg['check_method'] = [
    1 => 'ping',
    2 => 'port',
];

$cfg['cat_types'] = [
    1 => 'L_HOSTS',
    2 => 'L_LINKS',
    3 => 'L_SEARCH_ENGINE',
];

$cfg['access_link_types'] = [
    1 => 'www',
];

$cfg['agent_notifications'] = [
    1 => "high_iowait",
    2 => "high_memory_usage",
    3 => "high_disk_usage",
    4 => "high_cpu_usage",
    5 => "starting",
    6 => "shutdown",
    7 => "system_shutdown",
];

$cfg['tasks'] = [
    1 => 'Events',
    2 => 'Track',
];
/* TODO: Retrieve from monnet-ansible */

$cfg['playbooks'] = [
    [
        'name' => 'ansible-facts',
        'desc' => 'Display gathered facts',
        'cat' => ['posix', 'windows'],
    ],
    [
        'name' => 'install-monnet-agent-systemd',
        'desc' => 'Install Monnet Agent on systemd devices',
        'cat' => ['posix'],
    ],
    [
        'name' => 'buildin-cmd-df-linux',
        'desc' => 'Get disk usage',
        'cat' => ['posix'],
    ],
    [
        'name' => 'buildin-shell-free-linux',
        'desc' => 'Get memory usage',
        'cat' => ['posix'],
    ],
    [
        'name' => 'cmd-df-linux',
        'desc' => 'Obtener información de particiones reales',
        'cat' => ['posix'],
    ],
    [
        'name' => 'cmd-sstuln',
        'desc' => 'Get network socket information',
        'cat' => ['posix'],
    ],
    [
        'name' => 'cmd-topbn1',
        'desc' => 'Gather system load information',
        'cat' => ['posix'],
    ],
    [
        'name' => 'cmd-uptime',
        'desc' => 'Gather uptime information',
        'cat' => ['posix'],
    ],
    [
        'name' => 'gather-facts',
        'desc' => 'Minimal fact gathering',
        'cat' => ['posix', 'windows'],
    ],
    [
        'name' => 'ip-info',
        'desc' => 'Gather IP address and routes',
        'cat' => ['posix'],
    ],
    [
        'name' => 'iptables-facts',
        'desc' => 'Gather iptables facts',
        'cat' => ['posix'],
    ],
    [
        'name' => 'journald-linux',
        'desc' => 'Get the last lines from the system journal',
        'cat' => ['posix'],
    ],
    [
        'name' => 'cmd-df-linux',
        'desc' => 'Obtener información de particiones reales',
        'cat' => ['posix'],
    ],
    [
        'name' => 'load-linux',
        'desc' => 'Get load statistics',
        'cat' => ['posix'],
    ],
    [
        'name' => 'reboot-linux',
        'desc' => 'Reboot a Linux system',
        'cat' => ['posix'],
    ],
    [
        'name' => 'reboot-win',
        'desc' => 'Reboot a Windows system',
        'cat' => ['windows'],
    ],
    [
        'name' => 'service-facts',
        'desc' => 'Gather service facts',
        'cat' => ['posix'],
    ],
    [
        'name' => 'setup',
        'desc' => 'Gather system information',
        'cat' => ['posix', 'windows'],
    ],
    [
        'name' => 'shutdown-linux',
        'desc' => 'Shutdown a Linux system',
        'cat' => ['posix'],
    ],
    [
        'name' => 'shutdown-win',
        'desc' => 'Shutdown a Windows system',
    ],
    [
        'name' => 'syslog-linux',
        'desc' => 'Get the last lines of syslog',
        'cat' => ['posix'],
    ],
    [
        'name' => 'win-facts',
        'desc' => 'Gather facts from Windows hosts',
        'cat' => ['windows'],
    ],
    [
        'name' => 'mysql-keepalive',
        'desc' => 'Check and ensure MySQL service is running',
        'cat' => ['posix'],
    ],
    [
        'name' => 'mysql-dblocks',
        'desc' => 'Ensure MySQL is running and check database locks',
        'string_vars' => ['database_service', 'database_name', 'db_username'],
        'passwd_vars' => ['db_password'],
        'cat' => ['posix'],
    ],
    [
        'name' => 'mysql-performance',
        'desc' => 'Monitor MySQL performance and resource usage',
        'string_vars' => ['db_username'],
        'password_vars' => ['db_password'],
        'cat' => ['posix'],
    ],
    [
        'name' => 'ansible-ping',
        'desc' => 'Test Ansible connectivity',
        'cat' => ['posix', 'windows'],
    ],
    [
        'name' => 'test',
        'desc' => 'Playbook que no hace nada',
        'cat' => ['posix', 'windows'],
    ]
];
