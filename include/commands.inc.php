<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

/**
 *
 * @param string $cmd
 * @param array<string> $params
 * @param string $stdin
 * @return array<string,string>|bool
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
        $stdout = stream_get_contents($pipes[1]);
        is_string($stdout) ? $stdout = $stdout : null;
        $return['stdout'] = $stdout;
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        is_string($stderr) ? $stderr = trim($stderr)  : null;
        $return['stderr'] = $stderr;
        fclose($pipes[2]);
        proc_close($proc);
    } else {
        Log::error('Error run command');
        $return = false;
    }

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
