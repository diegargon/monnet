<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/

namespace App\Helpers;

class Response
{
    /**
     *
     * @param bool $success
     * @param mixed $msg
     * @param bool $force_reload
     * @param array<string, mixed> $extra_fields
     * @return array<string, string|int>
     */
    public static function stdReturn(
        bool $success,
        mixed $msg,
        bool $force_reload = false,
        array $extra_fields = [],
    ): array {
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
