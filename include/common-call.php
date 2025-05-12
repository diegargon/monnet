<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/**
 *
 * @param array<string, mixed> $ncfg
 * @param int $id
 * @return array<string, string|int>|bool
 */
function get_manufacture_data(\Config $ncfg, int $id): array|bool
{
    $theme = $ncfg->get('theme');

    foreach ($ncfg->get('manufacture') as $manufacture) {
        if ($manufacture['id'] == $id) {
            $imgfile = 'tpl/' . $theme . '/img/icons/' . $manufacture['img'];
            if (file_exists($imgfile)) :
                $manufacture['manufacture_image'] = $imgfile;
            else :
                $manufacture['manufacture_image'] = 'tpl/' . $theme . '/img/icons/unknown.png';
            endif;
            $manufacture['manufacture_name'] = $manufacture['name'];

            return $manufacture;
        }
    }
    return false;
}

/**
 *
 * @param array<string, mixed> $ncfg
 * @param int $id
 * @return array<string, string|int>|bool
 */
function get_os_data(\Config $ncfg, int $id): array|bool
{
    $theme = $ncfg->get('theme');

    foreach ($ncfg->get('os') as $os) {
        if ($os['id'] == $id) {
            $imgfile = 'tpl/' . $theme . '/img/icons/' . $os['img'];
            if (file_exists($imgfile)) :
                $os['os_image'] = $imgfile;
            else :
                $os['os_image'] = 'tpl/' . $theme . '/img/icons/unknown.png';
            endif;
            $os['os_name'] = $os['name'];

            return $os;
        }
    }
    return false;
}
/**
 *
 * @param array<string, mixed> $ncfg
 * @param int $id
 * @return array<string, string|int>|bool
 */
function get_system_rol_data(\Config $ncfg, int $id): array|bool
{
    $theme = $ncfg->get('theme');

    foreach ($ncfg->get('system_rol') as $system_rol) {
        if ($system_rol['id'] == $id) {
            $imgfile = 'tpl/' . $theme . '/img/icons/' . $system_rol['img'];
            if (file_exists($imgfile)) :
                $system_rol['system_rol_image'] = $imgfile;
            else :
                $system_rol['system_rol_image'] = 'tpl/' . $theme . '/img/icons/unknown.png';
            endif;
            $system_rol['system_rol_name'] = $system_rol['name'];

            return $system_rol;
        }
    }
    return false;
}
