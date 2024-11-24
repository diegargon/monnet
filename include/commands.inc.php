<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

/**
 *
 * @param string $cmd
 * @param array<string> $params
 * @param string $stdin
 * @return array<string,string|int>|bool
 */
function run_cmd(string $cmd, array $params, string $stdin = null): array|bool
{
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
        Log::err('Error run command ');
        $return = false;
    }
    proc_close($proc);

    return $return;
}

/**
 *
 * @param string $cmd
 * @return string|bool
 */
function check_command(string $cmd): string|bool
{
    $result = run_cmd('command', ['-v', $cmd]);

    if (empty($result['stdout'])) {
        return false;
    } else {
        return $result['stdout'];
    }
}
