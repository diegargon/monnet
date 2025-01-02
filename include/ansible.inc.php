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
 * @param AppContext $ctx
 * @param array<string,mixed> $host
 * @param string $playbook
 * @param array<string,mixed> $extra_vars
 *
 * @return array<mixed,mixed>
 */
function ansible_playbook(AppContext $ctx, array $host, string $playbook, ?array $extra_vars = []): array
{
    $ncfg = $ctx->get('Config');

    $server_ip = $ncfg->get('ansible_server_ip');
    $server_port = $ncfg->get('ansible_server_port');

    $data = [
        'playbook' => $playbook . '.yml',
        'extra_vars' => $extra_vars,
        'ip' => $host['ip'],
        'user' => $ncfg->get('ansible_user'),
    ];

    if (!empty($ncfg->get('ansible_user'))) :
        $data['user'] = $ncfg->get('ansible_user');
    endif;

    $send_data = [
        'command' => 'playbook',
        'data' => $data
    ];
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        $error_msg = 'Socket Creation fail: ' . socket_strerror(socket_last_error());

        return ['status' => 'error', 'error_msg' => $error_msg];
    }

    $result = socket_connect($socket, $server_ip, $server_port);
    if ($result === false) {
        $error_msg = 'Socket Connection fail: ' . socket_strerror(socket_last_error($socket));

        return ['status' => 'error', 'error_msg' => $error_msg];
    }

    $encoded_send_data = json_encode($send_data);

    if ($encoded_send_data === false) :
        return ['status' => 'error', 'error_msg' => 'Json returns fasle'];
    endif;

    if (json_last_error() !== JSON_ERROR_NONE) :
        return ['status' => 'error', 'error_msg' => 'Invalid json receive: ' . json_last_error_msg()];
    endif;


    socket_write($socket, $encoded_send_data, strlen($encoded_send_data));

    $response = '';
    /*
     * Contamos llaves abiertas para detectar el final }
     */
    $openBraces = 0;
    $jsonComplete = false;

    while (!$jsonComplete) {
        $chunk = socket_read($socket, 1024); // Leer fragmentos de 1024 bytes
        if ($chunk === false) {
            $error_msg = 'Error reading socket: ' . socket_strerror(socket_last_error($socket));

            return ['status' => 'error', 'error_msg' => $error_msg];
        }
        if ($chunk === '') {
            $error_msg = 'Chunk Error reading socket: Incomplete JSON response';

            return ['status' => 'error', 'error_msg' => $error_msg];
        }

        $response .= $chunk;

        // Verificar balanceo de llaves
        foreach (str_split($chunk) as $char) {
            if ($char === '{' || $char === '[') {
                $openBraces++;
            } elseif ($char === '}' || $char === ']') {
                $openBraces--;
            }
        }

        // Full JSON (all braces closed)
        if ($openBraces === 0 && trim($response) !== '') {
            $jsonComplete = true;
        }
    }

    socket_close($socket);

    $responseArray = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $error_msg = 'JSON decode error: ' . json_last_error_msg();

        return ['status' => 'error', 'error_msg' => $error_msg, 'response' => $response];
    }

    if (isset($responseArray['status']) && $responseArray['status'] === 'success' && isset($responseArray['result'])) {
        /* OUTPUT SUCCESS */
        return $responseArray;
    } else {
        $error_msg = 'Ansible status error: ';
        if (isset($responseArray['message'])) {
            $error_msg .= $responseArray['message'];
        }

        return ['status' => 'error', 'error_msg' => $error_msg, 'response' => $response];
    }
}
