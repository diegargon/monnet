<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
CommandRouter       Recibe el comando y los valores.
CommandRouter       Redirige la solicitud al método correspondiente en HostController.
CmdHostController   Valida los datos de entrada y llama a HostService para obtener los datos.
HostService         Se comunica con HostModel para obtener los datos y realiza cualquier lógica de negocio necesaria.
HostService         Devuelve los datos a HostController.
CmdHostController   Formatea los datos (opcionalmente usando HostFormatted) y prepara la respuesta.
CmdHostController   Devuelve la respuesta a CommandRouter.
CommandRouter       Devuelve la respuesta final al cliente.
 */

namespace App\Controllers;

use App\Models\CmdHostModel;
use App\Models\CmdHostLogsModel;
use App\Models\CmdHostNotesModel;
use App\Services\Filter;
use App\Services\AnsibleService;
use App\Services\TemplateService;
use App\Services\HostFormatter;
use App\Services\HostService;
use App\Services\HostLogsService;

class CmdHostController
{

    private CmdHostModel $cmdHostModel;
    private CmdHostLogsModel $cmdHostLogsModel;
    private CmdHostNotesModel $cmdHostNotesModel;
    private HostLogsService $hostLogsService;

    private Filter $filter;
    private \AppContext $ctx;
    private $ansibleService;
    private $templateService;
    private $reportKeysToShow = ["id", "display_name", "ip", 'mac', "online"];

    public function __construct(\AppContext $ctx)
    {
        $this->hostService = new HostService($ctx);
        $this->hostFormatter = new HostFormatter($ctx);
        $this->cmdHostModel = new CmdHostModel($ctx);
        $this->cmdHostLogsModel = new CmdHostLogsModel($ctx);
        $this->cmdHostNotesModel = new CmdHostNotesModel($ctx);
        $this->ansibleService = new AnsibleService($ctx);
        $this->templateService = new TemplateService($ctx);
        $this->hostLogsService = new hostLogsService($ctx);

        $this->filter = new Filter();
        $this->ctx = $ctx;
    }

    /**
     * Obtiene los detalles de un host.
     *
     * @param array<string, string|int> $command_     * @param array<string, string|int> $command_values Los valores del comando.values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function getHostDetails(array $command_values): array
    {
        $user = $this->ctx->get('User');
        $target_id = $this->filter->varInt($command_values['id']);
        $field = 'getHostDetails';

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        $hostDetails = $this->hostService->getDetails($target_id);

        if (!$hostDetails) {
            return $this->stdReturn(false, "$field: No details");
        }

        $tdata['theme'] = $user->getTheme();
        $tdata['host_details'] = $hostDetails;
        $tdata['host_details']['host_logs'] =  $this->templateService->getTpl(
            'term',
            [
                'term_logs' => '',
                'host_id' => $target_id
            ]
        );
        $hostDetailsTpl = $this->templateService->getTpl('host-details', $tdata);

        $host_data = [
            'cfg' => ['place' => "#left-container"],
            'data' => $hostDetailsTpl,
        ];

        return $this->stdReturn(true, "ok", false, ['host_details' => $host_data]);
    }

    /**
     * Elimina un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function removeHost(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $field = 'removeHost';

        if(!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->removeByID($target_id)) {
            return $this->stdReturn(true, "$field: Host removed $target_id", true);
        }

        return $this->stdReturn(false, "$field: Error removing host");
    }

    /**
     * Actualiza la información de un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function updateHost(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $field = $this->filter->varString($command_values['field']);
        $value = $this->filter->varString($command_values['value']);

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, [$field => $value])) {
            return $this->stdReturn(true, "$field: updated successfully");
        }

        return $this->stdReturn(true, "$field: error updating host");
    }

    /**
     * Activa o desactiva el ping para un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function toggleDisablePing(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varBool($command_values['value']);
        $field = 'disable_ping';

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, ['disable_ping' => $value])) {
            return $this->stdReturn(true, "$field: Ping toggled successfully");
        }

        return $this->stdReturn(false, "$field: error toggling ping");
    }

    /**
     * Establece el método de verificación de puertos para un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function setCheckPorts(array $command_values): array
    {
        // 1 ping 2 TCP/UDP
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varInt($command_values['value']);
        $field = 'check_method';

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, ['check_method' => $value])) {
            return $this->stdReturn(true, "$field: Error updating check ports method");
        } else {
            return $this->stdReturn(false, "$field: Error updating check ports method");
        }
    }

    /**
     * Crea un token para un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function submitHostToken(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $field = 'createHostToken';

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->createHostToken($target_id)) {
            return $this->stdReturn(true, "$field: success $target_id");
        } else {
            return $this->stdReturn(false, "$field: Error creating token");
        }
    }

    /**
     * Agrega un puerto remoto a un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function addRemotePort(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $pnumber = isset($command_values['pnumber']) ?
                $this->filter->varInt($command_values['pnumber']) : null;
        $protocol = isset($command_values['protocol']) ?
                $this->filter->varInt($command_values['protocol']) : null;
        $field ='addRemotePort';

        if ($target_id === null || $target_id <= 0 || $pnumber === null || $protocol === null) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Remote port: Invalid input data',
            ];
        }
        $port_details = [
            'pnumber' => $pnumber,
            'protocol' => $protocol,
        ];

        if ($this->cmdHostModel->addRemoteScanHostPort($target_id, $port_details)) {
            return $this->stdReturn(true, "$field: success $target_id");
        } else {
            return $this->stdReturn(false, "$field: Error adding port");
        }
    }

    /**
     * Elimina un puerto de un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function deleteHostPort(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $field = 'delete_port';

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->deletePort($target_id)) {
            return $this->stdReturn(true, "$field: success $target_id");
        } else {
            return $this->stdReturn(false, "$field: Error adding port");
        }
    }

    /**
     * Actualiza el nombre de un servicio personalizado para un puerto.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function submitCustomServiceName(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varString($command_values['value']);
        $field = 'custom_service';

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updatePort($target_id, ['custom_service' => $value])) {
            return $this->stdReturn(true, "$field: success $target_id");
        } else {
            return $this->stdReturn(false, "$field: Error updating custom service name");
        }
    }

    /**
     * Actualiza el título de un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function submitTitle(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varString($command_values['value']);
        $field = 'title';

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, ['title' => $value])) {
            return $this->stdReturn(true, "$field: change success $target_id", true);
        } else {
            return $this->stdReturn(false, "$field: Error updating title");
        }
    }

    /**
     * Actualiza el hostname de un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function submitHostname(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varDomain($command_values['value']);
        $field = 'hostname';

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, ['hostname' => $value])) {
            return $this->stdReturn(true, "$field: change success $target_id");
        } else {
            return $this->stdReturn(false, "$field: Error updating hostname");
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitOwner(array $command_values): array
    {
        return $this->updateMisc($command_values);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitHostTimeout(array $command_values): array
    {
        return $this->updateMisc($command_values);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitHostCategory(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varString($command_values['value']);
        $field = 'category';

        if (!is_numeric($target_id)) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($target_id === null || $target_id <= 0 || $value === null) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, ['category' => $value])) {
            return $this->stdReturn(true, "$field: update success $target_id");
        } else {
            return $this->stdReturn(false, "$field: Error updating host category");
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitManufacture(array $command_values): array
    {
        return $this->updateMisc('manufacture', $command_values);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitMachineType(array $command_values): array
    {
        return $this->updateMisc('machine_type', $command_values);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitSysAval(array $command_values): array
    {
        return $this->updateMisc('sys_availability', $command_values);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function ackHostLog(array $command_values): array
    {
        return $this->updateMisc('ack', $command_values);
    }

    /**
     * Obtiene las métricas de un host.
     *
     * @param int $target_id El ID del host.
     * @return array<string, string|int> Las métricas del host.
     */
    private function getHostMetrics(int $target_id): array
    {
        $metrics = [];

        // Obtener estadísticas de memoria
        $metrics['mem_info'] = $this->cmdHostModel->getMemoryInfo($target_id);

        // Obtener carga promedio
        $metrics['load_avg'] = $this->cmdHostModel->getLoadAverage($target_id);

        // Obtener estadísticas de I/O
        $metrics['iowait_stats'] = $this->cmdHostModel->getIOWaitStats($target_id);

        // Obtener información de discos
        $metrics['disks_info'] = $this->cmdHostModel->getDisksInfo($target_id);

        return $metrics;
    }

    /**
     *
     * @param string $field
     * @param array<string, int|string> $command_values
     * @return array<string, int|string>
     */
    public function updateMisc(string $field, array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varInt($command_values['value']);

        if ($target_id === null || $target_id <= 0 || $value === null) {
            return $this->stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return $this->stdReturn(true, "$field: updated successfully");
        }

        return $this->stdReturn(false, "$field: updated error");
    }

    /**
     * Obtiene el campo misc de un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function getMisc(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $misc_data = $this->cmdHostModel->getMisc($target_id);

        return $this->stdReturn(true, $misc_data);
    }

    /**
     *
     * @return array<string, string|int>
     */
    public function getAlertHosts(): array
    {
        $lng = $this->ctx->get('lng');
        $field = 'report_alerts';
        $tdata['hosts'] = $this->hostService->getAlertHosts();
        $keysToShow = $this->reportKeysToShow;
        array_push($keysToShow, 'log_msgs');
        $tdata['keysToShow'] = $keysToShow;
        $tdata['table_btn'] = 'clear_alerts';
        $tdata['table_btn_name'] = $lng['L_CLEAR_ALERTS'];

        $alertHostsTpl = $this->templateService->getTpl('hosts-report', $tdata);

        return $this->stdReturn(true, $alertHostsTpl, false, ['command_receive' => $field]);
    }

    /**
     *
     * @return array<string, string|int>
     */
    public function getWarnHosts(): array
    {
        $lng = $this->ctx->get('lng');
        $field = 'report_warns';
        $tdata['hosts'] = $this->hostService->getWarnHosts();
        $keysToShow = $this->reportKeysToShow;
        array_push($keysToShow, 'log_msgs');
        $tdata['keysToShow'] = $keysToShow;
        $tdata['table_btn'] = 'clear_warns';
        $tdata['table_btn_name'] = $lng['L_CLEAR_WARNS'];

        $warnHostsTpl = $this->templateService->getTpl('hosts-report', $tdata);

        return $this->stdReturn(true, $warnHostsTpl, false, ['command_receive' => $field]);
    }

    /**
     *
     * @param int|null $status
     * @return array<string, string|int>
     */
    public function getAgentsHosts(?int $status = null): array {
        $hosts = $this->hostService->getAgentsHosts($status);
        $field = 'report_agents_hosts';
        $tdata['hosts'] = $hosts;
        $keysToShow = $this->reportKeysToShow;
        array_push($keysToShow, 'ansible_enabled', 'agent_version');
        $tdata['keysToShow'] = $keysToShow;

        $agentHostsTpl = $this->templateService->getTpl('hosts-report', $tdata);

        return $this->stdReturn(true, $agentHostsTpl, false, ['command_receive' => $field]);
    }

    /**
     *
     * @param int|null $status
     * @return array<string, string|int>
     */
    public function getAnsibleHosts(?int $status = null): array
    {
        $tdata['hosts'] = $this->hostService->getAnsibleHosts($status);
        $field = 'report_ansible';
        $keysToShow = $this->reportKeysToShow;
        array_push($keysToShow, 'ansible_enabled', 'agent_version');
        $tdata['keysToShow'] = $keysToShow;

        $ansibleHostsTpl = $this->templateService->getTpl('hosts-report', $tdata);

        return $this->stdReturn(true, $ansibleHostsTpl, false, ['command_receive' => $field]);
    }

    /**
     *
     * @return array<string,string|int>
     */
    public function clearAlerts(): array
    {
        $field = 'clearAlerts';
        $ret = $this->cmdHostModel->clearAllAlerts();

        if ($ret) {
            return $this->stdReturn(true, $field . ': success', true);
        } else {
            return $this->stdReturn(false, "$field: failed " . $ret);
        }
    }

    /**
     *
     * @return array<string,string|int>
     */
    public function clearWarns(): array
    {
        $field = 'clearWarns';

        if ($this->cmdHostModel->clearAllWarns()) {
            return $this->stdReturn(true, $field . ': success', true);
        } else {
            return $this->stdReturn(false, "$field: failed");
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function setHighlight(array $command_values): array
    {
        $field = 'setHighlight';
        $target_id = $this->filter->varInt($command_values['id']);
        $value = (empty($command_values['value'])) ? 0 : 1;

        $update['highlight'] = $value;

        if ($this->cmdHostModel->updateByID($target_id, $update)) {
            return $this->stdReturn(true, $field . ': success', true);
        } else {
            return $this->stdReturn(false, "$field: failed");
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function setHostAnsible(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $field = 'setHostAnsible';
        $value = (empty($command_values['value'])) ? 0 : 1;

        $update['ansible_enabled'] = $value;

        if ($this->cmdHostModel->updateByID($target_id, $update)) {
            return $this->stdReturn(true, $field . ': success', true);
        } else {
            return $this->stdReturn(false, "$field: failed ");
        }
    }

   /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function saveNote(array $command_values): array
    {
        $field = 'saveNote';
        $target_id = $this->filter->varInt($command_values['id']);
        $value_command = $this->filter->varUTF8($command_values['value']);

        $content = urldecode($value_command);
        if (str_starts_with($content, ":clear")) {
            $content = '';
        }
        $update['content'] = $content;
        $this->cmdHostNotesModel = new CmdHostNotesModel($this->ctx);
        if ($this->cmdHostNotesModel->updateByID($target_id, $update)) {
            return $this->stdReturn(true, $field . ': success', true);
        } else {
            return $this->stdReturn(false, "$field: failed");
        }
     }
    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function setAlwaysOn(array $command_values): array
    {
        $field = 'always_on';
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varInt($command_values['value']);

        if ($this->cmdHostModel->updateMiscByID($target_id, ['always_on' => $value])) {
            return $this->stdReturn(true, $field . ': success', true);
        } else {
            return $this->stdReturn(false, "$field: failed");
        }
    }


    public function logsReload(array $command_values) : array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $field = 'logs-reload';
        $response = $this->hostLogsService->getLogs($target_id, $command_values);

        return $this->stdReturn(true, $response, false, ['command_receive' => $field]);


    }
    /**
     *
     * @param array<string, string|int> $command_values
     * return array<string, string|int>
     */
    public function handleTabChange(array $command_values): array
    {
        $tabName = $this->filter->varString($command_values['value']);
        $target_id = $this->filter->varInt($command_values['id']);
        $cmd = '';
        $response = '';

        switch($tabName):
            case 'tab3':    # Notes
                $cmd = 'load_notes';
                $response = $this->cmdHostNotesModel->getNotes($target_id);
                break;
            case 'tab9':    # Log
                $cmd = 'logs-reload';
                $response = $this->hostLogsService->getLogs($target_id, $command_values);
                break;
            case 'tab10':   # Metrics
                break;
            case 'tab15':   # Tasks
                break;
            case 'tab20':   # Ansible
                break;
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Unused tab change',
                ];
        endswitch;

        return [
            'command_receive' => $cmd,
            'command_success' => 1,
            'response_msg' => $response,
        ];
    }

    /**
     *
     * @param bool $success
     * @param mixed $msg
     * @param bool $force_reload
     * @param array<string, mixed> $extra_fields
     * @return array<string, string|int>
     */
    private function stdReturn(
        bool $success,
        mixed $msg,
        bool $force_reload = false,
        array $extra_fields = [],
    ): array
    {
        if ($success) {
            $response = [
                'command_success' => 1,
                'response_msg' => $msg,
            ];
        } else {
            $response = [
                'command_error' => 1,
                'command_error_msg' => $msg,
            ];
        }
        if ($force_reload) {
            $response['force_hosts_refresh'] = 1;
        }

        if (!empty($extra_fields)) {
            $response = array_merge($response, $extra_fields);
        }


         return $response;
    }
}
