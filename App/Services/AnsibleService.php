<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Services\SocketClient;
use App\Models\CmdAnsibleModel;

class AnsibleService
{
    private \AppContext $ctx;
    private SocketClient $socketClient;
    private \Config $ncfg;
    private CmdAnsibleModel $cmdAnsibleModel;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->cmdAnsibleModel = new CmdAnsibleModel($ctx);
    }

    /**
     *
     * @param int $target_id
     * @param string $playbook
     * @param array<string, string|int> $extra_vars
     * @return array<string, string|int>
     */
    public function runPlaybook(int $target_id, string $playbook, array $extra_vars): array
    {
        $ctx = $this->ctx;
        $this->ncfg = $ctx->get('Config');
        $user = $ctx->get('User');
        $db = $ctx->get('Mysql');
        $cfg = $ctx->get('cfg');
        $hosts = $ctx->get('Hosts');
        $host = $hosts->getHostById($target_id);

        $server_ip = $this->ncfg->get('ansible_server_ip');
        $server_port = $this->ncfg->get('ansible_server_port');


        $data = $this->buildResponse($host, $playbook, $extra_vars);

        $send_data = [
            'command' => 'playbook',
            'data' => $data
        ];

        try {
            $this->socketClient = new SocketClient($server_ip, $server_port);
            $responseArray = $this->socketClient->sendAndReceive($send_data);
        } catch (\Exception $e) {
            return ['status' => 'error', 'error_msg' => $e->getMessage()];
        }


        if (
                isset($responseArray['status']) &&
                $responseArray['status'] === 'success' &&
                isset($responseArray['result'])
        ){
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
                    'report' => json_encode($responseArray),
                ];
                $db->insert('reports', $insert_data);
            }

            return $responseArray;
        }

        $error_msg = 'Ansible status error: ';
        if (isset($responseArray['message'])) {
            $error_msg .= $responseArray['message'];
        }

        return ['status' => 'error', 'error_msg' => $error_msg];
    }

    /**
     *
     * @param array<string, string|int> $host
     * @param string $playbook
     * @param array<string, string|int> $extraVars
     * @return array<string, string|int>
     */
    private function buildResponse(array $host, string $playbook, array $extraVars): array
    {
        return [
            'command' => 'playbook',
            'data' => [
                'playbook' => $playbook . '.yml',
                'extra_vars' => $extraVars,
                'ip' => $host['ip'],
                'user' => $this->config->get('ansible_user') ?? 'ansible'
            ]
        ];
    }

    /**
     *
     * @param int $hid
     * @param int $trigger_type
     * @param string $playbook
     * @param array<string, string|int> $extra_vars
     * @return array<string, string|int>
     */
    public function createTask(int $hid, int $trigger_type, string $playbook, array $extra_vars)
    {
        $db = $this->ctx->get('Mysql');

        $pb_id = $this->findPlaybookId($playbook);

        if (empty($pb_id)) {
            return ['status' => 'error', 'msg' => 'pb id not exists'];
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

    /**
     *
     * @param string $playbook
     * @return int|null
     */
    private function findPlaybookId(string $playbook): ?int
    {
        $cfg = $this->ctx->get('cfg');
        foreach ($cfg['playbooks'] as $pb) {
            if ($pb['name'] === $playbook) {
                return $pb['id'];
            }
        }
        return null;
    }

    /**
     * Obtiene los informes de Ansible para un host.
     *
     * @param int $host_id El ID del host.
     * @return array<string, string|int> Los informes de Ansible.
     */
    public function getReports(int $host_id) {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT * FROM reports WHERE host_id = :host_id ORDER BY date DESC";
        $params = ['host_id' => $host_id];

        return $db->qfetchAll($query, $params);
    }
}
