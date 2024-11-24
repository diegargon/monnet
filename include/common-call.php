<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/**
 *
 * @param array<string, mixed> $cfg
 * @param int $id
 * @return array<string, string|int>|bool
 */
function get_manufacture_data(array $cfg, int $id): array|bool
{
    foreach ($cfg['manufacture'] as $manufacture) {
        if ($manufacture['id'] == $id) {
            return $manufacture;
        }
    }
    return false;
}

/**
 *
 * @param array<string, mixed> $cfg
 * @param int $id
 * @return <string, string|int>|bool
 */
function get_os_data(array $cfg, int $id): array|bool
{
    foreach ($cfg['os'] as $os) {
        if ($os['id'] == $id) {
            return $os;
        }
    }
    return false;
}
/**
 *
 * @param array<string, mixed> $cfg
 * @param int $id
 * @return array<string, string|int>|bool
 */
function get_system_type_data(array $cfg, int $id): array|bool
{
    foreach ($cfg['system_type'] as $system_type) {
        if ($system_type['id'] == $id) {
            return $system_type;
        }
    }
    return false;
}
