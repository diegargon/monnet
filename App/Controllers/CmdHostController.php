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

class CmdHostController
{

    private $cmdHostModel;
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->cmdHostModel = new CmdHostModel($ctx);
        $this->ctx = $ctx;
    }

    /**
     * Obtiene los detalles de un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function getHostDetails($command_values) {
        $target_id = $this->filterService->varInt($command_values['id']);

        if (!$target_id) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'ID de host no válido',
            ];
        }

        // Obtener los detalles del host
        $hostDetails = $this->hostModel->getHostDetails($target_id);

        if ($hostDetails) {
            return [
                'command_success' => 1,
                'response_msg' => $hostDetails,
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'No se encontraron detalles para el host',
            ];
        }
    }

    /**
     * Elimina un host.
     *
     * @param array $command_values Los valores del comando.
     * @return array Respuesta en formato JSON.
     */
    public function removeHost($command_values)
    {
        $target_id = $this->filterService->varInt($command_values['id']);

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
        $target_id = $this->filterService->varInt($command_values['id']);
        $field = $this->filterService->varString($command_values['field']);
        $value = $this->filterService->varString($command_values['value']);

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
        $target_id = $this->filterService->varInt($command_values['id']);
        $value = $this->filterService->varBool($command_values['value']);

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
        $target_id = $this->filterService->varInt($command_values['id']);
        $value = $this->filterService->varInt($command_values['value']);

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
        $target_id = $this->filterService->varInt($command_values['id']);

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
        $target_id = $this->filterService->varInt($command_values['id']);
        $port_details = [
            'pnumber' => $this->filterService->varInt($command_values['pnumber']),
            'protocol' => $this->filterService->varInt($command_values['protocol']),
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
        $target_id = $this->filterService->varInt($command_values['id']);

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
        $target_id = $this->filterService->varInt($command_values['id']);
        $value = $this->filterService->varString($command_values['value']);

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
        $target_id = $this->filterService->varInt($command_values['id']);
        $value = $this->filterService->varString($command_values['value']);

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
        $target_id = $this->filterService->varInt($command_values['id']);
        $value = $this->filterService->varDomain($command_values['value']);

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
}
