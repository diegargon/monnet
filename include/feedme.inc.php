<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/**
 *
 * @param string $msg
 * @return void
 */
function trigger_feedme_error(string $msg): void
{
    Log::err($msg);
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $msg
    ]);
    exit;
}

function feed_update_listen_ports(Hosts $hosts, int $host_id, array $listen_ports): void
{
    $scan_type = 2; // Agent Based
    $online = 1;

    // Obtener los puertos actuales de la base de datos, organizados en un mapa para comparación
    $actual_host_ports = $hosts->getHostScanPorts($host_id, $scan_type);
    $actual_ports_map = [];
    foreach ($actual_host_ports as $port) {
        // Normalizar interface para IPv6
        $interface = $port['interface'];
        if ($port['ip_version'] === 'ipv6' && strpos($interface, ':') !== false && $interface[0] !== '[') {
            $interface = "[{$interface}]";
        }

        $key = "{$port['protocol']}:{$port['pnumber']}:{$interface}:{$port['ip_version']}";
        $actual_ports_map[$key] = $port;
    }

    // Procesar los puertos reportados en $listen_ports
    foreach ($listen_ports as $port) {
        // Validar y normalizar datos de entrada
        $protocol = ($port['protocol'] === 'tcp') ? 1 : 2;
        $pnumber = (int)$port['port'];
        $interface = $port['interface'] ?? '';
        if ($port['ip_version'] === 'ipv6' && strpos($interface, ':') !== false && $interface[0] !== '[') {
            $interface = "[{$interface}]"; // Normalizar IPv6
        }
        $ip_version = $port['ip_version'] ?? '';

        $key = "{$protocol}:{$pnumber}:{$interface}:{$ip_version}";

        if (isset($actual_ports_map[$key])) {
            // Port exists check changes and update
            $db_port = $actual_ports_map[$key];
            if ($db_port['online'] == 0 || $db_port['service'] !== $port['service']) {
                $set = [
                    "online" => $online,
                    "service" => $port['service'],
                    "last_change" => date_now()
                ];
                if ($db_port['online'] == 0) :
                    $alertmsg = "Port UP detected: {$port['pnumber']} ({$port['service']})";
                    $hosts->setAlertOn($host_id, $alertmsg);
                endif;
                if ($db_port['service'] !== $port['service']) :
                    $warnmsg = "Service name change detected: {$port['pnumber']} ({$port['service']})";
                    $hosts->setWarnOn($host_id, $warnmsg);
                endif;
                $hosts->updatePort($db_port['id'], $set);
            }
            unset($actual_ports_map[$key]); // Marcar como procesado
        } else {
            // Port not exist. Create.
            $insert_values = [
                'hid' => $host_id,
                'scan_type' => $scan_type,
                'protocol' => $protocol,
                'pnumber' => $pnumber,
                'online' => $online,
                'service' => $port['service'],
                'interface' => $interface,
                'ip_version' => $ip_version,
                'last_change' => date_now(),
            ];
            Log::logHost('LOG_NOTICE', $host_id, "New listing port detected: $pnumber ({$port['service']})");
            $hosts->addPort($insert_values);
        }
    }

    // Missing existing ports tag offline
    foreach ($actual_ports_map as $db_port) {
        if ($db_port['online'] == 1) {
            $set = [
                'online' => 0,
                'last_change' => date_now(),
            ];
            $alertmsg = "Port DOWN detected: {$db_port['pnumber']} ({$db_port['service']})";
            $hosts->setAlertOn($host_id, $alertmsg);
            $hosts->updatePort($db_port['id'], $set);
        }
    }
}

/**
 *
 * @param Hosts $hosts
 * @param array<string,mixed> $host
 * @param array<string,mixed> $rdata
 * @return array<string,mixed>
 */
function notification_process(Hosts $hosts, array $host, array $rdata): array
{
    $host_id = $host['id'];
    $host_update_values = [];

    $log_msg = "Receive notification with id: $host_id, {$rdata['name']}";
    isset($rdata['msg']) ? $log_msg .= ': ' . $rdata['msg'] : null;

    if ($rdata['name'] === 'starting') :
        Log::logHost('LOG_NOTICE', $host_id, $log_msg, LT_EVENT);
        if (!empty($rdata['ncpu'])) :
            if (!isset($host['ncpu']) || ($rdata['ncpu'] !== $host['ncpu'])) :
                $host_update_values['ncpu'] = $rdata['ncpu'];
            endif;
        endif;
        if (!empty($rdata['uptime'])) :
            if (!isset($host['uptime']) || ($rdata['uptime'] !== $host['uptime'])) :
                $host_update_values['uptime'] = $rdata['uptime'];
            endif;
        endif;
    else :
        if (!empty($rdata['event_value'])) :
            $log_msg .= ' Event value: '. $rdata['event_value'];
        else :
            $rdata['event_value'] = '';
        endif;
        if (empty($rdata['event_type'])) :
            Log::logHost('LOG_NOTICE', $host_id, $log_msg, LT_EVENT);
        else :
            if (in_array($rdata['event_value'], [3, 5])):
                $hosts->setAlertOn($host_id, $log_msg, $rdata['event_type']);
            else:
                $hosts->setWarnOn($host_id, $log_msg, $rdata['event_type']);
            endif;
        endif;
    endif;

    return $host_update_values;
}
