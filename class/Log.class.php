<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

Class Log {

    private $console;
    private $cfg;
    private $db;

    public function __construct(array $cfg, Database $db) {        
        $this->console = false;
        $this->cfg = $cfg;
        $this->db = $db;
    }

    public function logged(string $type, string $msg) {

        $LOG_TYPE = [
            'LOG_EMERG' => 0, // 	UNUSEDsystem is unusable
            'LOG_ALERT' => 1, // 	UNUSED: action must be taken immediately UNUSED
            'LOG_CRIT' => 2, // 	UNUSED: critical conditions
            'LOG_ERR' => 3, //          error conditions
            'LOG_WARNING' => 4, // 	warning conditions
            'LOG_NOTICE' => 5, //	UNUSED: normal, but significant, condition
            'LOG_INFO' => 6, // 	informational message
            'LOG_DEBUG' => 7, //	debug-level message
        ];

        if (isset($LOG_TYPE[$this->cfg['syslog_level']]) && $LOG_TYPE[$type] <= $LOG_TYPE[$this->cfg['syslog_level']]) {
            if ($this->console) {
                if (is_array($msg)) {
                    $msg = var_dump($msg, true);
                }
                echo '[' . formatted_date_now($this->cfg['timezone'], $this->cfg['datetime_log_format']) . '][' . $this->cfg['app_name'] . '][' . $type . '] ' . $msg . "\n";
            }

            if ($this->cfg['log_to_file']) {
                $log_file = 'cache/logs/monnet.log';
                if (is_array($msg)) {
                    $msg = print_r($msg, true);
                }
                $content = '';
                $content = '[' . formatted_date_now($this->cfg['timezone'], $this->cfg['datetime_log_format']) . '][' . $this->cfg['app_name'] . ']:[' . $type . '] ' . $msg . "\n";
                if (!file_exists($log_file)) {
                    touch($log_file);
                }
                if ((file_put_contents($log_file, $content, FILE_APPEND)) === false) {
                    echo 'Error opening/writing log to file\n';
                }
            }
            if ($this->cfg['log_to_syslog']) {
                if (openlog($this->cfg['app_name'] . ' ' . $this->cfg['monnet_version'], LOG_NDELAY, LOG_SYSLOG)) {
                    if (is_array($msg)) {
                        $msg = print_r($msg, true);
                        isset($this->console) ? $this->cfg['app_name'] . ' : [' . $type . '] ' . $msg . "\n" : null;
                        syslog($LOG_TYPE[$type], $msg);
                    } else {
                        isset($this->console) ? $this->cfg['app_name'] . ' : [' . $type . '] ' . $msg . "\n" : null;
                        syslog($LOG_TYPE[$type], $msg);
                    }
                }
            }
        }
    }

    public function setConsole(bool $value) {
        if ($value === true || $value === false) {
            $this->console = $value;
        } else {
            return false;
        }
    }

    public function loghost(string $loglevel, int $host_id, string $msg) {
        $this->logged($loglevel, '[HOST:' . $host_id . ']' . $msg);
        $host_msg = '[' . $loglevel . '] ' . $msg . "\n";
        $set = ['host_id' => $host_id, 'msg' => $host_msg];
        $this->db->insert('hosts_logs', $set);
    }

    public function debug(string $msg) {
        $this->logged('LOG_DEBUG', $msg);
    }

    public function info(string $msg) {
        $this->logged('LOG_INFO', $msg);
    }

    public function notice(string $msg) {
        $this->logged('LOG_NOTICE', $msg);
    }

    public function warning(string $msg) {
        $this->logged('LOG_WARNING', $msg);
    }

    public function err(string $msg) {
        $this->logged('LOG_ERR', $msg);
    }

    public function alert(string $msg) {
        $this->logged('LOG_ALERT', $msg);
    }

    public function emerg(string $msg) {
        $this->logged('LOG_EMERG', $msg);
    }
}
