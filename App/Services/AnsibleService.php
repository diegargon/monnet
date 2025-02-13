<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class AnsibleService
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function runPlaybook($command_values)
    {
        $target_id = $this->taskModel->varInt($command_values['id']);
        $playbook = $this->taskModel->varString($command_values['value']);
        $extra_vars = $this->taskModel->varJson($command_values['extra_vars']);

        $response = $this->ansibleService->runPlaybook($target_id, $playbook, $extra_vars);

        if ($response['status'] === "success") {
            return [
                'command_success' => 1,
                'response_msg' => $response,
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => $response['error_msg'],
            ];
        }
    }

    /**
     * Obtiene los informes de Ansible para un host.
     *
     * @param int $host_id El ID del host.
     * @return array Los informes de Ansible.
     */
    public function getReports(int $host_id) {
        global $db;

        $query = "SELECT * FROM ansible_reports WHERE host_id = :host_id ORDER BY date DESC";
        $params = ['host_id' => $host_id];

        return $db->fetchAll($query, $params);
    }
}
