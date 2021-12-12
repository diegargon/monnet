<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/* phpseclib deps */
require_once 'vendor/autoload.php';

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

function ssh_connect_host(array $cfg, array &$result, array $host) {
    $ssh = new SSH2($host['ip']);
    $key = PublicKeyLoader::load(file_get_contents($cfg['cert']));

    //TODO Fingerprint check
    /*
      $_fingerprint = explode(" ", $ssh->getServerPublicHostKey());
      $fingerprint = $_fingerprint[1];
      if($fingerprint != $host['fingerprint']) {
      $result['conn'] = 'fail';
      $result['login'] = 'fail';
      $result['err_msg'] = 'Fingerprint check';
      return false;
      }
     */
    if (!$ssh->login('monnet', $key)) {
        $result['conn'] = 'fail';
        $result['login'] = 'fail';
        $result['err_msg'] = 'login fail';
        //throw new \Exception('Login failed');
        return false;
    }
    $result['conn'] = 'success';
    $result['login'] = 'success';

    return $ssh;
}

function ssh_exec(SSH2 $ssh, array &$result, string $cmd) {
    if (empty($result['motd'])) {
        $result['motd'] = $ssh->read('$');
    }
    $cmd = $cmd . ';echo @EOC';
    $ssh->write($cmd . "\n");
    $ssh->read('@EOC'); //this is the $cmd echo
    $ssh_result = $ssh->read('@EOC');
    $result['result'] = mb_substr($ssh_result, 0, -5);
}

function run_commands(array $cfg, Database $db) {
    $result = $db->select('cmd', '*');
    $cmds = $db->fetchAll($result);

    foreach ($cmds as $cmd) {
        $run_command = $cfg['commands'][$cmd['cmd_type']];
        //echo "Running: $run_command\n";
        $hid = $cmd['hid'];
        $result = $db->select('hosts', '*', ['id' => $hid]);
        $host = $db->fetchAll($result);
        $ssh_conn_result = [];
        $result = [];
        if (!empty($host) && !empty($host[0]['ip'])) {
            $host = $host[0];
            $host_status = ping($host['ip']);
            if (empty($host_status['isAlive'])) {
                //host down skip
                $db->delete('cmd', ['cmd_id' => $cmd['cmd_id']], 'LIMIT 1');
                continue;
            }
            $ssh = ssh_connect_host($cfg, $ssh_conn_result, $host);
            if (!$ssh) {
                continue;
            }
            try {
                ssh_exec($ssh, $result, $run_command);
            } catch (Exception $e) {
                //avoid error on shutdown and reboot catch it for ignore
                if ($cmd['cmd_type'] == 1 || $cmd['cmd_type'] == 2) {
                    //echo $e;
                } else {
                    echo $e;
                }
            }
            $db->delete('cmd', ['cmd_id' => $cmd['cmd_id']], 'LIMIT 1');
        }
    }
}

function ssh_exec_test(SSH2 $ssh, array &$result, string $cmd) {
    //Add Motd
    //empty($result['data']) ? $result['data'] = [] : null;
    $result['data']['motd'] = $ssh->read('$');
    //Add exec command
    $result['data']['cmd'] = $cmd;
    #$cmd = 'isvalid=true;count=1;while [ $isvalid ]; do echo $count; if [ $count -eq 5 ]; then break; fi; ((count++)); done;echo @EOC';
    $cmd = $cmd . ';echo @EOC';
    $ssh->write($cmd . "\n");
    $ssh->read('@EOC'); //this is the $cmd echo
    #echo $ssh->read('monnet@firewall:~$');
    ##echo "\n";

    $ssh_result = $ssh->read('@EOC');
    $ssh_result = mb_substr($ssh_result, 0, -5);
    echo $ssh_result . "\n";
    echo "------------------------\n";
    $ssh->write('cd /;echo @EOC' . "\n");
    echo $ssh->read('@EOC'); //this is the $cmd echo
    echo "FINECO\n";
    echo $ssh->read('@EOC') . "\n";
    echo "FINRES\n";
    $ssh->write($cmd . "\n");
    echo $ssh->read('@EOC');
    echo "FINECO\n";
    echo $ssh->read('@EOC') . "\n";
    echo "FINRES\n";
    /*
      $ssh->write($cmd . "\n");
      echo $ssh->read('@EOC');
      $ssh->setTimeout(1);
      echo $ssh->read('@EOC');
      $ssh->setTimeout(1);
      echo "---\n";
     *
     */
    //$ssh_result .= $ssh->read('@EOC');

    $result['data']['response'] = $ssh_result;
    $result['exec'] = 'success';

    return true;
}
