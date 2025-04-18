<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Services\Filter;
use App\Services\TemplateService;
use App\Helpers\Response;

class CmdNetworkController
{
    private Filter $filter;
    private \AppContext $ctx;
    private TemplateService $templateService;

    public function __construct(\AppContext $ctx)
    {
        $this->templateService = new TemplateService($ctx);
        $this->filter = new Filter();
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
        $action = $this->filter->varString($command_values['action']);
        $target_id = $this->filter->varInt($command_values['id']);

        if (isset($command_values['value'])) {
            $value_command = $this->filter->varJson($command_values['value']);
        } else {
            $value_command = '';
        }

        $networks = $this->ctx->get('Networks');

        if (!empty($action) && is_numeric($target_id)) :
            if ($action === 'remove') {
                $networks->removeNetwork($target_id);
                //TODO: remove host on that network?
            } elseif ($action === 'update' || $action === 'add') {
                $decodedJson = json_decode((string) $value_command, true);

                if ($decodedJson === null) {
                    return Response::stdReturn(false, 'JSON Invalid');
                }
                $val_net_data = $this->validateNetData($command_values['action'], $decodedJson);

                if (!empty($val_net_data['error'])) {
                    return Response::stdReturn(false, $val_net_data['error_msg']);
                }

                if ($action === 'add') {
                    $this->ctx->get('Networks')->addNetwork($val_net_data);
                    return Response::stdReturn(true, 'add ok', true);
                }
                if ($action === 'update') {
                    $this->ctx->get('Networks')->updateNetwork($target_id, $val_net_data);
                    return Response::stdReturn(true, 'update ok', true);
                }
            }
        endif;

        $f_networks = $networks->getNetworks();
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
        $networks = $this->ctx->get('Networks');
        $lng = $this->ctx->get('lng');
        $tdata['networks'] = $networks->getPoolIPs(2) ?? [];

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

        $target_id = $this->filter->varInt($command_values['id']);
        $value_command = $this->filter->varIP($command_values['value']);

        $reserved_host = [
            'title' => 'UserReserved',
            'ip' => $value_command,
            'network' => $target_id
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

        if (!$this->filter->varNetwork($network_plus_cidr)) :
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

        $networks_list = $this->ctx->get('Networks')->getNetworks();
        foreach ($networks_list as $net) {
            if ($net['name'] == $new_network['name']) {
                if (
                    $action !== 'update' ||
                    ((int)$net['id'] !== (int)$new_network['id'])
                ) :
                    $data['error'] = 1;
                    $data['error_msg'] = 'Name must be unique<br/>';
                endif;
            }
            if ($net['network'] == $network_plus_cidr) {
                if (
                    $action !== 'update' ||
                    ((int)$net['id'] !== (int)$new_network['id'])
                ) :
                    $data['error'] = 1;
                    $data['error_msg'] = 'Network must be unique<br/>';
                endif;
            }
        }
        if (isset($data['error'])) {
            return $data;
        }

        if (
            str_starts_with($new_network['network'], "0") ||
            !$this->ctx->get('Networks')->isLocal($new_network['network'])
        ) :
            $new_network['vlan'] = 0;
            $new_network['scan'] = 0;
        endif;

        return $new_network;
    }
}
