<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function run_cmd(string $cmd, array $params, string $stdin = null) {
    global $log;

    $return = [];
    $pipes = [];
    $exec_params = '';

    $descriptorspec = [
        0 => ["pipe", "r"], /* stdin */
        1 => ["pipe", "w"], /* stdout */
        2 => ["pipe", "w"] /* sterr */
            /* 2 => array("file", "/tmp/error-output.txt", "a") */
    ];

    foreach ($params as $param) {
        $exec_params .= ' ' . $param;
    }
    $exec_cmd = $cmd . $exec_params;

    $proc = proc_open($exec_cmd, $descriptorspec, $pipes);

    if (is_resource($proc)) {
        if (!empty($stdin)) {
            fwrite($pipes[0], $stdin);
            fclose($pipes[0]);
        }
        $return['stdout'] = trim(stream_get_contents($pipes[1]));
        fclose($pipes[1]);
        $return['stderr'] = trim(stream_get_contents($pipes[2]));
        fclose($pipes[2]);
    } else {
        $log->err('Error run command ');
        $return = false;
    }
    proc_close($proc);

    return $return;
}

function check_command($cmd) {
    $result = run_cmd('command', ['-v', $cmd]);

    return empty($result['stdout']) ? false : $result['stdout'];
}
