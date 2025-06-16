<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
 * CommandRouter       Recibe el comando y los valores.
 * CommandRouter       Redirige la solicitud al método correspondiente en HostController.
 * CmdHostController   Valida los datos de entrada y llama a HostService para obtener los datos.
 * HostService         Se comunica con HostModel para obtener los datos y realiza cualquier lógica de negocio necesaria.
 * HostService         Devuelve los datos a HostController.
 * CmdHostController   Formatea los datos (opcionalmente usando HostFormatted) y prepara la respuesta.
 * CmdHostController   Devuelve la respuesta a CommandRouter.
 * CommandRouter       Devuelve la respuesta final al cliente.
 */

namespace App\Controllers;

use App\Core\AppContext;
use App\Core\DBManager;

use App\Services\Filter;
use App\Services\AnsibleService;
use App\Services\HostFormatter;
use App\Services\HostService;
use App\Services\LogHostsService;
use App\Services\LogSystemService;
use App\Services\HostViewBuilder;
use App\Services\HostMetricsService;
use App\Services\UserService;
use App\Services\DateTimeService;
use App\Services\CategoriesService;

use App\Models\CmdHostModel;
use App\Models\CmdHostNotesModel;

use App\Helpers\Response;
use App\Utils\NetUtils;

use App\Constants\LogLevel;

class CmdHostController
{
    private CmdHostModel $cmdHostModel;
    private CmdHostNotesModel $cmdHostNotesModel;

    private LogHostsService $logHostsService;
    private HostMetricsService $hostMetricsService;
    private AnsibleService $ansibleService;
    private HostService $hostService;

    private HostViewBuilder $hostViewBuilder;
    private HostFormatter $hostFormatter;

    private AppContext $ctx;
    private DBManager $db;
    private LogSystemService $logSys;
    private CategoriesService $categoriesService;

    public function __construct(AppContext $ctx)
    {
        $this->db = $ctx->get(DBManager::class);

        $this->hostService = new HostService($ctx);
        $this->ansibleService = new AnsibleService($ctx);
        $this->logHostsService = new LogHostsService($ctx);
        $this->hostMetricsService = new hostMetricsService($ctx);

        $this->cmdHostNotesModel = new CmdHostNotesModel($this->db);
        $this->cmdHostModel = new CmdHostModel($this->db);

        $this->hostFormatter = new HostFormatter($ctx);
        $this->hostViewBuilder = new HostViewBuilder($ctx);

        $this->ctx = $ctx;
        $this->logSys = new LogSystemService($ctx);
        $this->categoriesService = new CategoriesService($ctx);
    }

    /**
     * Obtiene los detalles de un host.
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function getHostDetails(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $field = 'getHostDetails';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        $hostDetails = $this->hostService->getDetails($target_id);

        if (!$hostDetails) {
            return Response::stdReturn(false, "$field: No details");
        }

        $hostDetailsTpl = $this->hostViewBuilder->hostDetails($target_id, $hostDetails);

        $host_data = [
            'cfg' => ['place' => "#left-container"],
            'data' => $hostDetailsTpl,
        ];

        return Response::stdReturn(true, "ok", false, ['host_details' => $host_data]);
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, mixed>
     */
    public function reloadStatsView(string $command, array $command_values): array
    {
        # TODO: Actually reload send all html/js again
        $target_id = Filter::varInt($command_values['id']);
        $field = 'reloadStatsView';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        $hostDetails = $this->hostService->getDetailsStats($target_id);

        if (!$hostDetails) {
            return Response::stdReturn(false, "$field: No details");
        }

        $hostDetailsTpl = $this->hostViewBuilder->buildStats($hostDetails);
        $extra_fields = [
            'command_receive' => $command,
            'host_details' => $hostDetailsTpl
            ];

        return Response::stdReturn(true, "ok", false, $extra_fields);
    }

    /**
     * Elimina un host.
     *
     * @param string $command
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function removeHost(string $command, array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $field = 'removeHost';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->removeByID($target_id)) {
            $this->logSys->notice('Deleted host id: ' . $target_id);
            return Response::stdReturn(true, "$field: Host removed $target_id", true, ['command_receive' => $command]);
        }

        return Response::stdReturn(false, "$field: Error removing host");
    }

    /**
     * Actualiza la información de un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, mixed>
     */
    public function updateHost(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $field = Filter::varString($command_values['field']);
        $value = Filter::varString($command_values['value']);

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: updated successfully");
        }

        return Response::stdReturn(true, "$field: error updating host");
    }

    /**
     * Activa o desactiva el ping para un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, string|int> Respuesta en formato JSON.
     */
    public function toggleDisablePing(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'disable_ping';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: Ping toggled successfully");
        }

        return Response::stdReturn(false, "$field: error toggling ping");
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
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'check_method';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, ['check_method' => $value])) {
            return Response::stdReturn(true, "$field: Updated check ports method ($value)");
        } else {
            return Response::stdReturn(false, "$field: Error updating check ports method ($value)");
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
        $hid = Filter::varInt($command_values['id']);
        $field = 'createHostToken';

        if (!is_numeric($hid) && $hid <= 0) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        $token = $this->hostService->createToken($hid);
        if ($token) {
            return Response::stdReturn(true, "Created token for id $hid", false, ['token' => $token]);
        } else {
            return Response::stdReturn(false, "Create token for id $hid fail");
        }
    }

    /**
     * Agrega un puerto remoto a un host.
     *
     * @param array<string, string|int> $command_values Los valores del comando.
     * @return array<string, mixed>
     */
    public function addRemotePort(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $pnumber = isset($command_values['value']) ?
                Filter::varInt($command_values['value']) : null;
        $protocol = isset($command_values['protocol']) ?
                Filter::varInt($command_values['protocol']) : null;
        $field = 'addRemotePort';

        if ($target_id === null || $target_id <= 0 || $pnumber === null || $protocol === null) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Remote port: Invalid input data',
            ];
        }
        $port_details = [
            'scan_type' => 1, # Remote
            'pnumber' => $pnumber,
            'protocol' => $protocol,
            'last_check' => DateTimeService::dateNow()
        ];

        if ($this->cmdHostModel->addRemoteScanHostPort($target_id, $port_details)) {
            return Response::stdReturn(true, "$field: success $target_id");
        } else {
            return Response::stdReturn(false, "$field: Error adding port");
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
        $port_id = Filter::varInt($command_values['id']);
        $field = 'delete_port';

        if (!is_numeric($port_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->deletePort($port_id)) {
            return Response::stdReturn(true, "$field: success $port_id", false, ['port_id' => $port_id]);
        } else {
            return Response::stdReturn(false, "$field: Error adding port $port_id");
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
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varString($command_values['value']);
        $field = 'custom_service';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updatePort($target_id, ['custom_service' => $value])) {
            return Response::stdReturn(true, "$field: success $target_id");
        } else {
            return Response::stdReturn(false, "$field: Error updating custom service name");
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
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varString($command_values['value']);
        $field = 'title';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: change success $target_id", true);
        } else {
            return Response::stdReturn(false, "$field: Error updating title");
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function setHostDisable(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'disable';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: change success $target_id", true);
        } else {
            return Response::stdReturn(false, "$field: Error updating title");
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
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varDomain($command_values['value']);
        $field = 'hostname';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, ['hostname' => $value])) {
            return Response::stdReturn(true, "$field: change success $target_id");
        } else {
            return Response::stdReturn(false, "$field: Error updating hostname");
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitOwner(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varString($command_values['value']);
        $field = 'owner';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitHostTimeout(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varFloat($command_values['value']);
        $field = 'timeout';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }

    /**
     * Trigger when change host category
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitChangeHostCategory(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varString($command_values['value']);
        $field = 'category';

        if (
            !is_numeric($target_id) ||
            $value === false ||
            $target_id <= 0
        ) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($target_id, ['category' => $value])) {
            return Response::stdReturn(true, "$field: update success $target_id");
        } else {
            return Response::stdReturn(false, "$field: Error updating host category");
        }
    }

    /**
     * Create new host category
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitNewHostsCat(array $command_values): array
    {
        $value = Filter::varString($command_values['value']);
        $response = $this->categoriesService->create(1, $value);

        if ($response['success'] == 1) {
            return Response::stdReturn(true, $response['msg']);
        }

        return Response::stdReturn(false, $response['msg']);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */

    public function powerOn(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $host = $this->hostService->getHostById($target_id);

        if (!empty($host) && !empty($host['mac'])) {
            # TODO Move to gateway?
            NetUtils::sendWOL($host['mac']);
            return Response::stdReturn(true, 'WOL: ' . $target_id);
        } else {
            $err_msg = ($host['ip'] ?? '') . ' not mac address';
            $this->logSys->warning($err_msg);
            return Response::stdReturn(false, $err_msg);
        }
    }
    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitManufacture(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varString($command_values['value']);
        $field = 'manufacture';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitMachineType(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'machine_type';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitSysAval(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'sys_availability';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitLinked(array $command_values): array
    {
        $host_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'linked';

        if (!is_numeric($host_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        # TODO check if is valid linked host
        if ($this->cmdHostModel->updateByID($host_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitOS(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'os';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitOSFamily(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'os_family';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitOSVersion(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'os_version';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitSystemRol(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'system_rol';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }
    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitAccessType(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $field = 'access_type';

        if (!is_numeric($target_id)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }
    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitAccessLink(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varUrl($command_values['value'], 255);
        $field = 'access_link';

        if (!is_numeric($target_id) || empty($value)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }
        if ($this->cmdHostModel->updateMiscByID($target_id, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
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
        $tdata['table_btn'] = 'clear_alerts';
        $tdata['table_btn_name'] = $lng['L_CLEAR_ALERTS'];

        $alertHostsTpl = $this->hostViewBuilder->buildHostReport($tdata, ['log_msgs']);

        return Response::stdReturn(true, $alertHostsTpl, false, ['command_receive' => $field]);
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
        $tdata['table_btn'] = 'clear_warns';
        $tdata['table_btn_name'] = $lng['L_CLEAR_WARNS'];

        $warnHostsTpl = $this->hostViewBuilder->buildHostReport($tdata, ['log_msgs']);

        return Response::stdReturn(true, $warnHostsTpl, false, ['command_receive' => $field]);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function clearHostAlarms(array $command_values): array
    {
        $hid = Filter::varInt($command_values['id']);
        $user = $this->ctx->get(UserService::class);
        $username = $user->getUsername();
        $lng = $this->ctx->get('lng');

        $values = [
            'alert' => 0,
            'warn' => 0,
            'ansible_fail' => 0,
        ];
        $this->cmdHostModel->updateByID($hid, $values);

        $this->logHostsService->logHost(
            LogLevel::NOTICE,
            $hid,
            $lng['L_CLEAR_ALARMS_BITS_BY'] . ': ' . $username
        );
        return Response::stdReturn(true, 'ok', true);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function setHostAlarms(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);
        $msg = $this->cmdHostModel->updateMiscByID($target_id, ['disable_alarms' => $value]);

        return Response::stdReturn(true, $msg);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function toggleMailAlarms(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);

        $msg = $this->cmdHostModel->updateMiscByID($target_id, ['email_alarms' => $value]);

        return Response::stdReturn(true, $msg);
    }
    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function toggleAlarmType(string $command, array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);

        $msg = $this->cmdHostModel->updateMiscByID($target_id, [$command => $value]);

        return Response::stdReturn(true, $msg);
    }
    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function toggleEmailAlarmType(string $command, array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);

        $msg = $this->cmdHostModel->updateMiscByID($target_id, [$command => $value]);

        return Response::stdReturn(true, $msg);
    }
    /**
     *
     * @param array<string, mixed> $command_values
     * @return array<string, string|int>
     */
    public function setEmailList(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $value = $command_values['value'];

        $string = implode(",", $value);
        $msg = $this->cmdHostModel->updateMiscByID($target_id, ['email_list' => $string]);

        return Response::stdReturn(true, $msg);
    }

    /**
     *
     * @param int|null $status
     * @return array<string, string|int>
     */
    public function getAgentsHosts(?int $status = null): array
    {
        $hosts = $this->hostService->getAgentsHosts($status);
        $field = 'report_agents_hosts';
        $tdata['hosts'] = $hosts;
        $agentHostsTpl = $this->hostViewBuilder->buildHostReport($tdata, ['ansible_enabled', 'agent_version']);

        return Response::stdReturn(true, $agentHostsTpl, false, ['command_receive' => $field]);
    }

    /**
     *
     * @param int|null $status
     * @return array<string, mixed>
     */
    public function getAnsibleHosts(?int $status = null): array
    {
        $tdata['hosts'] = $this->hostService->getAnsibleHosts($status);
        $field = 'report_ansible';
        $ansibleHostsTpl = $this->hostViewBuilder->buildHostReport($tdata, ['ansible_enabled', 'agent_version']);

        return Response::stdReturn(true, $ansibleHostsTpl, false, ['command_receive' => $field]);
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
            return Response::stdReturn(true, $field . ': success', true);
        } else {
            return Response::stdReturn(false, "$field: failed " . $ret);
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
            return Response::stdReturn(true, $field . ': success', true);
        } else {
            return Response::stdReturn(false, "$field: failed");
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
        $target_id = Filter::varInt($command_values['id']);
        $value = (empty($command_values['value'])) ? 0 : 1;

        $update['highlight'] = $value;

        if ($this->cmdHostModel->updateByID($target_id, $update)) {
            return Response::stdReturn(true, $field . ': success', true);
        } else {
            return Response::stdReturn(false, "$field: failed");
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function setHostAnsible(array $command_values): array
    {
        $target_id = Filter::varInt($command_values['id']);
        $field = 'setHostAnsible';
        $value = (empty($command_values['value'])) ? 0 : 1;

        $update['ansible_enabled'] = $value;

        if ($this->cmdHostModel->updateByID($target_id, $update)) {
            return Response::stdReturn(true, $field . ': success', true);
        } else {
            return Response::stdReturn(false, "$field: failed ");
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitHost(array $command_values): array
    {
        $host = [];
        $ip = $domain = false;
        $lng = $this->ctx->get('lng');

        $ip = Filter::varIP($command_values['value']);
        if (empty($ip)) {
            $domain = Filter::varDomain($command_values['value']);
        } else {
            $host['ip'] = $ip;
        }

        if (empty($ip) && !empty($domain)) {
            $host['ip'] = gethostbyname($domain);
            $host['hostname'] = $domain;
        }

        if (!empty($host['ip'])) {
            $result = $this->hostService->add($host);
            if (isset($result['status']) && $result['status'] === 'success') {
                return Response::stdReturn(true, $lng['L_OK'], true);
            } elseif (isset($result['status']) && $result['status'] === 'error') {
                $msg = $result['error_msg'] ?? $lng['L_ERR_NOT_NET_CONTAINER'];
                return Response::stdReturn(false, $msg);
            } else {
                return Response::stdReturn(false, $lng['L_ERR_NOT_NET_CONTAINER']);
            }
        } else {
            return Response::stdReturn(false, $lng['L_ERR_NOT_INTERNET_IP'] . ($host['ip'] ?? ''));
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
        $target_id = Filter::varInt($command_values['id']);
        $value_command = Filter::varUTF8($command_values['value']);

        if ($value_command) {
            $content = urldecode($value_command);
            if (strpos($content, ":clear") === 0) {
                $content = '';
            }
            $update['content'] = $content;
            $this->cmdHostNotesModel = new CmdHostNotesModel($this->db);
            if ($this->cmdHostNotesModel->updateByID($target_id, $update)) {
                return Response::stdReturn(true, $field . ': success', true);
            }
        }
        return Response::stdReturn(false, "$field: failed");
    }
    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function setAlwaysOn(array $command_values): array
    {
        $field = 'always_on';
        $target_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);

        if ($this->cmdHostModel->updateMiscByID($target_id, ['always_on' => $value])) {
            return Response::stdReturn(true, $field . ': success', true);
        } else {
            return Response::stdReturn(false, "$field: failed");
        }
    }
    public function setLinkable(array $command_values): array
    {
        $field = 'linkable';
        $host_id = Filter::varInt($command_values['id']);
        $value = Filter::varInt($command_values['value']);


        if ($value != 1) {
            $value = 0;
        }

        if ($this->cmdHostModel->updateByID($host_id, [$field => $value])) {
            return Response::stdReturn(true, $field . ': success', true);
        } else {
            return Response::stdReturn(false, "$field: failed");
        }
    }

    /**
     * Unused yet
     *
     * @param string $command
     * @param array<string, mixed> $command_values
     * @param string $field
     * @param string $filterType
     *
     * @return array<string, mixed>
     */
    public function updateHostField(
        string $command,
        array $command_values,
        string $field,
        string $filterType = 'int'
    ): array {
        $hid = Filter::varInt($command_values['id']);

        switch ($filterType) {
            case 'int':
                $value = Filter::varInt($command_values['value']);
                break;
            case 'string':
                $value = Filter::varString($command_values['value']);
                break;
            default:
                return Response::stdReturn(false, "$field: Invalid filter type");
        }

        if (!is_numeric($hid)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateByID($hid, [$field => $value])) {
            return Response::stdReturn(true, "$field: change success $hid", true);
        } else {
            return Response::stdReturn(false, "$field: Error updating");
        }
    }


    /**
     * UNUSED YET
     * Updates a misc field
     * $this->submitField($command_values, 'access_link', [$this->filter, 'varInt'])
     *
     * @param array $command_values Input values containing 'id' and 'value'.
     * @param string $field The field name to update.
     * @param callable $filter_function A filtering function for the value.
     *
     * @return array Standard response array.
     */
    /*
    public function submitMiscField(array $command_values, string $field, callable $filter_function): array
    {
        $hid = Filter::varInt($command_values['id']);
        $value = $filter_function($command_values['value']);

        if (!is_numeric($hid)) {
            return Response::stdReturn(false, "$field: Invalid input data");
        }

        if ($this->cmdHostModel->updateMiscByID($hid, [$field => $value])) {
            return Response::stdReturn(true, "$field: successfully");
        }

        return Response::stdReturn(false, "$field: error");
    }
    */
    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, mixed>
     */
    public function handleTabChange(array $command_values): array
    {
        $tabName = Filter::varString($command_values['value']);
        $target_id = Filter::varInt($command_values['id']);
        $extra = [];
        $cmd = 'changeHDTab';

        switch ($tabName) :
            case 'tab3':    # Notes
                $cmd_value = 'load_notes';
                $response['notes'] = $this->cmdHostNotesModel->getNotes($target_id);
                $response['bitacora'] = $this->logHostsService->formatBitacoraRows($target_id);
                break;
            case 'tab9':    # Log
                $cmd_value = 'logs-reload';
                $response = $this->logHostsService->getLogs($target_id, $command_values);
                break;
            case 'tab10':   # Metrics
                $cmd_value =  'tab10';
                $response = $this->hostMetricsService->getMetricsGraph($target_id);
                break;
            case 'tab15':   # Tasks
                $cmd_value = 'tab15';
                $response = $this->ansibleService->getHostTasks($target_id);
                break;
            case 'tab20':   # Ansible
                $cmd_value = 'tab20';
                $response = $this->ansibleService->getAnsibleTabDetails($target_id);
                break;
            default:
                return Response::stdReturn(false, 'Unused tab change');
        endswitch;

        $extra['command_receive'] = $cmd;

        if (isset($cmd_value)) {
            $extra['command_value'] = $cmd_value;
        }

        return Response::stdReturn(true, $response, false, $extra);
    }

    /**
     *
     * @param array<string, string|int> $cmd_vals
     * @return array<string, string|int>
     */
    public function saveAgentConfig(array $cmd_vals): array
    {
        $hid = Filter::varInt($cmd_vals['id']);
        $data['agent_log_level'] = Filter::varStrict($cmd_vals['agent_log_level']);
        $data['mem_alert_threshold'] = Filter::varInt($cmd_vals['mem_alert_threshold'], 100);
        $data['mem_warn_threshold'] = Filter::varInt($cmd_vals['mem_warn_threshold'], 100);
        $data['disks_alert_threshold'] = Filter::varInt($cmd_vals['disks_alert_threshold'], 100);
        $data['disks_warn_threshold'] = Filter::varInt($cmd_vals['disks_warn_threshold'], 100);

        $wrongKeys = '';
        foreach ($data as $kval => $vval) {
            empty($vval) ? $wrongKeys .= $kval . ', ' : '';
        }
        if (!empty($wrongKeys)) {
            return Response::stdReturn(false, 'Wrong fields: '. $wrongKeys);
        }

        if ($this->hostService->updateAgentConfig($hid, $data)) {
            return Response::stdReturn(true, 'Update agent config success');
        }

        return Response::stdReturn(false, 'Nothing to update'. $wrongKeys);
    }

    public function updateHostConfig(array $command_values): array
    {
        $hid = isset($command_values['host_id']) ? Filter::varInt($command_values['host_id']) : (isset($command_values['id']) ? Filter::varInt($command_values['id']) : null);
        if (!$hid) {
            return Response::stdReturn(false, 'Invalid host id');
        }

        // Main Row Fields
        $mainFields = [
            'highlight', 'ansible_enabled', 'linkable', 'disable',
            'title', 'hostname', 'category', 'linked', 'token'
        ];
        // Misc Fields
        $miscFields = [
            'owner', 'access_link', 'access_link_type', 'machine_type',
            'os_family', 'os', 'os_version', 'system_rol', 'manufacture',
            'sys_availability', 'always_on', 'compliant', 'bastion',
            'location',
        ];

        $mainData = [];
        $miscData = [];
        $validFields = array_merge($mainFields, $miscFields);

        foreach ($command_values as $key => $value) {
            if ($key === 'host_id' || $key === 'id') {
                continue;
            }
            if (!in_array($key, $validFields, true)) {
                return Response::stdReturn(false, "Invalid field: $key");
            }
            if (in_array($key, $mainFields, true)) {
                $mainData[$key] = $value;
            }
            if (in_array($key, $miscFields, true)) {
                $miscData[$key] = $value;
            }
        }

        // Normaliza valores booleanos
        foreach ([
            'highlight', 'ansible_enabled', 'linkable', 'disable', 'always_on', 'compliant', 'bastion'
        ] as $boolField) {
            if (isset($mainData[$boolField])) {
                $mainData[$boolField] = (int)$mainData[$boolField];
            }
        }

        $updateData = $mainData;
        if (!empty($miscData)) {
            $updateData['misc'] = $miscData;
        }

        $result = $this->hostService->updateHost($hid, $updateData);

        if (isset($result['success']) && $result['success']) {
            return Response::stdReturn(true, $result['msg']);
        } elseif (isset($result['error_msg'])) {
            return Response::stdReturn(false, $result['error_msg']);
        } else {
            return Response::stdReturn(false, 'Error updating host config');
        }
    }
}
