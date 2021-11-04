<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/* TODO: Only TCP port check (check_type=1), add ping check at least (check_type=2) */
/* port_type = 2 (udp) only work for non DGRAM sockets, dgram need wait for response/ ping */

function ping_ports(&$hosts) {

    foreach ($hosts as $khost => $host) {
        $err_code = $err_msg = '';
        $timeout = 1;
        $hosts[$khost]['online'] = 0;

        if (!empty($host['timeout'])) {
            $timeout = $host['timeout'];
        }

        if (!empty($host['ports']) && count($host['ports']) > 0) {
            foreach ($host['ports'] as $kport => $value_port) {
                $tim_start = microtime(true);
                $hostname = $host['host'];
                $value_port['port_type'] == 2 ? $hostname = 'udp://' . $hostname : null;

                $conn = @fsockopen($hostname, $value_port['port'], $err_code, $err_msg, $timeout);
                //echo "Conn " . $hostname . ' port ' . $value_port['port'] . "";
                if (is_resource($conn)) {
                    //echo " ok \n";
                    $hosts[$khost]['online'] = 1;
                    $hosts[$khost]['ports'][$kport]['online'] = 1;
                    fclose($conn);
                } else {
                    $hosts[$khost]['ports'][$kport]['online'] = 0;
                    $hosts[$khost]['ports'][$kport]['err_code'] = $err_code;
                    $hosts[$khost]['ports'][$kport]['err_msg'] = $err_msg;
                    //echo " fail \n";
                }
                $hosts[$khost]['ports'][$kport]['latency'] = round(microtime(true) - $tim_start, 2);
            }
        }
    }
}
