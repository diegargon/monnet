<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);

require_once('include/common.inc.php');
require_once('include/usermode.inc.php');

$tdata = [];
$data = [];
$data['conn'] = 'successful';
if ($user->getId() > 0) {
    $data['login'] = 'successful';
} else {
    $data['login'] = 'fail';
    print(json_encode($data));
    exit();
}
$frontend = new Frontend($cfg);
$tdata['theme'] = $cfg['theme'];

if ($user->getPref('show_hightlight_hosts_status')) {

    $tdata['hosts'] = get_hosts($cfg, $db, $user, $lng);
    $data['hosts'] = $frontend->getTpl('hosts', $tdata);
}


print json_encode($data);
