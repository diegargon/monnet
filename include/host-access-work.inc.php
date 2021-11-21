<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function h_get_hostname($ssh, array &$result) {
    ssh_exec($ssh, $result, 'hostname');
    if (!empty($result['result'])) {
        $hostname = str_replace("\r\n", '', $result['result']);
        $result['hostname'] = trim($hostname);
    }
    $result['result'] = '';
}

function h_get_ncpus($ssh, array &$result) {

    $ncpu = 0;

    ssh_exec($ssh, $result, 'grep processor /proc/cpuinfo|wc -l');
    if (!empty($result['result'])) {
        $ncpu = str_replace("\r\n", '', $result['result']);
        $ncpu = trim($ncpu);
    }
    $result['ncpu'] = $ncpu;
    $result['result'] = '';
}

function h_get_sys_mem($ssh, array &$result) {

    ssh_exec($ssh, $result, 'cat /proc/meminfo');
    if (!empty($result['result'])) {
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
            if (!empty($result['mem_total']) && !empty($result['mem_avaible'])) {
                $result['mem']['mem_used'] = $result['mem_total'] - $result['mem_available'];
            }
        }
    }

    $result['result'] = '';
}

function h_get_sys_space($ssh, array &$result) {
    $mount_points = [];

    ssh_exec($ssh, $result, 'mount -t ext3,ext4,cifs,nfs,nfs4,zfs');

    if (!empty($result['result'])) {
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

    $result['result'] = '';
}

function h_get_uptime($ssh, array &$result) {

    ssh_exec($ssh, $result, 'uptime -s');
    if (!empty($result['result'])) {

        $split = explode(' ', $result['result']);
        if (count($split) == 2) {
            $result['uptime']['datetime'] = trim($result['result']);
            $result['uptime']['date'] = trim($split[0]);
            $result['uptime']['hour'] = trim($split[1]);
        }
    }
    $result['result'] = '';
}

function h_get_load_average($ssh, array &$result) {

    ssh_exec($ssh, $result, 'cat /proc/loadavg');

    if (!empty($result['result'])) {
        $split = explode(' ', trim($result['result']));
        if (count($split) > 3) {
            $result['loadavg'][1] = trim($split[0]);
            $result['loadavg'][5] = trim($split[1]);
            $result['loadavg'][15] = trim($split[2]);
        }
    }
    $result['result'] = '';
}
