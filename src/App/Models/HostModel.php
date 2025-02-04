<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Models;

class HostModel {
    public function remove($target_id) {
        // Lógica para eliminar un host
        global $db;
        $db->delete('hosts', ['id' => $target_id]);
    }

}