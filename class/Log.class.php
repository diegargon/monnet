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

    private $max_db_msg = 254;
    private $recursionCount = 0;
    private $console;
    private $cfg;
    private $db;
    private $lng;
    private $LOG_TYPE = [
        'LOG_EMERG' => 0, // 	system is unusable
        'LOG_ALERT' => 1, // 	action must be taken immediately UNUSED
        'LOG_CRIT' => 2, //     critical conditions
        'LOG_ERR' => 3, //      error conditions
        'LOG_WARNING' => 4, // 	warning conditions
        'LOG_NOTICE' => 5, //	normal, but significant, condition
        'LOG_INFO' => 6, // 	informational message
        'LOG_DEBUG' => 7, //	debug-level message
    ];

    public function __construct(array $cfg, Database $db, array $lng) {
        $this->console = false;
        $this->cfg = $cfg;
        $this->db = $db;
        $this->lng = $lng;
    }

    public function logged(string $type, mixed $msg, ?int $self_caller = null) {
        $LOG_TYPE = $this->LOG_TYPE;

        if ($self_caller === null) {
            $this->recursionCount = 0;
        } else {
            $this->recursionCount++;
            if ($this->recursionCount > 3) {
                return;
            }
        }


        if (isset($LOG_TYPE[$this->cfg['log_level']]) && $LOG_TYPE[$type] <= $LOG_TYPE[$this->cfg['log_level']]) {
            if (is_array($msg)) {
                $msg = print_r($msg, true);
            }
            if ($this->console) {
                echo '[' . formatted_date_now($this->cfg['timezone'], $this->cfg['datetime_log_format']) . '][' . $this->cfg['app_name'] . '][' . $type . '] ' . $msg . "\n";
            }
            if ($this->cfg['log_to_db']) {
                $level = $this->getLogLevelId($type);
                if (mb_strlen($msg) > $this->max_db_msg) {
                    $this->warning($this->lng['L_LOGMSG_TOO_LONG'], 1);
                    $msg = substr($msg, 0, 254);
                }
                $this->db->insert('system_logs', ['level' => $level, 'msg' => $msg]);
            }
            if ($this->cfg['log_to_file']) {
                $log_file = $this->cfg['log_file'];

                $content = '';
                $content = '[' . formatted_date_now($this->cfg['timezone'], $this->cfg['datetime_log_format']) . '][' . $this->cfg['app_name'] . ']:[' . $type . '] ' . $msg . "\n";
                if (!file_exists($log_file)) {
                    if (!touch($log_file)) {
                        $this->err($this->lng['L_ERR_FILE_CREATE'] . ' effective User: ' . posix_getpwuid(getmyuid())['name'], 1);
                        $this->debug(getcwd(), 1);
                    } else {
                        if (!chown($log_file, $this->cfg['log_file_owner'])) {
                            $this->err($this->lng['L_ERR_FILE_CHOWN'], 1);
                        }
                        if (!chgrp($log_file, $this->cfg['log_file_owner_group'])) {
                            $this->err('L_ERR_FILE_CHGRP', 1);
                        }
                        if ((file_put_contents($log_file, $content, FILE_APPEND)) === false) {
                            $this->err('Error opening/writing log to file ' . 'effective User: ' . posix_getpwuid(getmyuid())['name'], 1);
                        }
                    }
                }
                if ((file_put_contents($log_file, $content, FILE_APPEND)) === false) {
                    $this->err('Error opening/writing log to file', 1);
                }
            }
            if ($this->cfg['log_to_syslog']) {
                if (openlog($this->cfg['app_name'] . ' ' . $this->cfg['monnet_version'], LOG_NDELAY, LOG_SYSLOG)) {
                    isset($this->console) ? $this->cfg['app_name'] . ' : [' . $type . '] ' . $msg . "\n" : null;
                    syslog($LOG_TYPE[$type], $msg);
                } else {
                    $this->err('Error opening syslog', 1);
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

    public function logHost(string $loglevel, int $host_id, string $msg) {
        $level = $this->getLogLevelID($loglevel);
        if (mb_strlen($msg) > $this->max_db_msg) {
            $this->warning($this->lng['L_LOGMSG_TOO_LONG'], 1);
            $msg = substr($msg, 0, 254);
        }
        $set = ['host_id' => $host_id, 'level' => $level, 'msg' => $msg];
        $this->db->insert('hosts_logs', $set);
    }

    public function getLoghosts(int $limit) {
        $query = 'SELECT * FROM hosts_logs WHERE level <= ' . $this->cfg['term_log_level'] . ' ORDER BY date DESC LIMIT ' . $limit;
        $result = $this->db->query($query);
        $lines = $this->db->fetchAll($result);

        return valid_array($lines) ? $lines : false;
    }

    public function getLoghost(int $host_id, int $limit) {
        $query = 'SELECT * FROM hosts_logs WHERE level <= ' . $this->cfg['term_log_level'] . ' AND host_id = ' . $host_id . ' ORDER BY date DESC LIMIT ' . $limit;
        $result = $this->db->query($query);
        $lines = $this->db->fetchAll($result);

        return valid_array($lines) ? $lines : false;
    }

    public function getLogLevelId(string $loglevel) {

        return $this->LOG_TYPE[$loglevel];
    }

    public function getLogLevelName(int $logvalue) {
        foreach ($this->LOG_TYPE as $ktype => $vtype) {
            if ($vtype == $logvalue) {
                return $ktype;
            }
        }
    }

    public function getSystemDBLogs(int $limit) {
        $query = 'SELECT * FROM system_logs WHERE level <= ' . $this->cfg['term_system_log_level'] . ' ORDER BY date DESC LIMIT ' . $limit;
        $result = $this->db->query($query);
        $lines = $this->db->fetchAll($result);

        return valid_array($lines) ? $lines : false;
    }

    public function debug(mixed $msg, ?int $self_caller = null) {
        $this->logged('LOG_DEBUG', $msg, $self_caller);
    }

    public function info(mixed $msg, ?int $self_caller = null) {
        $this->logged('LOG_INFO', $msg, $self_caller);
    }

    public function notice(mixed $msg, ?int $self_caller = null) {
        $this->logged('LOG_NOTICE', $msg, $self_caller);
    }

    public function warning(mixed $msg, ?int $self_caller = null) {
        $this->logged('LOG_WARNING', $msg, $self_caller);
    }

    public function err(mixed $msg, ?int $self_caller = null) {
        $this->logged('LOG_ERR', $msg, $self_caller);
    }

    public function alert(mixed $msg, ?int $self_caller = null) {
        $this->logged('LOG_ALERT', $msg, $self_caller);
    }

    public function emerg(mixed $msg, ?int $self_caller = null) {
        $this->logged('LOG_EMERG', $msg, $self_caller);
    }
}
