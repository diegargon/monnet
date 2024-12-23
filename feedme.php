<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
/*
  {
      cmd: ping
      token: string
      version: 0.1
      data: []
  }
  {
      cmd: pong
      token: string
      version: 0.1
      data: [
        response_msg: true
        refresh: 1 //seconds (optional value)
      ]
  }
 */

require_once 'include/common.inc.php';
require_once 'include/common-call.php';

// Configuración
$agent_refresh_interval = $cfg['agent_refresh_interval'];
// Leer la entrada JSON
$request = file_get_contents('php://input');
$data = json_decode($request, true);

Log::debug("Host contact request". print_r($request, true));
// Validacion
if (!isset($data['id'], $data['cmd'], $data['token'], $data['version']) || $data['cmd'] !== 'ping') {
    Log::err("Invalid data receive");
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Comando no valido o parametros faltantes.'
    ]);
    exit;
}
$hosts = $ctx->get('Hosts');
$host = $hosts->getHostById($data['id']);
if (!$host):
    Log::err("Host not found id:", $host['id']);
    echo json_encode([
        'error' => 'Host not found'
    ]);
else:
    if (empty($host['token']) || $host['token'] !== $data['token']):
        Log::warning("Invalid Token receive from id:", $host['id']);
        echo json_encode([
            'error' => 'Invalid Token'
        ]);
    endif;
endif;

if (empty($host['agent_installed'])):
    $hosts->update($host['id'],['agent_installed' => 1]);
endif;

$hosts->update($host['id'],['agent_last_report' => time()]);
// Respuesta al comando 'ping'
$response = [
    'cmd' => 'pong',
    'token' => $data['token'],
    'version' => $cfg['agent_version'],
    'data' => [
        'response_msg' => true,
        'refresh' => $agent_refresh_interval
    ]
];

// Enviar la respuesta JSON
header('Content-Type: application/json');
echo json_encode($response);

