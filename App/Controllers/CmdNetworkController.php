<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */


namespace App\Controllers;

use App\Models\CmdNetworkModel;

class CmdNetworkController
{
    private CmdNetworkModel $networkModel;
    private Filter $filter;
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->cmdNetworkModel = new CmdNetworkModel($ctx);
        $this->filter = new Filter();
        $this->ctx = $ctx;
    }

    public function manageNetworks($command_values)
    {
        $action = $this->filter->varString($command_values['action']);
        $target_id = $this->filter->varInt($command_values['id']);
        $value_command = $this->filter->varJson($command_values['value']);

        if ($action === 'remove') {
            $this->networkModel->removeNetwork($target_id);
            return [
                'command_success' => 1,
                'response_msg' => 'Network removed successfully',
            ];
        } elseif ($action === 'update' || $action === 'add') {
            $decodedJson = json_decode($value_command, true);
            if ($decodedJson === null) {
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'JSON Invalid',
                ];
            }

            $network_data = $this->validateNetworkData($decodedJson);
            if ($action === 'update') {
                $this->networkModel->updateNetwork($target_id, $network_data);
            } else {
                $this->networkModel->addNetwork($network_data);
            }

            return [
                'command_success' => 1,
                'response_msg' => 'Network ' . $action . ' successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Invalid action',
            ];
        }
    }

    private function validateNetworkData($data)
    {
        // Validar y sanitizar los datos de la red
        return [
            'network' => $this->filter->varString($data['network']),
            'description' => $this->filter->varString($data['description']),
        ];
    }
}
