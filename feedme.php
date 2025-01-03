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

    Notifications
        {
            "id": id,
            "cmd": "notification",
            "token": token,
            "version": AGENT_VERSION,
            "data":  data,
            "meta": meta
        }
            data {
                "name": "notification name" // Mandatory
                "msg": "Custom msg" //Optional
                ... other custom fields ...
            }

        "high_iowait", "iowait"
        "high_memory_usage", "memory_usage": meminfo_data
        "high_disk_usage", stats
        "high_cpu_usage", "cpu_usage": loadavg_data["usage"]
        "starting", "msg": "Custom msg"
        "shutdown", "msg": "Custom msg"
        "system_shutdown", "msg": "Custom msg"
 *
 *
 *
 */

/**
 * @var Database $db
 * @var AppContext $ctx Instance of AppCtx. Init in common.inc
 * @var array<string,mixed> $cfg common.inc.php
 */
require_once 'include/common.inc.php';
require_once 'include/common-call.php';
require_once 'include/feedme.inc.php';

$agent_default_interval = $cfg['agent_default_interval'];
$request_content = file_get_contents('php://input');

if ($request_content === false) :
    trigger_feedme_error('Error: file_get_contents');
endif;
$request = json_decode($request_content, true);

if (json_last_error() !== JSON_ERROR_NONE) :
    trigger_feedme_error('Invalid json receive: ' . json_last_error_msg());
endif;

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
    trigger_feedme_error('Invalid command receive id: ' . serialize($request));
endif;

if (!is_array($request['data'])) :
    trigger_feedme_error('Invalid data field recevive: not an array, id: ' . serialize($request));
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
$last_refreshing = (int) $ncfg->get('refreshing');
$refresh_time_seconds = (int) $cfg['refresher_time'] * 60;

if ((time() - $last_refreshing) < $refresh_time_seconds) :
    $agent_default_interval = 5;
endif;

$host_update_values['agent_next_report'] = time() + (int) $agent_default_interval;
$host_update_values['agent_last_contact'] = time();

if (empty($host['agent_version']) ||  $host['agent_version'] != (string) $request['version']) :
    $host_update_values['agent_version'] = (string) $request['version'];
endif;

if ((int) $host['online'] !== 1) :
    $host_update_values['online'] = 1;
endif;
$host_update_values['agent_online'] = 1;

if (!isEmpty($rdata)) :
    if (!isEmpty($rdata['loadavg'])) :
        $host_update_values['load_avg'] = serialize($rdata['loadavg']);
    endif;
    if (!isEmpty($rdata['loadavg_stats'])) :
        $set_stats = [
            'date' => date_now(),
            'type' => 2,   //loadavg
            'host_id' => $host['id'],
            'value' => $rdata['loadavg_stats']
        ];
        $db->insert('stats', $set_stats);
    endif;
    if (!isEmpty($rdata['iowait_stats'])) :
        $set_stats = [
            'date' => date_now(),
            'type' => 3,   //iowait
            'host_id' => $host['id'],
            'value' => $rdata['iowait_stats']
        ];
        $db->insert('stats', $set_stats);
    endif;
    if (!isEmpty($rdata['meminfo'])) :
        $host_update_values['mem_info'] = serialize($rdata['meminfo']);
    endif;
    if (!isEmpty($rdata['disksinfo'])) :
        $host_update_values['disks_info'] = serialize($rdata['disksinfo']);
    endif;
    if (!isEmpty($rdata['iowait'])) :
        $host_update_values['iowait'] = $rdata['iowait'];
    endif;
endif;

//TODO: ADD EVENT TYPE 1 to logHost notifications
if ($command === 'notification' && isset($rdata['name'])) :
    $log_msg = "Receive $command with id: $hostid, {$rdata['name']}";
    isset($rdata['msg']) ? $log_msg .= ':' . $rdata['msg'] : null;

    if ($rdata['name'] == 'starting') :
        Log::logHost('LOG_NOTICE', $host_id, $log_msg);
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
        Log::logHost('LOG_WARNING', $host_id, $log_msg);
    endif;
endif;

/* Update host */
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

$reply = 0;
switch ($command) :
    case 'ping':
        $reply = 1;
        $response['cmd'] = 'pong';
        $response['response_msg'] = true;
        break;
    case 'notification':
        //No response
        $reply = 0;
        break;
endswitch;

// Enviar la respuesta JSON
if ($reply) :
    header('Content-Type: application/json');
    echo json_encode($response);
endif;
