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

$agent_default_interval = $cfg['agent_default_interval'];
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
    !is_string($request['version'])
) :
    if (is_numeric($request['id'])) :
        $dtype_error_host = $request['id'];
    else :
        $dtype_error_host = 'Wrong id';
    endif;
    trigger_feedme_error('Invalid datatypes receive id: ' . $dtype_error_host);
endif;

$valid_commands = ['ping', 'notification'];

if (!in_array($request['cmd'], $valid_commands, true)) :
    trigger_feedme_error('Invalid command receive id: ' . $request['id']);
endif;

if (!is_array($request['data'])) :
    trigger_feedme_error('Invalid data field recevive: not an array, id: ' . $request['id']);
endif;

/* Setting Vars */
$command = $request['cmd'];
$host_id = $request['id'];
$hosts = $ctx->get('Hosts');
$host = $hosts->getHostById($request['id']);
$rdata = $request['data'];


if (!$host) :
    Log::err("Host not found, requested id:", $host_id);
    echo json_encode([
        'error' => 'Host not found'
    ]);
    exit();
elseif (empty($host['token']) || $host['token'] !== $request['token']) :
    Log::warning("Invalid Token receive from id:", $host_id);
    echo json_encode([
        'error' => 'Invalid Token'
    ]);
    exit();
endif;

$agent_logId = '[AGENT v' . $request['version'] . '][' . $host['display_name'] . '] ';

if (empty($host['agent_installed'])) :
    $hosts->update($host_id, ['agent_installed' => 1]);
endif;


/*
 * Si alguien esta refrescando solicitamos que los agentes esten mas atentos
 */
$last_refreshing = $ncfg->get('refreshing');
$refresh_time_seconds = $cfg['refresher_time'] * 60;

if ((time() - $last_refreshing) < $refresh_time_seconds) :
    $agent_default_interval = 5;
endif;

$host_update_values['agent_next_report'] = time() + (int) $agent_default_interval;

if( (int) $host['online'] !== 1) :
    $host_update_values['online'] = 1;
endif;
$host_update_values['agent_online'] = 1;

$hosts->update($host['id'], $host_update_values);

/* Response Template */
$response = [
    'cmd' => $command,  /* required */
    'token' => $request['token'], /* required */
    'version' => $cfg['agent_min_version'],
    'response_msg' => null,
    'refresh' => $agent_default_interval,
    'data' => []
];

switch ($command)
{
    case 'ping':
        $response['cmd'] = 'pong';
        $response['response_msg'] = true;
        break;
    case 'notification':
        Log::logHost('LOG_NOTICE', $host_id, $agent_logId . $rdata['type'] . ':' . $rdata['msg']);
        break;
}

// Enviar la respuesta JSON
header('Content-Type: application/json');
echo json_encode($response);
