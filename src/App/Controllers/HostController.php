<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Models\HostModel;

class HostController {
    private $hostModel;

    public function __construct() {
        $this->hostModel = new HostModel();
    }

    public function removeHost($command_values) {
        $target_id = Filters::varInt($command_values['id']);
        $this->hostModel->remove($target_id);

        return [
            'command_success' => 1,
            'response_msg' => 'Host removed: ' . $target_id,
        ];
    }
}