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
      response_msg: true
      refresh: 5 // Inform when is the next update
      data: []
  }
 */

/**
 * @var AppContext $ctx Instance of AppCtx. Init in common.inc
 * @var array<string,string> $cfg common.inc.php
 */
require_once 'include/common.inc.php';
require_once 'include/common-call.php';
require_once 'include/feedme.inc.php';

$agent_refresh_interval = $cfg['agent_refresh_interval'];
$request_content = file_get_contents('php://input');
$request = json_decode($request_content, true);

Log::debug("Host contact request" . print_r($request, true));

// Validation
if (!isset($request['id'], $request['cmd'], $request['token'], $request['version'])) :
    trigger_feedme_error('Invalid data receive: Empty or missing fields');
endif;

if (
    !is_numeric($request['id']) ||
    !is_string($request['cmd']) ||
    !is_string($request['token']) ||
    !is_float($request['version'])
) :
    if (is_numeric($request['id'])) :
        $dtype_error_host = $request['id'];
    else :
        $dtype_error_host = 'Wrong id';
    endif;
    trigger_feedme_error('Invalid datatypes receive id: ' . $dtype_error_host);
endif;

$host_id = $request['id'];

if ($request['cmd'] !== 'ping') :
    trigger_feedme_error('Invalid command receive id: ' . $host_id);
endif;

if (!is_array($request['data'])) :
    trigger_feedme_error('Invalid data field recevive: not an array, id: ' . $host_id);
else :
    $rdata = $request['data'];
endif;

$hosts = $ctx->get('Hosts');
$host = $hosts->getHostById($request['id']);
if (!$host) :
    Log::err("Host not found, requested id:", $request['id']);
    echo json_encode([
        'error' => 'Host not found'
    ]);
    exit();
else :
    if (empty($host['token']) || $host['token'] !== $request['token']) :
        Log::warning("Invalid Token receive from id:", $host['id']);
        echo json_encode([
            'error' => 'Invalid Token'
        ]);
        exit();
    endif;
endif;

if (empty($host['agent_installed'])) :
    $hosts->update($host['id'], ['agent_installed' => 1]);
endif;

$host_update_values['agent_next_report'] = time() + (int) $agent_refresh_interval;

if( (int) $host['online'] !== 1) {
    $host_update_values['online'] = 1;
}
$hosts->update($host['id'], $host_update_values);

// Respuesta al comando 'ping'
$response = [
    'cmd' => 'pong',
    'token' => $request['token'],
    'version' => $cfg['agent_version'],
    'response_msg' => true,
    'refresh' => $agent_refresh_interval,
    'data' => []
];

// Enviar la respuesta JSON
header('Content-Type: application/json');
echo json_encode($response);
