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
            $imgfile = 'tpl/' . $cfg['theme'] . '/img/icons/' . $manufacture['img'];
            if (file_exists($imgfile)) :
                $manufacture['manufacture_image'] = $imgfile;
            else :
                $manufacture['manufacture_image'] = 'tpl/' . $cfg['theme'] . '/img/icons/unknown.png';
            endif;
            $manufacture['manufacture_name'] = $manufacture['name'];

            return $manufacture;
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
function get_os_data(array $cfg, int $id): array|bool
{
    foreach ($cfg['os'] as $os) {
        if ($os['id'] == $id) {
            $imgfile = 'tpl/' . $cfg['theme'] . '/img/icons/' . $os['img'];
            if (file_exists($imgfile)) :
                $os['os_image'] = $imgfile;
            else :
                $os['os_image'] = 'tpl/' . $cfg['theme'] . '/img/icons/unknown.png';
            endif;
            $os['os_name'] = $os['name'];

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
            $imgfile = 'tpl/' . $cfg['theme'] . '/img/icons/' . $system_type['img'];
            if (file_exists($imgfile)) :
                $system_type['system_type_image'] = $imgfile;
            else :
                $system_type['system_type_image'] = 'tpl/' . $cfg['theme'] . '/img/icons/unknown.png';
            endif;
            $system_type['system_type_name'] = $system_type['name'];

            return $system_type;
        }
    }
    return false;
}
