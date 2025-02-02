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
  TABLA TASK
  `id`
  `hid`             host id, ver posibilidad de rango en el futuro
  `pb_id`           playbook a ejecutar
  `trigger_type`    tipo de trigger 1 manual  2 temporizaedo 3 otra task 4 evento
  `last_triggered`  ultima vez que se ejecuto
  `task_name`       nombre comun
  `next_task`       encadena tareas por id
  `disable`         deshabilita la tarea

   Trigger Manual: Encolo el comando, se ejecuta via mgateway, deberia enviar un mensaje
    para avisar de que hay algo en la queue.
 *
    TODO: Field temporizador ¿formato? horas? fecha?
 */

Class TaskAnsible
{
    /**
     *
     * @param AppContext $ctx
     * @param int $trigger_type
     * @param int $hid
     * @param string $playbook
     * @param array<string,string> $extra_vars
     * @return array<string,string>
     */
    public static function create(AppContext $ctx, int $trigger_type, int $hid, string $playbook, array $extra_vars = []): array
    {

        $db = $ctx->get('Mysql');
        $cfg = $ctx->get('cfg');
        $status = ['status' => 'error', 'msg' => 'id not exists'];
        foreach($cfg['playbooks'] as $playbook_std) :
            if ($playbook_std['name'] === $playbook) {
                $pb_id = $playbook_std['id'];

            }
        endforeach;

        if (empty($pb_id)) {
            return $status;
        }

        $insert_data = [
            'hid' => $hid,
            'pb_id' => $pb_id,
            'trigger_type' => $trigger_type,
            'task_name' => $playbook,
            'extra' => json_encode($extra_vars),
        ];
        $ret = $db->insert('tasks', $insert_data);
        ($ret) ? $status = ['status' => 'success', 'msg' => 'success'] : null;

        return $status;
    }

    public static function delete()
    {

    }

    /**
     *
     * @param AppContext $ctx
     * @param array<string,mixed> $host
     * @param string $playbook
     * @param array<string,mixed> $extra_vars
     *
     * @return array<mixed,mixed>
     */
    public static function runPlaybook(AppContext $ctx, array $host, string $playbook, ?array $extra_vars = []): array
    {
        $ncfg = $ctx->get('Config');
        $user = $ctx->get('User');
        $db = $ctx->get('Mysql');
        $cfg = $ctx->get('cfg');

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
            /* SUCCESS */
            $playbook_id = 0;

            foreach ($cfg['playbooks'] as $play) {
                if ($play['name'] === $playbook) :
                    $playbook_id = $play['id'];
                    break;
                endif;
            }
            if ($playbook_id) {
                $insert_data = [
                    'host_id' => $host['id'],
                    'source_id' => $user->getId(),
                    'pb_id' => $playbook_id,
                    'rtype' => 1, //Manual
                    'report' => $response,
                ];
                $db->insert('reports', $insert_data);
            }

            return $responseArray;
        }

        $error_msg = 'Ansible status error: ';
        if (isset($responseArray['message'])) {
            $error_msg .= $responseArray['message'];
        }

        return ['status' => 'error', 'error_msg' => $error_msg, 'response' => $response];
    }

}
