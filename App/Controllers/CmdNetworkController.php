<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Services\HostService;
use App\Services\Filter;
use App\Services\TemplateService;
use App\Services\NetworksService;
use App\Helpers\Response;

class CmdNetworkController
{
    private \AppContext $ctx;
    private TemplateService $templateService;
    private HostService $hostService;
    private NetworksService $networksService;

    public function __construct(\AppContext $ctx)
    {
        $this->templateService = new TemplateService($ctx);
        $this->networksService = new NetworksService($ctx);
        $this->ctx = $ctx;
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function manageNetworks(string $command, array $command_values): array
    {
        $action = Filter::varString($command_values['action']);
        $network_id = Filter::varInt($command_values['id']);

        if (isset($command_values['value'])) {
            $value_command = Filter::varJson($command_values['value']);
        } else {
            $value_command = '';
        }

        if (!empty($action) && is_numeric($network_id)) :
            if ($action === 'remove') {
                $this->networksService->removeNetwork($network_id);
                return Response::stdReturn(
                        true,
                        'Network removed successful',
                        true,
                        ['nid' => $network_id, 'action' => $action]
                    );
                // Update existing host to default 1
                // TODO Uncomment and test
                #if (!isset($this->hostService)) {
                #    $this->hostService->switchHostsNetwork($network_id, 1);
                #}
                //TODO: remove host on that network?
            } elseif ($action === 'update' || $action === 'add') {
                $decodedJson = json_decode((string) $value_command, true);

                if ($decodedJson === null) {
                    return Response::stdReturn(
                            false, 'JSON Invalid',
                            false,
                            ['nid' => $network_id, 'action' => $action]
                        );
                }
                $val_net_data = $this->validateNetData($command_values['action'], $decodedJson);

                if (!empty($val_net_data['error'])) {
                    return Response::stdReturn(
                            false,
                            $val_net_data['error_msg'],
                            false,
                            ['nid' => $network_id, 'action' => $action]
                        );
                }

                if ($action === 'add') {
                    $this->networksService->addNetwork($val_net_data);
                    return Response::stdReturn(
                            true,
                            'Network added successful, reopen to check',
                            true,
                            ['action' => $action]
                        );
                }
                if ($action === 'update') {
                    $this->networksService->updateNetwork($network_id, $val_net_data);
                    return Response::stdReturn(true,
                            'Network updated succesful',
                            true,
                            ['nid' => $network_id, 'action' => $action]
                        );
                }
            }
        endif;

        $f_networks = $this->networksService->getNetworks();
        foreach ($f_networks as $nid => $network) :
            list($ip, $cidr) = explode('/', $network['network']);
            $f_networks[$nid]['ip'] = $ip;
            $f_networks[$nid]['cidr'] = $cidr;
        endforeach;

        $tdata = [];

        $tdata['networks'] = $f_networks;
        $tdata['networks_table'] = $this->templateService->getTpl('networks-table', $tdata);

        $extra = [
            'command_receive' => $command,
            'action' => 'mgmt',
            'mgmt_networks' => [
                'cfg' => ['place' => '#left-container'],
                'data' => $this->templateService->getTpl('mgmt-networks', $tdata)
            ]
        ];
        return Response::stdReturn(true, 'ok', false, $extra);
    }

    /**
     *
     * @return array<string, string|int>
     */
    public function requestPoolIPs(): array
    {
        $lng = $this->ctx->get('lng');

        $tdata['networks'] = $this->networksService->getPoolIPs(2) ?? [];

        if (empty($tdata['networks'])) :
            $tdata['status_msg'] = $lng['L_NO_POOLS'];
        endif;

        $extra = [
            'command_receive' => 'requestPool',
            'pool' => [
                'cfg' => ['place' => '#left-container'],
                'data' => $this->templateService->getTpl('pool', $tdata)
            ]
        ];

        return Response::stdReturn(true, 'ok', false, $extra);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitPoolReserver(array $command_values): array
    {
        $hosts = $this->ctx->get('Hosts');
        $user = $this->ctx->get('User');
        $username = $user->getUsername();

        $network_id = Filter::varInt($command_values['id']);
        $value_command = Filter::varIP($command_values['value']);

        $reserved_host = [
            'title' => $username. 'Reserved',
            'ip' => $value_command,
            'network' => $network_id
        ];
        if ($hosts->addHost($reserved_host)) {
            return Response::stdReturn(true, 'Reserved', true);
        }

        return Response::stdReturn(false, 'Add reserved fail');
    }

    /**
     *
     * @param string $action
     * @param array<string, string|int> $net_values
     * @return array<string, string|int>
     */
    private function validateNetData(string $action, array $net_values): array
    {
        $lng = $this->ctx->get('lng');
        $new_network = [];
        $data['error_msg'] = '';

        foreach ($net_values as $key => $dJson) {
            ($key == 'networkVLAN') ? $key = 'vlan' : null;
            ($key == 'networkScan') ? $key = 'scan' : null;
            ($key == 'networkName') ? $key = 'name' : null;
            ($key == 'networkDisable') ? $key = 'disable' : null;
            ($key == 'networkPool') ? $key = 'pool' : null;
            ($key == 'networkWeight') ? $key = 'weight' : null;
            ($key == 'networkOnlyOnline') ? $key = 'only_online' : null;
            $new_network[$key] = trim($dJson);
        }

        if ($new_network['networkCIDR'] == 0 && $new_network['network'] != '0.0.0.0') {
            $data['error'] = 1;
            $data['error_msg'] .= $lng['L_MASK'] .
                ' ' . $new_network['networkCIDR'] .
                ' ' . $lng['L_NOT_ALLOWED'] . '<br/>';
            return $data;
        }

        $network_plus_cidr = $new_network['network'] . '/' . $new_network['networkCIDR'];
        unset($new_network['networkCIDR']);
        $new_network['network'] = $network_plus_cidr;

        if (!Filter::varNetwork($network_plus_cidr)) :
            $data['error'] = 1;
            $data['error_msg'] .= $lng['L_NETWORK'] . ' ' . $lng['L_INVALID'] . '<br/>';
        endif;
        if (!is_numeric($new_network['vlan'])) :
            $data['error'] = 1;
            $data['error_msg'] .= 'VLAN ' . "{$lng['L_MUST_BE']} {$lng['L_NUMERIC']}<br/>";
        endif;
        if (!is_numeric($new_network['scan'])) :
            $data['error'] = 1;
            $data['error_msg'] .= 'Scan ' . "{$lng['L_MUST_BE']} {$lng['L_NUMERIC']}<br/>";
        endif;

        $networks_list = $this->networksService->getNetworks();
        foreach ($networks_list as $net) {
            if ($net['name'] == $new_network['name']) {
                if (
                    $action !== 'update' ||
                    ((int)$net['id'] !== (int)$new_network['id'])
                ) :
                    $data['error'] = 1;
                    $data['error_msg'] .= 'Name must be unique<br/>';
                endif;
            }
            if ($net['network'] == $network_plus_cidr) {
                if (
                    $action !== 'update' ||
                    ((int)$net['id'] !== (int)$new_network['id'])
                ) :
                    $data['error'] = 1;
                    $data['error_msg'] .= 'Network must be unique<br/>';
                endif;
            }
        }
        if (isset($data['error'])) {
            return $data;
        }

        if (
            (strpos($new_network['network'], "0") === 0)||
            !$this->networksService->isLocal($new_network['network'])
        ) :
            $new_network['vlan'] = 0;
            $new_network['scan'] = 0;
        endif;

        return $new_network;
    }
}
