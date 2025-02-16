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
use App\Services\Filter;
use App\Services\AnsibleService;
use App\Services\TemplateService;
use App\Services\HostFormatter;
use App\Services\HostService;

class CmdHostController
{

    private CmdHostModel $cmdHostModel;
    private CmdHostLogsModel $cmdHostLogsModel;
    private Filter $filter;
    private \AppContext $ctx;
    private $ansibleService;
    private $templateService;
    private $reportKeysToShow = ["id", "display_name", "ip", 'mac', "online"];

    public function __construct(\AppContext $ctx)
    {
        $this->hostService = new HostService($ctx);
        $this->cmdHostLogsModel = new CmdHostLogsModel($ctx);
        $this->hostFormatter = new HostFormatter($ctx);
        $this->cmdHostModel = new CmdHostModel($ctx);
        $this->ansibleService = new AnsibleService($ctx);
        $this->templateService = new TemplateService($ctx);

        $this->filter = new Filter();
        $this->ctx = $ctx;
    }

    /**
     * Obtiene los detalles de un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function getHostDetails(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);

        if (!$target_id) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'ID de host no válido',
            ];
        }

        $hostDetails = $this->hostService->getDetails($target_id);

        if (!$hostDetails) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'No se encontraron detalles para el host',
            ];
        }

        $tdata['host_details'] = $hostDetails;
        $hostDetailsTpl = $this->templateService->getTpl('host-details', $tdata);

        $host_data = [
            'cfg' => ['place' => "#left-container"],
            'data' => $hostDetailsTpl,
        ];

        return [
            'command_success' => 1,
            'host_details' => $host_data,
            'response_msg' => "ok",
        ];

    }

    /**
     * Elimina un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function removeHost(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);

        if ($this->cmdHostModel->remove($target_id)) {
            return [
                'command_success' => 1,
                'response_msg' => 'Host removed: ' . $target_id,
                'force_hosts_refresh' => 1,
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error removing host',
            ];
        }
    }

    /**
     * Actualiza la información de un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function updateHost(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $field = $this->filter->varString($command_values['field']);
        $value = $this->filter->varString($command_values['value']);

        if ($this->cmdHostModel->update($target_id, [$field => $value])) {
            return [
                'command_success' => 1,
                'response_msg' => 'Host updated successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error updating host',
            ];
        }
    }

    /**
     * Activa o desactiva el ping para un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function toggleDisablePing(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varBool($command_values['value']);

        if ($this->cmdHostModel->update($target_id, ['disable_ping' => $value])) {
            return [
                'command_success' => 1,
                'response_msg' => 'Ping toggled successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error toggling ping',
            ];
        }
    }

    /**
     * Establece el método de verificación de puertos para un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function setCheckPorts(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varInt($command_values['value']);

        if ($this->cmdHostModel->update($target_id, ['check_method' => $value])) {
            return [
                'command_success' => 1,
                'response_msg' => 'Check ports method updated successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error updating check ports method',
            ];
        }
    }

    /**
     * Crea un token para un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function submitHostToken(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);

        if ($this->cmdHostModel->createHostToken($target_id)) {
            return [
                'command_success' => 1,
                'response_msg' => 'Token created successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error creating token',
            ];
        }
    }

    /**
     * Agrega un puerto remoto a un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function addRemotePort(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $port_details = [
            'pnumber' => $this->filter->varInt($command_values['pnumber']),
            'protocol' => $this->filter->varInt($command_values['protocol']),
        ];

        if ($this->cmdHostModel->addRemoteScanHostPort($target_id, $port_details)) {
            return [
                'command_success' => 1,
                'response_msg' => 'Port added successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error adding port',
            ];
        }
    }

    /**
     * Elimina un puerto de un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function deleteHostPort(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);

        if ($this->cmdHostModel->deletePort($target_id)) {
            return [
                'command_success' => 1,
                'response_msg' => 'Port deleted successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error deleting port',
            ];
        }
    }

    /**
     * Actualiza el nombre de un servicio personalizado para un puerto.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function submitCustomServiceName(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varString($command_values['value']);

        if ($this->cmdHostModel->updatePort($target_id, ['custom_service' => $value])) {
            return [
                'command_success' => 1,
                'response_msg' => 'Custom service name updated successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error updating custom service name',
            ];
        }
    }

    /**
     * Actualiza el título de un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function submitTitle(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varString($command_values['value']);

        if ($this->cmdHostModel->update($target_id, ['title' => $value])) {
            return [
                'command_success' => 1,
                'response_msg' => 'Title updated successfully',
                'force_hosts_refresh' => 1,
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error updating title',
            ];
        }
    }

    /**
     * Actualiza el hostname de un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function submitHostname(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varDomain($command_values['value']);

        if ($this->cmdHostModel->update($target_id, ['hostname' => $value])) {
            return [
                'command_success' => 1,
                'response_msg' => 'Hostname updated successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error updating hostname',
            ];
        }
    }

    public function ackHostLog(array $command_values) {
        $target_id = $this->filter->varInt($command_values['id']);
        $value = $this->filter->varBool($command_values['value']);

        if ($this->cmdHostLogsModel->update($target_id, ['ack' => $value])) {
            return [
                'command_success' => 1,
                'response_msg' => 'Ack updated successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error updating hostname',
            ];
        }

    }

    /**
     * Obtiene las métricas de un host.
     *
     * @param int $target_id El ID del host.
     * @return array Las métricas del host.
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
     * Actualiza el campo misc de un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function updateMisc(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $misc_data = $this->filter->varArray($command_values['misc']);

        if ($this->cmdHostModel->updateMisc($target_id, $misc_data)) {
            return [
                'command_success' => 1,
                'response_msg' => 'Campo misc actualizado correctamente',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error al actualizar el campo misc',
            ];
        }
    }

    /**
     * Obtiene el campo misc de un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function getMisc(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);

        $misc_data = $this->cmdHostModel->getMisc($target_id);

        return [
            'command_success' => 1,
            'response_msg' => $misc_data,
        ];
    }

    public function getAlertHosts(): array
    {
        $lng = $this->ctx->get('lng');
        $tdata['hosts'] = $this->hostService->getAlertHosts();
        $keysToShow = $this->reportKeysToShow;
        array_push($keysToShow, 'log_msgs');
        $tdata['keysToShow'] = $keysToShow;
        $tdata['table_btn'] = 'clear_alerts';
        $tdata['table_btn_name'] = $lng['L_CLEAR_ALERTS'];

        $alertHostsTpl = $this->templateService->getTpl('hosts-report', $tdata);

        return [
            'command_receive' => 'report_alerts',
            'command_success' => 1,
            'response_msg' => $alertHostsTpl,
        ];
    }

    public function getWarnHosts(): array
    {
        $lng = $this->ctx->get('lng');
        $tdata['hosts'] = $this->hostService->getWarnHosts();
        $keysToShow = $this->reportKeysToShow;
        array_push($keysToShow, 'log_msgs');
        $tdata['keysToShow'] = $keysToShow;
        $tdata['table_btn'] = 'clear_warns';
        $tdata['table_btn_name'] = $lng['L_CLEAR_WARNS'];

        $warnHostsTpl = $this->templateService->getTpl('hosts-report', $tdata);

        return [
            'command_receive' => 'report_warns',
            'command_success' => 1,
            'response_msg' => $warnHostsTpl,
        ];
    }

    public function getAgentsHosts(?int $status = null): array {
        $hosts = $this->hostService->getAgentsHosts($status);
        $tdata['hosts'] = $hosts;
        $keysToShow = $this->reportKeysToShow;
        array_push($keysToShow, 'ansible_enabled', 'agent_version');
        $tdata['keysToShow'] = $keysToShow;

        $agentHostsTpl = $this->templateService->getTpl('hosts-report', $tdata);

        return [
            'command_receive' => 'report_warns',
            'command_success' => 1,
            'response_msg' => $agentHostsTpl,
        ];
    }

    public function getAnsibleHosts(?int $status = null): array
    {
        $tdata['hosts'] = $this->hostService->getAnsibleHosts($status);
        $keysToShow = $this->reportKeysToShow;
        array_push($keysToShow, 'ansible_enabled', 'agent_version');
        $tdata['keysToShow'] = $keysToShow;

        $warnHostsTpl = $this->templateService->getTpl('hosts-report', $tdata);

        return [
            'command_receive' => 'report_ansible',
            'command_success' => 1,
            'response_msg' => $warnHostsTpl,
        ];
    }

    public function handleTabChange(array $command_values)
    {
        $tabName = $this->filter->varString($command_values['value']);

        switch($tabName):
            case 'tab3':    # Notes
                return [];
            case 'tab9':    # Log
                return [];
            case 'tab10':   # Metrics
                return [];
            case 'tab15':   # Tasks
                return [];
            case 'tab20':   # Ansible
                return [];
            default:
                return [];
        endswitch;
    }
}
