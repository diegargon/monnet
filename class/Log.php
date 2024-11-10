<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Log
{
    /**
     *
     * @var int
     */
    private static int $max_db_msg = 254;

    /**
     *
     * @var int
     */
    private static int $recursionCount = 0;

    /**
     *
     * @var bool
     */
    private static bool $console = false;

    /**
     * @var array<int|string, mixed> $cfg
     */
    private static array $cfg;
    private static Database $db;

    /**
     * @var array<string> $lng
     */
    private static array $lng = [];

    /**
     * @var array<string, int> $LOG_TYPE
     */
    private static $LOG_TYPE = [
        'LOG_EMERG' => 0, // system is unusable
        'LOG_ALERT' => 1, // action must be taken immediately UNUSED
        'LOG_CRIT' => 2, // critical conditions
        'LOG_ERR' => 3, // error conditions
        'LOG_WARNING' => 4, // warning conditions
        'LOG_NOTICE' => 5, // normal, but significant, condition
        'LOG_INFO' => 6, // informational message
        'LOG_DEBUG' => 7, // debug-level message
    ];

    /**
     * @param array $lng
     * @param array $cfg
     */
    public static function init(array &$cfg, Database &$db, array &$lng): void
    {
        self::$cfg = &$cfg;
        self::$db = &$db;
        self::$lng = &$lng;
    }

    /**
     *
     * @param string $type
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function logged(string $type, mixed $msg, ?int $self_caller = null): void
    {
        $LOG_TYPE = self::$LOG_TYPE;

        if ($self_caller === null) {
            self::$recursionCount = 0;
        } else {
            self::$recursionCount++;
            if (self::$recursionCount > 3) {
                return;
            }
        }

        if (isset($LOG_TYPE[self::$cfg['log_level']]) && $LOG_TYPE[$type] <= $LOG_TYPE[self::$cfg['log_level']]) {
            if (is_array($msg)) {
                $msg = print_r($msg, true);
            }
            if (self::$console) {
                echo '[' .
                formatted_date_now(self::$cfg['timezone'], self::$cfg['datetime_log_format']) .
                '][' . self::$cfg['app_name'] . '][' . $type . '] ' . $msg . "\n";
            }
            if (self::$cfg['log_to_db']) {
                $level = self::getLogLevelId($type);
                if (mb_strlen($msg) > self::$max_db_msg) {
                    self::debug(self::$lng['L_LOGMSG_TOO_LONG'] . '(System Log)', 1);
                    $msg_db = substr($msg, 0, 254);
                } else {
                    $msg_db = $msg;
                }
                self::$db->insert('system_logs', ['level' => $level, 'msg' => $msg_db]);
            }
            if (self::$cfg['log_to_file']) {
                $log_file = self::$cfg['log_file'];

                $content = '['
                    . formatted_date_now(self::$cfg['timezone'], self::$cfg['datetime_log_format'])
                    . '][' . self::$cfg['app_name'] . ']:[' . $type . '] ' . $msg . "\n";
                if (!file_exists($log_file)) {
                    if (!touch($log_file)) {
                        self::err(self::$lng['L_ERR_FILE_CREATE']
                            . ' effective User: ' . posix_getpwuid(getmyuid())['name'], 1);
                        self::debug(getcwd(), 1);
                    } else {
                        if (!chown($log_file, self::$cfg['log_file_owner'])) {
                            self::err(self::$lng['L_ERR_FILE_CHOWN'], 1);
                        }
                        if (!chgrp($log_file, self::$cfg['log_file_owner_group'])) {
                            self::err('L_ERR_FILE_CHGRP', 1);
                        }
                        if ((file_put_contents($log_file, $content, FILE_APPEND)) === false) {
                            self::err('Error opening/writing log to file '
                                . 'effective User: ' . posix_getpwuid(getmyuid())['name'], 1);
                        }
                    }
                }
                if ((file_put_contents($log_file, $content, FILE_APPEND)) === false) {
                    self::err('Error opening/writing log to file', 1);
                }
            }
            if (self::$cfg['log_to_syslog']) {
                if (openlog(self::$cfg['app_name'] . ' ' . self::$cfg['monnet_version'], LOG_NDELAY, LOG_SYSLOG)) {
                    isset(self::$console) ? self::$cfg['app_name'] . ' : [' . $type . '] ' . $msg . "\n" : null;
                    syslog($LOG_TYPE[$type], $msg);
                } else {
                    self::err('Error opening syslog', 1);
                }
            }
        }
    }

    /**
     * Output log to console
     * @param bool $value
     * @return void
     */
    public static function setConsole(bool $value): void
    {
        self::$console = $value;
    }

    /**
     *
     * @param string $loglevel
     * @param int $host_id
     * @param string $msg
     *
     * @return void
     */
    public static function logHost(string $loglevel, int $host_id, string $msg): void
    {
        $level = self::getLogLevelID($loglevel);
        if (mb_strlen($msg) > self::$max_db_msg) {
            self::debug(self::$lng['L_LOGMSG_TOO_LONG'] . '(Host ID:' . $host_id . ')', 1);
            $msg_db = substr($msg, 0, 254);
        } else {
            $msg_db = $msg;
        }
        $set = ['host_id' => $host_id, 'level' => $level, 'msg' => $msg_db];
        self::$db->insert('hosts_logs', $set);
    }

    /**
     *
     * @param int $limit
     * @return array
     */
    public static function getLoghosts(int $limit): array|bool
    {
        $query = 'SELECT * FROM hosts_logs WHERE level <= ' .
            self::$cfg['term_log_level'] . ' ORDER BY date DESC LIMIT ' . $limit;
        $result = self::$db->query($query);
        $lines = self::$db->fetchAll($result);

        return valid_array($lines) ? $lines : false;
    }

    /**
     *
     * @param int $host_id
     * @param array $opts
     *
     * @return array
     */
    public static function getLoghost(int $host_id, array $opts): array|bool
    {
        if (!isset($opts['log_level']) || !is_numeric($opts['log_level'])) :
            $log_level = self::$cfg['term_log_level'];
        else :
            $log_level = $opts['log_level'];
        endif;

        $query = 'SELECT * FROM hosts_logs WHERE level <= ' . $log_level .
            ' AND host_id = ' . $host_id . ' ORDER BY date DESC';

        if (!empty($opts['max_lines'])) :
            $query .= ' LIMIT ' . $opts['max_lines'];
        endif;

        $result = self::$db->query($query);
        $lines = self::$db->fetchAll($result);

        return valid_array($lines) ? $lines : false;
    }

    public static function getLogLevelId(string $loglevel): int|bool
    {
        if (!isset(self::$LOG_TYPE[$loglevel])) {
            self::debug('Wrong Log Level name used');
            return false;
        }
        return self::$LOG_TYPE[$loglevel];
    }

    /**
     *
     * @param int $logvalue
     *
     * @return string|bool
     */
    public static function getLogLevelName(int $logvalue): string|bool
    {
        foreach (self::$LOG_TYPE as $ktype => $vtype) {
            if ($vtype == $logvalue) {
                return $ktype;
            }
        }
        return false;
    }

    /**
     *
     * @param int $limit
     *
     * @return array|bool
     */
    public static function getSystemDBLogs(int $limit): array|bool
    {
        $query = 'SELECT * FROM system_logs WHERE level <= ' .
            self::$cfg['term_system_log_level'] . ' ORDER BY date DESC LIMIT ' . $limit;
        $result = self::$db->query($query);
        $lines = self::$db->fetchAll($result);

        return valid_array($lines) ? $lines : false;
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function debug(mixed $msg, ?int $self_caller = null): void
    {
        self::logged('LOG_DEBUG', $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function info(mixed $msg, ?int $self_caller = null): void
    {
        self::logged('LOG_INFO', $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function notice(mixed $msg, ?int $self_caller = null): void
    {
        self::logged('LOG_NOTICE', $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     * @return void
     */
    public static function warning(mixed $msg, ?int $self_caller = null): void
    {
        self::logged('LOG_WARNING', $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function err(mixed $msg, ?int $self_caller = null): void
    {
        self::logged('LOG_ERR', $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function alert(mixed $msg, ?int $self_caller = null): void
    {
        self::logged('LOG_ALERT', $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function emerg(mixed $msg, ?int $self_caller = null): void
    {
        self::logged('LOG_EMERG', $msg, $self_caller);
    }
}
