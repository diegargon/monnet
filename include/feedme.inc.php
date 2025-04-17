<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/**
 *
 * @param string $msg
 * @return never
 */
function trigger_feedme_error(string $msg): void
{
    Log::error($msg);
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $msg
    ]);
    exit;
}

/**
 * Notify notifiations
 *
 * @param AppContext $ctx
 * @param int $host_id
 * @param array<string,mixed> $rdata
 * @return array<string,mixed>
 */
function notification_process(AppContext $ctx, int $host_id, array $rdata): array
{
    $hosts = $ctx->get('Hosts');
    $host = $hosts->getHostById($host_id);

    $host_id = $host['id'];
    $host_update_values = [];

    if ($rdata['name'] === 'starting') :
        if (!empty($rdata['ncpu'])) {
            if (!isset($host['ncpu']) || ($rdata['ncpu'] !== $host['ncpu'])) :
                $host_update_values['ncpu'] = $rdata['ncpu'];
            endif;
        }
        if (!empty($rdata['uptime'])) {
            if (!isset($host['uptime']) || ($rdata['uptime'] !== $host['uptime'])) {
                $host_update_values['uptime'] = $rdata['uptime'];
            }
        }
    endif;

    $event_type = 0;
    $log_type = 0;
    $log_level = 7;

    if (isset($rdata['log_type'])) :
        $log_type = $rdata['log_type'];
    else :
        if (!empty($rdata['event_type'])) {
            $log_type = LogType::EVENT;
        }
    endif;

    if (!empty($rdata['event_type'])) {
        $event_type = $rdata['event_type'];
    }

    if (isset($rdata['log_level'])) {
        $log_level = $rdata['log_level'];
    }

    $log_msg = "Notification: {$rdata['name']}";
    isset($rdata['msg']) ? $log_msg .= ': ' . $rdata['msg'] : null;
    if (!empty($rdata['event_value'])) {
        $log_msg .= ' Event value: ' . $rdata['event_value'];
    }

    if ($log_level <= LogLevel::CRITICAL) :
        $hosts->setAlertOn($host_id, $log_msg, LogType::EVENT_ALERT, $event_type);
    elseif ($log_level == LogLevel::ERROR || $log_level == LogLevel::WARNING) :
        $hosts->setWarnOn($host_id, $log_msg, LogType::EVENT_WARN, $event_type);
    else :
        Log::logHost($log_level, $host_id, $log_msg, $log_type, $event_type);
    endif;

    return $host_update_values;
}

/**
 * Deal with notifications data
 *
 * @param AppContext $ctx
 * @param int $host_id
 * @param array<string,mixed> $rdata
*/
function notification_data_process(AppContext $ctx, int $host_id, array $rdata): void
{
    $db = $ctx->get('Mysql');

    if (!isEmpty($rdata['load_avg_stats'])) :
        $set_stats = [
            'date' => date_now(),
            'type' => 2,   //loadavg
            'host_id' => $host_id,
            'value' => $rdata['load_avg_stats']['5min']
        ];
        $db->insert('stats', $set_stats);
    endif;
    if (!isEmpty($rdata['iowait_stats'])) :
        $set_stats = [
            'date' => date_now(),
            'type' => 3,   //iowait
            'host_id' => $host_id,
            'value' => $rdata['iowait_stats']
        ];
        $db->insert('stats', $set_stats);
    endif;
    if (!isEmpty($rdata['memory_stats'])) :
        $set_stats = [
            'date' => date_now(),
            'type' => 4,   // Memory
            'host_id' => $host_id,
            'value' => $rdata['memory_stats']
        ];
        $db->insert('stats', $set_stats);
    endif;
    if (!isEmpty($rdata['listen_ports_info'])) :
        feed_update_listen_ports($ctx, $host_id, $rdata['listen_ports_info']);
    endif;
}

/**
 * Deal with the listen ports report
 *
 * @param AppContext $ctx
 * @param int $host_id
 * @param array<array<string, int|string>> $listen_ports
 * @return void
 */
function feed_update_listen_ports(AppContext $ctx, int $host_id, array $listen_ports): void
{
    $hosts = $ctx->get('Hosts');
    $scan_type = 2; // Agent Based

    // Obtener los puertos actuales de la base de datos, organizados en un mapa para comparación
    $db_host_ports = $hosts->getHostScanPorts($host_id, $scan_type);
    $db_ports_map = [];
    foreach ($db_host_ports as $db_port) :
        // Normalizar interface para IPv6
        $interface = $db_port['interface'] ?? '';
        $pnumber = (int) $db_port['pnumber'];
        $protocol = (int) $db_port['protocol'];
        if ($db_port['ip_version'] === 'ipv6' && strpos($interface, ':') !== false && $interface[0] !== '[') {
            $interface = "[{$interface}]";
        }

        $key = "{$protocol}:$pnumber:{$interface}:{$db_port['ip_version']}";
        $db_ports_map[$key] = $db_port;
    endforeach;
    // Procesar los puertos reportados en $listen_ports
    foreach ($listen_ports as $port) :
        // Validar y normalizar datos de entrada
        $interface = $port['interface'] ?? '';
        $pnumber = (int)$port['port'];
        $protocol = ($port['protocol'] === 'tcp') ? 1 : 2;

        if ($port['ip_version'] === 'ipv6' && strpos($interface, ':') !== false && $interface[0] !== '[') {
            $interface = "[{$interface}]"; // Normalizar IPv6
        }
        $ip_version = $port['ip_version'] ?? '';

        $key = "{$protocol}:{$pnumber}:{$interface}:{$port['ip_version']}";

        if (isset($db_ports_map[$key])) {
            $db_port = $db_ports_map[$key];
            // Si el servicio cambia, actualiza en lugar de insertar un nuevo registro
            if ($db_port['service'] !== $port['service']) {
                $warnmsg = 'Service name change detected: '
                    . "({$db_port['service']}->{$port['service']}) ({$pnumber})";
                $hosts->setWarnOn($host_id, $warnmsg, LogType::EVENT_WARN, EventType::SERVICE_NAME_CHANGE);

                // Actualizamos
                $hosts->updatePort($db_port['id'], [
                    "service" => $port['service'],
                    "online" => 1,
                    "last_check" => date_now(),
                ]);
            } elseif ($db_port['online'] == 0) {
                // Si el puerto estaba offline, actualizar a online
                $alertmsg = "Port UP detected: ({$port['service']}) ($pnumber)";
                $hosts->setWarnOn($host_id, $alertmsg, LogType::EVENT_WARN, EventType::PORT_UP);

                $hosts->updatePort($db_port['id'], [
                    "online" => 1,
                    "last_check" => date_now(),
                ]);
            }

            unset($db_ports_map[$key]); // Quitar procesado
        } else {
            Log::warning($key);
            // Crear nuevo puerto si no existe
            $hosts->addPort([
                'hid' => $host_id,
                'scan_type' => $scan_type,
                'protocol' => $protocol,
                'pnumber' => $pnumber,
                'online' => 1,
                'service' => $port['service'],
                'interface' => $interface,
                'ip_version' => $ip_version,
                'last_check' => date_now(),
            ]);

            $log_msg = "New port detected: $pnumber ({$port['service']})";
            $hosts->setAlertOn($host_id, $log_msg, LogType::EVENT_ALERT, EventType::PORT_NEW);
            unset($db_ports_map[$key]); // Quitamos procesado
        }
    endforeach;

    // Missing existing ports tag offline
    foreach ($db_ports_map as $db_port) :
        if ($db_port['online'] == 1) {
            $set = [
                'online' => 0,
                'last_check' => date_now(),
            ];
            $alertmsg = "Port DOWN detected: {$db_port['pnumber']} ({$db_port['service']})";
            $hosts->setAlertOn($host_id, $alertmsg, LogType::EVENT_ALERT, EventType::PORT_DOWN);
            $hosts->updatePort($db_port['id'], $set);
        }
    endforeach;
}
