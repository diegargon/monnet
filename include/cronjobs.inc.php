<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function check_hosts(Database $db) {
    $hosts = get_hosts($db);

    ping_ports($hosts);

    foreach ($hosts as $host_id => $host) {
        update_host($db, $host_id, $host);
    }
}
