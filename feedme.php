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

// Leer la entrada JSON
$request = file_get_contents('php://input');
$data = json_decode($request, true);

// Validacion
if (!isset($data['cmd'], $data['token'], $data['version']) || $data['cmd'] !== 'ping') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Comando no válido o parámetros faltantes.'
    ]);
    exit;
}
$hosts = $ctx->get('Hosts');

// Respuesta al comando 'ping'
$response = [
    'cmd' => 'pong',
    'token' => $data['token'],
    'version' => $cfg['agent_version'],
    'data' => [
        'response_msg' => true,
        'refresh' => 10 // Opcional, modificar el tiempo de solicitud
    ]
];

// Enviar la respuesta JSON
header('Content-Type: application/json');
echo json_encode($response);

