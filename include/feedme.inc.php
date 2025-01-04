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

    // Obtener los puertos actuales de la base de datos, organizados en un mapa para fácil comparación
    $actual_host_ports = $hosts->getHostScanPorts($host_id, $scan_type);
    $actual_ports_map = [];
    foreach ($actual_host_ports as $port) {
        $key = "{$port['protocol']}:{$port['pnumber']}:{$port['interface']}:{$port['ip_version']}";
        $actual_ports_map[$key] = $port;
    }

    // Procesar los puertos reportados en $listen_ports
    foreach ($listen_ports as $port) {
        $protocol = ($port['protocol'] === 'tcp') ? 1 : 2; // Convertir protocolo a formato numérico
        $key = "{$protocol}:{$port['port']}:{$port['interface']}:{$port['ip_version']}";

        if (isset($actual_ports_map[$key])) {
            // Port exists check changes and update
            $db_port = $actual_ports_map[$key];
            if ($db_port['online'] == 0 || $db_port['service'] !== $port['service']) {
                $set = [
                    "online" => $online,
                    "service" => $port['service'],
                    "last_change" => date_now()
                ];
                $hosts->updatePort($db_port['id'], $set);
            }
            unset($actual_ports_map[$key]); // Marcamos como procesado
        } else {
            // Port not exist. Create.
            $insert_values = [
                'scan_type' => $scan_type,
                'protocol' => $protocol,
                'pnumber' => $port['port'],
                'online' => $online,
                'service' => $port['service'],
                'interface' => $port['interface'],
                'ip_version' => $port['ip_version'],
                'last_change' => date_now(),
            ];
            $hosts->addPort($host_id, $insert_values);
        }
    }

    // Missing existing ports tag offline
    foreach ($actual_ports_map as $db_port) {
        if ($db_port['online'] == 1) {
            $set = [
                'online' => 0,
                'last_change' => date_now(),
            ];
            $hosts->updatePort($db_port['id'], $set);
        }
    }
}
