<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2023 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

use phpseclib3\Net\SSH2;

function h_get_hostname(SSH2 $ssh, array &$result) {
    ssh_exec($ssh, $result, 'hostname');
    if (!empty($result['result'])) {
        //Remove ANSI Term codes. https://stackoverflow.com/questions/40731273/php-remove-terminal-codes-from-string
        $result['result'] = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $result['result']);
        $hostname = str_replace("\r\n", '', $result['result']);
        $result['hostname'] = trim($hostname);
    }
    unset($result['result']);
}

function h_get_ncpus(SSH2 $ssh, array &$result) {

    $ncpu = 0;

    ssh_exec($ssh, $result, 'grep --color=never processor /proc/cpuinfo|wc -l');
    if (!empty($result['result'])) {

        //Remove ANSI Term codes.
        $result['result'] = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $result['result']);

        $ncpu = str_replace("\r\n", '', $result['result']);
        $ncpu = trim($ncpu);
    }
    $result['ncpu'] = $ncpu;
    unset($result['result']);
}

function h_get_sys_mem(SSH2 $ssh, array &$result) {

    ssh_exec($ssh, $result, 'cat /proc/meminfo');
    if (!empty($result['result'])) {
        //Remove ANSI Term codes.
        $result['result'] = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $result['result']);
        $lines = explode("\n", $result['result']);
        foreach ($lines as $line) {
            $pieces = [];
            if (preg_match('/^MemTotal:\s+(\d+)\skB/', $line, $pieces)) {
                $mem_total = str_replace("\r\n", '', $pieces[1]);
                $result['mem']['mem_total'] = trim($mem_total);
            }
            if (preg_match('/^MemFree:\s+(\d+)\skB/', $line, $pieces)) {
                $mem_free = str_replace("\r\n", '', $pieces[1]);
                $result['mem']['mem_free'] = trim($mem_free);
            }
            if (preg_match('/^MemAvailable:\s+(\d+)\skB/', $line, $pieces)) {
                $mem_available = str_replace("\r\n", '', $pieces[1]);
                $result['mem']['mem_available'] = trim($mem_available);
            }
            if (!empty($mem_available) && !empty($mem_free)) {
                $result['mem']['mem_used'] = $mem_available - $mem_free;
            }
        }
    }

    unset($result['result']);
}

function h_get_sys_space(SSH2 $ssh, array &$result) {
    $mount_points = [];

    ssh_exec($ssh, $result, 'mount -t ext3,ext4,cifs,nfs,nfs4,zfs');

    if (!empty($result['result'])) {
        //Remove ANSI Term codes.
        $result['result'] = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $result['result']);
        $lines = explode("\n", $result['result']);
        foreach ($lines as $line) {
            $line = str_replace("\r\n", '', $line);
            $mount_points[] = trim((explode(" ", $line))[0]);
        }
    }
    $result['result'] = '';
    if (count($mount_points) > 0) {
        ssh_exec($ssh, $result, 'df');
        if (!emptY($result['result'])) {
            //Remove ANSI Term codes.
            $result['result'] = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $result['result']);
            $lines = explode("\n", $result['result']);
            foreach ($lines as $line) {

                $dev = [];
                //remove all extra spaces
                $line = preg_replace('/\s+/', ' ', $line);
                $line = str_replace("\r\n", '', $line);
                $split = explode(" ", $line);
                if (count($split) < 6) {
                    continue;
                }
                $dev['dev'] = trim($split[0]);
                $dev['total'] = $split[1];
                $dev['used'] = $split[2];
                $dev['available'] = $split[3];
                $dev['used_percent'] = str_replace('%', '', $split[4]);
                $dev['mounted'] = $split[5];
                foreach ($mount_points as $mount_point) {
                    if (!empty($mount_point) && $mount_point == $dev['dev']) {
                        $result['disks'][] = $dev;
                    }
                }
            }
        }
    }

    unset($result['result']);
}

function h_get_uptime(SSH2 $ssh, array &$result) {

    ssh_exec($ssh, $result, 'uptime -s');
    if (!empty($result['result'])) {
        //Remove ANSI Term codes
        $result['result'] = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $result['result']);
        $split = explode(' ', $result['result']);
        if (count($split) == 2) {
            $result['uptime']['datetime'] = trim($result['result']);
            $result['uptime']['date'] = trim($split[0]);
            $result['uptime']['hour'] = trim($split[1]);
        }
    }
    unset($result['result']);
}

function h_get_load_average(SSH2 $ssh, array &$result) {

    ssh_exec($ssh, $result, 'cat /proc/loadavg');

    if (!empty($result['result'])) {
        //Remove ANSI Term codes.
        $result['result'] = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $result['result']);
        $split = explode(' ', trim($result['result']));
        if (count($split) > 3) {
            $result['loadavg'][1] = trim($split[0]);
            $result['loadavg'][5] = trim($split[1]);
            $result['loadavg'][15] = trim($split[2]);
        }
    }
    unset($result['result']);
}

function h_get_tail_syslog(SSH2 $ssh, array &$result) {
    ssh_exec($ssh, $result, 'tail -50 /var/log/syslog');

    if (!empty($result['result'])) {
        //Remove ANSI Term codes.
        $result['result'] = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $result['result']);
        $logs = explode("\r\n", $result['result']);
        $logs = str_replace("\r", '', $logs);
        $logs = str_replace("'", '', $logs);
        //foreach ($logs as $k_log => $v_log) {
        //    $logs[$k_log] = $db->escape($v_log);
        //}

        $result['tail_syslog'] = $logs; // $db->escape($result['result']);
    }

    unset($result['result']);
}
