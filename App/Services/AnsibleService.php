<?php

/**
 * Ansible related
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
 * Gateway
 *  Module: ansible
 *  Commands:
 *   - get_all_playbooks_metadata
 *   - get_all_playbooks_ids
 *   - playbook_exec
 */

namespace App\Services;

use App\Core\AppContext;
use App\Core\ConfigService;

use App\Services\TemplateService;
use App\Services\DateTimeService;
use App\Services\LogSystemService;
use App\Services\GatewayService;
use App\Services\Filter;
use App\Services\HostService;
use App\Services\NetworksService;
use App\Services\UserService;

use App\Models\CmdAnsibleModel;
use App\Models\CmdAnsibleReportModel;

class AnsibleService
{
    private AppContext $ctx;
    private ConfigService $ncfg;
    private CmdAnsibleModel $cmdAnsibleModel;
    private CmdAnsibleReportModel $ansibleReportModel;
    private TemplateService $templateService;
    private LogSystemService $logSys;
    private GatewayService $gatewayService;
    private HostService $hostService;
    private NetworksService $networksService;

    private array $playbooks_metadata = [];

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get(ConfigService::class);
        $this->cmdAnsibleModel = new CmdAnsibleModel($ctx);
        $this->templateService = new TemplateService($ctx);
        $this->gatewayService = new GatewayService($ctx);
        $this->logSys = new LogSystemService($ctx);
        $this->hostService = new HostService($ctx);
        $this->networksService = new NetworksService($ctx);
    }

    /**
     *
     * @param int $target_id
     * @param string $playbook_id
     * @param array<string, string|int> $extra_vars
     * @return array<string, mixed>
     */
    public function runPlaybook(int $target_id, string $playbook_id, array $extra_vars = []): array
    {
        $user = $this->ctx->get(UserService::class);
        $host = $this->hostService->getHostById($target_id);

        if ($playbook_id == 'std-install-monnet-agent-systemd') {
            if (empty($host['token'])) {
                $token = $this->hostService->createToken($target_id);
            } else {
                $token = $host['token'];
            }
            /* Set default config */
            $agent_config = [
                "id" => $host['id'],
                "token" => $token,
                "agent_log_level" => 'INFO',
                "default_interval" => $this->ncfg->get('agent_default_interval'),
                "ignore_cert" => $this->ncfg->get('agent_allow_selfcerts'),
                "server_host" => Filter::getServerHost() ?: 'localhost',
                "mem_alert_threshold" => $this->ncfg->get('default_mem_alert_threshold'),
                "mem_warn_threshold" => $this->ncfg->get('default_mem_warn_threshold'),
                "disks_alert_threshold" => $this->ncfg->get('default_disks_alert_threshold'),
                "disks_warn_threshold" => $this->ncfg->get('default_disks_warn_threshold'),
                "server_endpoint" => "/feedme.php",
            ];

            // Usar networksService en vez de contexto
            $networks = $this->networksService;

            if (!empty($this->ncfg->get('agent_external_host')) && !$networks->isLocal($host['ip'])) {
                $agent_config['server_host'] = $this->ncfg->get('agent_external_host');
            }
            if (!empty($this->ncfg->get('agent_default_interval'))) {
                $agent_config['agent_default_interval'] = $this->ncfg->get('agent_default_interval');
            }
            $extra_vars['agent_config'] = json_encode($agent_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['status' => 'error', 'error_msg' => 'Error encoding JSON: ' . json_last_error_msg()];
            }
        }

        $data = $this->buildSendData($host, $playbook_id, $extra_vars);

        $send_data = [
            'command' => 'playbook_exec',
            'module' => 'ansible',
            'data' => $data
        ];

        return $this->gatewayService->sendCommand($send_data);
    }

    /**
     *
     * @param int $hid
     * @param int $trigger_type
     * @param string $pid
     * @param array<string, string|int> $extra_vars
     * @return array<string, string|int>
     */
    public function queueTask(int $hid, int $trigger_type, string $pid, array $extra_vars = [])
    {
        $pb = $this->getPlaybookById($pid);

        if (empty($pid)) {
            return ['status' => 'error', 'error_msg' => 'pid not exists:' . $pid];
        }

        $task_name = $pb['name'];

        // Verify if a task with the same name and host already exists and is not done=1
        $existing_tasks = $this->cmdAnsibleModel->getHostsTasks($hid);
        foreach ($existing_tasks as $task) {
            if (
                isset($task['task_name'], $task['done']) &&
                $task['task_name'] === $task_name &&
                (int)$task['done'] !== 1
            ) {
                return [
                    'status' => 'error',
                    'error_msg' => 'Task already exists with the same name and host that is not done=1'
                ];
            }
        }

        $task_data = [
            'hid' => $hid,
            'pid' => $pid,
            'trigger_type' => $trigger_type,
            'task_name' => $task_name,
            /* 'extra' => json_encode($extra_vars), */
        ];
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['status' => 'error', 'error_msg' => 'Error encoding JSON: ' . json_last_error_msg()];
        }

        if ($this->cmdAnsibleModel->createTask($task_data)) {
            $status = ['status' => 'success', 'response_msg' => 'success'];
        } else {
            $status = ['status' => 'error', 'error_msg' => 'Error creating task'];
        }

        return $status;
    }

    /**
     *
     * @param array<string, int|string> $task_data
     * @return array<string, int|string>
     */
    public function createTask(array $task_data): array
    {
        if ($this->cmdAnsibleModel->createTask($task_data)) {
            return ['status' => 'success', 'response_msg' => 'success'];
        }

        return ['status' => 'error', 'error_msg' => 'Error creating task'];
    }

    /**
     * @param int $task_id
     * @param array<string, int|string> $task_data
     * @return array<string, int|string>
     */
    public function updateTask(int $task_id, array $task_data): array
    {
        if ($this->cmdAnsibleModel->updateTask($task_id, $task_data)) {
            return ['status' => 'success', 'response_msg' => 'success'];
        }

        return ['status' => 'error', 'error_msg' => 'Error updating task'];
    }
    /**
     *
     * @param int $host_id
     * @return array<string, string>
     */
    public function getAnsibleTabDetails(int $host_id): array
    {

        $response['reports_list'] = $this->getHostHeadsReports($host_id);
        $response['ansible_vars'] = $this->cmdAnsibleModel->getAnsibleVarsByHostId($host_id);

        # TODO: Filter tags respect os
        # TODO: Add os_family and os to tags filter of host os is set
        if ($this->setPbMetadata()) {
            // Añadir os y os_family a tags si existen
            $playbooks_metadata = [];
            foreach ($this->playbooks_metadata as $pb) {
                $pb_mod = $pb;
                if (isset($pb_mod['os'])) {
                    if (!isset($pb_mod['tags']) || !is_array($pb_mod['tags'])) {
                        $pb_mod['tags'] = [];
                    }
                    if (is_array($pb_mod['os'])) {
                        foreach ($pb_mod['os'] as $os) {
                            if (!in_array($os, $pb_mod['tags'], true)) {
                                $pb_mod['tags'][] = $os;
                            }
                        }
                    } elseif (is_string($pb_mod['os'])) {
                        if (!in_array($pb_mod['os'], $pb_mod['tags'], true)) {
                            $pb_mod['tags'][] = $pb_mod['os'];
                        }
                    }
                }
                if (isset($pb_mod['os_family'])) {
                    if (!isset($pb_mod['tags']) || !is_array($pb_mod['tags'])) {
                        $pb_mod['tags'] = [];
                    }
                    if (is_array($pb_mod['os_family'])) {
                        foreach ($pb_mod['os_family'] as $osf) {
                            if (!in_array($osf, $pb_mod['tags'], true)) {
                                $pb_mod['tags'][] = $osf;
                            }
                        }
                    } elseif (is_string($pb_mod['os_family'])) {
                        if (!in_array($pb_mod['os_family'], $pb_mod['tags'], true)) {
                            $pb_mod['tags'][] = $pb_mod['os_family'];
                        }
                    }
                }
                $playbooks_metadata[] = $pb_mod;
            }
            $response['playbooks_metadata'] = $playbooks_metadata;
        }

        return $response;
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

        $user = $this->ctx->get(UserService::class);
        $ncfg = $this->ctx->get(ConfigService::class);
        //format TODO: Move
        foreach ($reports as &$report) {
            $playbook = $this->getPbById($report['pid']);
            if ($playbook) {
                $report['pb_name'] = $playbook['name'] . ' - ' . $playbook['description'];
            }
            $timezone = $user->getTimezone();
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
     * @param array<string, mixed> $report
     * @return array<string, string|int>
     */
    public function asHtml(array $report): array
    {
        $response = $this->templateService->getTpl('ansible-report', $report);

        return [
            'status' => 'success',
            'response_msg' => $response,
        ];
    }

    /**
     *
     * @param array<string, string|int> $host
     * @param array<string, mixed>> $result
     * @return string
     */
    public function fSystemLogs(array $host, array $result): string
    {
        $debug_lines = '';
        $host_ip = $host['ip'];
        if (!isset($result['plays']) || !is_array($result['plays'])) {
            return $debug_lines = 'Ansible result Plays Format Error';
        }
        foreach ($result['plays'] as $play) :
            if (!isset($play['tasks']) || !is_array($play['tasks'])) {
                return $debug_lines = 'Ansible result Tasks Format Error';
            }
            foreach ($play['tasks'] as $task) :
                if (isset($task['hosts'][$host_ip]['action']) && $task['hosts'][$host_ip]['action'] == 'debug') {
                    if (!empty($task['hosts'][$host_ip]['msg'])) {
                        return implode('<br/>', $task['hosts'][$host_ip]['msg']);
                    } else {
                        $debug_lines = "empty";
                    }
                }
            endforeach;
        endforeach;

        return $debug_lines;
    }

    /**
     *
     * @param string $pb_name
     * @return string
     */
    public function getPbIdByName(string $pb_name): string
    {
        if ($this->setPbMetadata()) {
            foreach ($this->playbooks_metadata as $play) {
                if ($play['name'] === $pb_name) {
                    return $play['id'];
                }
            }
        }
        $this->logSys->warning("Playbook name: $pb_name not found");

        return '';
    }

    /**
     *
     * @param string $id
     * @return array<string, string|int
     */
    public function getPbById(string $id): array
    {
        if ($this->setPbMetadata()) {
            foreach ($this->playbooks_metadata as $play) {
                if ($play['id'] === $id) :
                    return $play;
                endif;
            }
        }
        $this->logSys->warning("Playbook id: $id not found");

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

        if (!$response) {
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

    /**
     *
     * @param int $hid
     * @return array<string, mixed>
     */
    public function getHostTasks(int $hid): array
    {
        $tdata['host_tasks'] = $this->cmdAnsibleModel->getHostsTasks($hid);

        $this->setPbMetadata();

        if (!empty($this->playbooks_metadata)) {
            $tdata['pb_meta'] = $this->playbooks_metadata;
        } else {
            $tdata['pb_meta'] = [];
        }

        $pb_sel = '<option value="" disable selected>No select</option>';
        foreach ($this->playbooks_metadata as $playbook) {
            $pb_sel .= "<option value={$playbook['id']}>{$playbook['name']}</option>";
        }

        $response = [
            'tasks_list' => $this->templateService->getTpl('ansible-tasks', $tdata),
            'pb_sel' => $pb_sel,
        ];

        return  $response;
    }

    private function getPlaybookById(string $pid)
    {
        // Ensure playbook metadata is loaded
        $pb_metadata_result = $this->setPbMetadata();
        if (isset($pb_metadata_result['status']) && $pb_metadata_result['status'] === 'error') {
            $this->logSys->error('Error loading playbook metadata: ' . $pb_metadata_result['error_msg']);
            return null;
        }

        foreach ($this->playbooks_metadata as $pb) {
            if ($pb['id'] === $pid) {
                return $pb;
            }
        }

        return null;
    }

    /**
     *
     * @param string $playbook
     * @return string|null
     */
    private function findPlaybookId(string $playbook): ?string
    {
        // Ensure playbook metadata is loaded
        $pb_metadata_result = $this->setPbMetadata();
        if (isset($pb_metadata_result['status']) && $pb_metadata_result['status'] === 'error') {
            $this->logSys->error('Error loading playbook metadata: ' . $pb_metadata_result['error_msg']);
            return null;
        }

        foreach ($this->playbooks_metadata as $pb) {
            if ($pb['name'] === $playbook) {
                return $pb['id'];
            }
        }

        return null;
    }

    /**
     *
     * @param array<string, string|int> $host
     * @param string $playbook_id
     * @param array<string, string|int> $extraVars
     * @return array<string, string|int>
     */
    private function buildSendData(array $host, string $playbook_id, array $extraVars = []): array
    {
        $user = $this->ctx->get(UserService::class);
        return [
            'pid' => $playbook_id,
            'extra_vars' => $extraVars,
            'ip' => $host['ip'],
            'hid' => $host['id'],
            'user' => $this->ncfg->get('ansible_user') ?? 'ansible',
            'source_id' => $user->getId()
        ];
    }

    /**
     *
     * @return array<string, string|int>
     */
    public function getPbIds(): array
    {
        $request = [
            'command' => 'get_all_playbooks_ids',
            'module' => 'ansible',
        ];

        return $this->gatewayService->sendCommand($request);
    }

    /**
     *
     * @return array<string, string|int>
     */
    public function getPbMetadata(): array
    {
        $this->setPbMetadata();

        return $this->playbooks_metadata;
    }

    /**
     *
     * @return bool
     */
    private function setPbMetadata(): bool
    {
        if (!empty($this->playbooks_metadata)) {
            return true;
        }

        $request = [
            'command' => 'get_all_playbooks_metadata',
            'module' => 'ansible',
        ];
        $response = $this->gatewayService->sendCommand($request);

        if (!isset($response['status'])) {
            $this->logSys->warning('No status error setting pb metada');
            return false;
        }

        if ($response['status'] == 'success' && isset($response['response_msg']))  {
            $this->playbooks_metadata = $response['response_msg'];
            return true;
        } elseif ($response['status'] == 'error' && isset($response['error_msg'])) {
            $this->logSys->warning($response['error_msg']);
            return false;
        }

        $this->logSys->warning('Unknown error getting pb metadata');
        return false;
    }
}
