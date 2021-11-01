<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);

require('config/config.inc.php');
require('includes/conn.inc.php');
require('includes/htmlbuilder.inc.php');
require('includes/utils.inc.php');

$data = [];

if (!empty($_GET['show_services'])) {
    $data['services'] = get_services();
}

if (!empty($_GET['this_system'])) {
    $data['this_system'] = get_this_system();
}

print json_encode($data);
