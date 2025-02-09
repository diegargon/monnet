<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Models\CmdHostModel;
use App\Services\Filter;
use App\Services\LogService;
use App\Services\AnsibleService;
use App\Services\TemplateService;

class CmdHostController
{

    private CmdHostModel $cmdHostModel;
    private Filter $filter;
    private \AppContext $ctx;

    private $logService;
    private $ansibleService;
    private $templateService;

    public function __construct(\AppContext $ctx)
    {
        $this->cmdHostModel = new CmdHostModel($ctx);
        $this->logService = new LogService($ctx);
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
    public function getHostDetails($command_values) {
        $target_id = $this->filter->varInt($command_values['id']);

        if (!$target_id) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'ID de host no válido',
            ];
        }

        // Obtener los detalles del host
        $hostDetails = $this->cmdHostModel->getHostDetails($target_id);

        if (!$hostDetails) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'No se encontraron detalles para el host',
            ];
        }

        // Obtener puertos remotos
        //$hostDetails['remote_ports'] = $this->cmdHostModel->getRemotePorts($target_id);

        // Obtener logs del host
        //$hostDetails['logs'] = $this->logService->getHostLogs($target_id);

        // Obtener métricas del host
        $hostDetails['metrics'] = $this->getHostMetrics($target_id);

        // Obtener detalles de Ansible (si está habilitado)
        if ($hostDetails['ansible_enabled']) {
            $hostDetails['ansible_reports'] = $this->ansibleService->getAnsibleReports($target_id);
        }

        $tpl = $this->templateService->getTpl('host-details', [
            'hostDetails' => $hostDetails,
            'remotePorts' => $hostDetails['remote_ports'],
            'logs' => $hostDetails['logs'],
            'metrics' => $hostDetails['metrics'],
            'ansibleReports' => $hostDetails['ansible_reports'],
        ]);

        return [
            'command_success' => 1,
            'response_msg' => $tpl
        ];

    }

    /**
     * Elimina un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function removeHost($command_values)
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
    public function updateHost($command_values)
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
    public function toggleDisablePing($command_values)
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
    public function setCheckPorts($command_values)
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
    public function submitHostToken($command_values)
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
    public function addRemotePort($command_values)
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
    public function deleteHostPort($command_values)
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
    public function submitCustomServiceName($command_values)
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
    public function submitTitle($command_values)
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
    public function submitHostname($command_values)
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

    /**
     * Obtiene las métricas de un host.
     *
     * @param int $target_id El ID del host.
     * @return array Las métricas del host.
     */
    private function getHostMetrics($target_id) {
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
    public function updateMisc($command_values) {
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
    public function getMisc($command_values) {
        $target_id = $this->filter->varInt($command_values['id']);
        
        $misc_data = $this->cmdHostModel->getMisc($target_id);

        return [
            'command_success' => 1,
            'response_msg' => $misc_data,
        ];
    }
}
