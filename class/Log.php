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
    /**
     * @var Database $db
     */
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
     * @param array<string, string> $lng
     * @param array<string, mixed> $cfg
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
                format_date_now(self::$cfg['timezone'], self::$cfg['datetime_log_format']) .
                '][' . self::$cfg['app_name'] . '][' . $type . '] ' . $msg . "\n";
            }
            if (self::$cfg['log_to_db']) {
                $level = self::getLogLevelId($type);
                if (is_numeric($level) && $level < 7 || self::$cfg['log_to_db_debug']) :
                    if (mb_strlen($msg) > self::$max_db_msg) {
                        self::debug(self::$lng['L_LOGMSG_TOO_LONG'] . '(System Log)', 1);
                        $msg_db = substr($msg, 0, 254);
                    } else {
                        $msg_db = $msg;
                    }
                    self::$db->insert('system_logs', ['level' => $level, 'msg' => $msg_db]);
                endif;
            }
            if (self::$cfg['log_to_file']) {
                $log_file = self::$cfg['log_file'];

                $content = '['
                    . format_date_now(self::$cfg['timezone'], self::$cfg['datetime_log_format'])
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
            if (self::$cfg['log_to_syslog'] === 1) {
                openlog(self::$cfg['app_name'] . ' ' . self::$cfg['monnet_version'], LOG_NDELAY, LOG_SYSLOG);
                syslog($LOG_TYPE[$type], $msg);
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
     * @param int $log_type
     *
     * @return void
     */
    public static function logHost(string $loglevel, int $host_id, string $msg, int $log_type = 0): void
    {
        $level = self::getLogLevelId($loglevel);
        !is_numeric($level) ? $level = 7 : null;

        if (mb_strlen($msg) > self::$max_db_msg) {
            self::debug(self::$lng['L_LOGMSG_TOO_LONG'] . '(Host ID:' . $host_id . ')', 1);
            $msg_db = substr($msg, 0, 254);
        } else {
            $msg_db = $msg;
        }
        $set = ['host_id' => $host_id, 'level' => $level, 'msg' => $msg_db, 'log_type' => $log_type];
        self::$db->insert('hosts_logs', $set);
    }

    /**
     * Return logs based on [$opt]ions
     * @param array<string,string|int> $opts
     * @return array<string,string>
     */
    public static function getLogsHosts(array $opts = []): array
    {
        $lines = [];
        $conditions = [];

        $query = 'SELECT * FROM hosts_logs';

        if (!empty($opts['level'])) :
            $conditions[] = 'level <= ' . (int)$opts['level'];
        endif;

        /* if ack is set show all if not hidde ack */
        if (!empty($opts['ack'])) :
            $conditions[] = ' ack >= 0';
        else :
            $conditions[] = ' ack != 1';
        endif;

        if (isset($opts['host_id'])) :
            $conditions[] = 'host_id = ' . (int)$opts['host_id'];
        endif;

        if (isset($opts['log_type'])) {
            if (is_array($opts['log_type'])) {
                $logConditions = [];
                foreach ($opts['log_type'] as $l_types) {
                    $logConditions[] = 'log_type=' . (int)$l_types;
                }
                $conditions[] = '(' . implode(' OR ', $logConditions) . ')';
            } else {
                $conditions[] = 'log_type=' . (int)$opts['log_type'];
            }
        }

        if (!empty($conditions)) :
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        endif;

        $query .= ' ORDER BY date DESC';

        if (!empty($opts['limit'])) :
            $query .= ' LIMIT ' . (int)$opts['limit'];
        endif;
        $result = self::$db->query($query);
        $lines = self::$db->fetchAll($result);

        return $lines;
    }

    /**
     *
     * @param string $loglevel
     * @return int|null
     */
    public static function getLogLevelId(string $loglevel): ?int
    {
        if (!isset(self::$LOG_TYPE[$loglevel])) {
            self::debug('Wrong Log Level name used');
            return null;
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
     * @return array<int, array<string, string>>
     */
    public static function getSystemDBLogs(int $limit): array
    {
        $lines = [];
        $query = 'SELECT * FROM system_logs WHERE level <= ' .
            self::$cfg['term_system_log_level'] . ' ORDER BY date DESC LIMIT ' . $limit;
        $result = self::$db->query($query);
        $lines = self::$db->fetchAll($result);

        return $lines;
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
