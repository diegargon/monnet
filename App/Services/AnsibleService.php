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
use App\Models\CmdAnsibleReportModel;
use App\Services\TemplateService;
use App\Services\DateTimeService;

class AnsibleService
{
    private \AppContext $ctx;
    private SocketClient $socketClient;
    private \Config $ncfg;
    private CmdAnsibleModel $cmdAnsibleModel;
    private TemplateService $templateService;
    private CmdAnsibleReportModel $ansibleReportModel;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get('Config');
        $this->cmdAnsibleModel = new CmdAnsibleModel($ctx);
        $this->templateService = new TemplateService($ctx);
    }

    /**
     *
     * @param int $target_id
     * @param string $playbook
     * @param array<string, string|int> $extra_vars
     * @return array<string, string|int>
     */
    public function runPlaybook(int $target_id, string $playbook, array $extra_vars = []): array
    {
        $user = $this->ctx->get('User');
        $hosts = $this->ctx->get('Hosts');
        $networks = $this->ctx->get('Networks');
        $host = $hosts->getHostById($target_id);

        $server_ip = $this->ncfg->get('ansible_server_ip');
        $server_port = $this->ncfg->get('ansible_server_port');

        if ($playbook == 'install-monnet-agent-systemd') :
            if (empty($host['token'])) :
                $token = $hosts->createHostToken($target_id);
            else :
                $token = $host['token'];
            endif;
            /* Set default config */
            $agent_config = [
                "id" => $host['id'],
                "token" => $token,
                "loglevel" => 'info',
                "default_interval" => $this->ncfg->get('agent_default_interval'),
                "ignore_cert" => $this->ncfg->get('agent_allow_selfcerts'),
                "server_host" => $_SERVER['HTTP_HOST'], //TODO Filter?
                "server_endpoint" => "/feedme.php",
            ];

            empty($networks) ? $networks = $this->ctx->get('Networks') : null;

            if (!empty($this->ncfg->get('agent_external_host')) && !$networks->isLocal($host['ip'])) {
                $agent_config['server_host'] = $this->ncfg->get('agent_external_host');
            }
            if (!empty($this->ncfg->get('agent_default_interval'))) {
                $agent_config['agent_default_interval'] = $this->ncfg->get('agent_default_interval');
            }
            $extra_vars['agent_config'] = json_encode($agent_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        endif;

        $data = $this->buildSendData($host, $playbook, $extra_vars);

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
        ) {
            /* SUCCESS */
            $playbook_id = $this->getPbIdByName($playbook);

            if ($playbook_id) {
                $pb_data = [
                    'host_id' => $host['id'],
                    'source_id' => $user->getId(),
                    'pb_id' => $playbook_id,
                    'rtype' => 1, //Manual
                    'report' => json_encode($responseArray),
                ];
                if (!isset($this->ansibleReportModel)) {
                    $this->ansibleReportModel = new CmdAnsibleReportModel($this->ctx);
                }
                $this->ansibleReportModel->insertReport($pb_data);
            }

            return ['status' => 'success', 'response_msg' => $responseArray];
        }

        $error_msg = 'Ansible status error: ';
        if (isset($responseArray['message'])) {
            $error_msg .= $responseArray['message'];
        }

        return ['status' => 'error', 'error_msg' => $error_msg];
    }

    /**
     *
     * @param int $hid
     * @param int $trigger_type
     * @param string $playbook
     * @param array<string, string|int> $extra_vars
     * @return array<string, string|int>
     */
    public function queueTask(int $hid, int $trigger_type, string $playbook, array $extra_vars = [])
    {
        $pb_id = $this->findPlaybookId($playbook);

        if (empty($pb_id)) {
            return ['status' => 'error', 'error_msg' => 'pb id not exists'];
        }

        $task_data = [
            'hid' => $hid,
            'pb_id' => $pb_id,
            'trigger_type' => $trigger_type,
            'task_name' => $playbook,
            'extra' => json_encode($extra_vars),
        ];
        if ($this->cmdAnsibleModel->createTask($task_data)) {
            $status = ['status' => 'success', 'response_msg' => 'success'];
        } else {
            $status = ['status' => 'error', 'error_msg' => 'Error creating task'];
        }

        return $status;
    }

    public function createTask(array $task_data): array
    {

        if ($this->cmdAnsibleModel->createTask($task_data)) {
            return ['status' => 'success', 'response_msg' => 'success'];
        }

        return ['status' => 'error', 'error_msg' => 'Error creating task'];
    }
    /**
     *
     * @param int $host_id
     * @return array<string, string>
     */
    public function getAnsibleTabDetails(int $host_id): array
    {

        $ansible['reports_list'] = $this->getHostHeadsReports($host_id);
        $ansible['ansible_vars'] = $this->cmdAnsibleModel->getAnsibleVarsByHostId($host_id);

        return $ansible;
    }

     /**
     * Gets the Ansible reports for a host applying the template
     *
     * @param int $host_id El ID del host.
     * @return string Los informes de Ansible.
     */
    public function getHostHeadsReports(int $host_id): string
    {
        if (!isset($this->ansibleReportModel)) {
            $this->ansibleReportModel = new CmdAnsibleReportModel($this->ctx);
        }
        $reports_opts = [
            'head' => 1,
            'host_id' => $host_id,
            'order' => 'DESC',
        ];

        $reports = $this->ansibleReportModel->getDbReports($reports_opts);

        $user = $this->ctx->get('User');
        $ncfg = $this->ctx->get('Config');
        //format TODO: Move
        foreach ($reports as &$report) {
            $playbook = $this->getPbById($report['pb_id']);
            if ($playbook) {
                $report['pb_name'] = $playbook['name'] . ' - ' . $playbook['desc'];
            }
            $timezone = $user->getTimeZone();
            $time_format = $ncfg->get('datetime_format');

            $dateTimeService = new DateTimeService();

            $report['user_date'] = $dateTimeService->utcToTz($report['date'], $timezone, $time_format);
            unset($report['date']);
        }
        $tdata['reports'] = $reports;

        return $this->templateService->getTpl('ansible-head-reports', $tdata);
    }

    /**
     *
     * @param array<string, mixed> $response
     * @return array<string, string|int>
     */
    public function asHtml(array $response): array
    {
        $response = $this->templateService->getTpl('ansible-report', $response);

        return [
            'status' => 'success',
            'response_msg' => $response,
        ];
    }

    /**
     *
     * @param array<string, string|int> $host
     * @param array<string, mixed>> $response
     * @return array<string, string|int>
     */
    public function fSystemLogs(array $host, array $response): array
    {
        $debug_lines = [];
        $host_ip = $host['ip'];
        foreach ($response['plays'] as $play) :
            foreach ($play['tasks'] as $task) :
                if (isset($task['hosts'][$host_ip]['action']) && $task['hosts'][$host_ip]['action'] === 'debug') {
                    $debug_lines = $task['hosts'][$host_ip]['msg'] ?? [];
                    foreach ($debug_lines as &$debug_line) :
                        $debug_line = $debug_line . '<br/>';
                    endforeach;
                    //$debug_lines[] =  serialize($task['hosts']);
                }
            endforeach;
        endforeach;

        return $debug_lines;
    }

    /**
     *
     * @param string $pb_name
     * @return int
     */
    public function getPbIdByName(string $pb_name): int
    {
        foreach ($this->ncfg->get('playbooks') as $play) {
            if ($play['name'] === $pb_name) :
                return $play['id'];
            endif;
        }

        return 0;
    }

    /**
     *
     * @param int $id
     * @return array<string, string|int
     */
    public function getPbById(int $id): array
    {
        foreach ($this->ncfg->get('playbooks') as $play) {
            if ($play['id'] === $id) :
                return $play;
            endif;
        }

        return [];
    }

    /**
     *
     * @param int $report_id
     * @return array<string,string>
     */
    public function getHtmlReportById(int $report_id): array
    {
        if (!isset($this->ansibleReportModel)) {
            $this->ansibleReportModel = new CmdAnsibleReportModel($this->ctx);
        }
        $response = $this->ansibleReportModel->getDbReportById($report_id);

        if(!$response) {
            return [
                'status' => 'error',
                'error' => 'Report id not exists'
            ];
        }

        try {
            $response_report = json_decode($response['report'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [
                'status' => 'error',
                'error_msg' => 'Error decoding JSON: ' . $e->getMessage()
            ];
        }

        $html_response = $this->asHtml($response_report);
        if ($html_response['status'] === 'success') {
            return [
                'status' => 'success',
                'response_msg' => $html_response['response_msg'],
            ];
        }

        return [
            'status' => 'error',
            'error_msg' => 'Unknown error getting the report'
        ];
    }

    public function getHostTasks(int $hid)
    {
        $tdata['host_tasks'] = $this->cmdAnsibleModel->getHostsTasks($hid);

        return  $this->templateService->getTpl('ansible-tasks', $tdata);
    }

    /**
     *
     * @param string $playbook
     * @return int|null
     */
    private function findPlaybookId(string $playbook): ?int
    {
        foreach ($this->ncfg->get('playbooks') as $pb) {
            if ($pb['name'] === $playbook) {
                return $pb['id'];
            }
        }
        return null;
    }

    /**
     *
     * @param array<string, string|int> $host
     * @param string $playbook
     * @param array<string, string|int> $extraVars
     * @return array<string, string|int>
     */
    private function buildSendData(array $host, string $playbook, array $extraVars = []): array
    {
        return [
            'playbook' => $playbook . '.yml',
            'extra_vars' => $extraVars,
            'ip' => $host['ip'],
            'user' => $this->ncfg->get('ansible_user') ?? 'ansible'
        ];
    }
}
