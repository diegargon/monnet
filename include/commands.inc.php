<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function run_command(string $cmd, array $params) {
    $pipes = [];

    $descriptorspec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
            /* 2 => array("file", "/tmp/error-output.txt", "a") */
    ];
    $_cmd = $cmd . ' ' . implode($params);
    $proc = proc_open($_cmd, $descriptorspec, $pipes);

    $return = stream_get_contents($pipes[1]);

    return $return;
}
