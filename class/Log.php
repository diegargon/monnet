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
     * @var array<string, mixed> $cfg
     */
    private static array $cfg;
    /**
     * @var Database $db
     */
    private static Database $db;

    /**
     * @var array<string,string> $lng
     */
    private static array $lng = [];

    /**
     * @param array<string, mixed> $cfg
     * @param Database $db
     * @param array<string, string> $lng
     */
    public static function init(array &$cfg, Database &$db, array &$lng): void
    {
        self::$cfg = &$cfg;
        self::$db = &$db;
        self::$lng = &$lng;
    }

    /**
     *
     * @param int $log_level
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function logged(int $log_level, mixed $msg, ?int $self_caller = null): void
    {
        if ($self_caller === null) {
            self::$recursionCount = 0;
        } else {
            self::$recursionCount++;
            if (self::$recursionCount > 3) {
                return;
            }
        }

        if ($log_level <= self::$cfg['log_level']) {
            if (is_array($msg)) {
                $msg = print_r($msg, true);
            }
            if (self::$console) {
                echo '[' .
                format_date_now(self::$cfg['timezone'], self::$cfg['datetime_log_format']) .
                '][' . self::$cfg['app_name'] . '][' . $log_level . '] ' . $msg . "\n";
            }
            if (self::$cfg['system_log_to_db']) {
                if ($log_level < 7 || self::$cfg['system_log_to_db_debug']) :
                    if (mb_strlen($msg) > self::$max_db_msg) {
                        self::debug(self::$lng['L_LOGMSG_TOO_LONG'] . '(System Log)', 1);
                        $msg_db = substr($msg, 0, 254);
                    } else {
                        $msg_db = $msg;
                    }
                    self::$db->insert('system_logs', ['level' => $log_level, 'msg' => $msg_db]);
                endif;
            }
            if (self::$cfg['log_to_file']) {
                $log_file = self::$cfg['log_file'];

                $content = '['
                    . format_date_now(self::$cfg['timezone'], self::$cfg['datetime_log_format'])
                    . '][' . self::$cfg['app_name'] . ']:[' . $log_level . '] ' . $msg . "\n";
                if (!file_exists($log_file)) {
                    $effectiveUser = false;
                    if (is_numeric($sysuid = getmyuid())) {
                        $effectiveUser = posix_getpwuid($sysuid);
                    }
                    $userName = $effectiveUser !== false ? $effectiveUser['name'] : 'Unknown';
                    if (!touch($log_file)) {
                        self::error(self::$lng['L_ERR_FILE_CREATE']
                            . ' effective User: ' . $userName, 1);
                        self::debug(getcwd(), 1);
                    } else {
                        if (!chown($log_file, self::$cfg['log_file_owner'])) {
                            self::error(self::$lng['L_ERR_FILE_CHOWN'], 1);
                        }
                        if (!chgrp($log_file, self::$cfg['log_file_owner_group'])) {
                            self::error('L_ERR_FILE_CHGRP', 1);
                        }
                        if ((file_put_contents($log_file, $content, FILE_APPEND)) === false) {
                            self::err('Error opening/writing log to file '
                                . 'effective User: ' . $userName, 1);
                        }
                    }
                }
                if ((file_put_contents($log_file, $content, FILE_APPEND)) === false) {
                    self::error('Error opening/writing log to file', 1);
                }
            }
            if (self::$cfg['system_log_to_syslog'] === 1) {
                openlog(self::$cfg['app_name'] . ' ' . self::$cfg['monnet_version'], LOG_NDELAY, LOG_SYSLOG);
                syslog($log_level, $msg);
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
     * @param int $log_level
     * @param int $host_id
     * @param string $msg
     * @param int $log_type
     *
     * @return void
     */
    public static function logHost(
        int $log_level,
        int $host_id,
        string $msg,
        int $log_type = 0,
        int $event_type = 0
    ): void {
        if (mb_strlen($msg) > self::$max_db_msg) {
            self::debug(self::$lng['L_LOGMSG_TOO_LONG'] . '(Host ID:' . $host_id . ')', 1);
            $msg_db = substr($msg, 0, 254);
        } else {
            $msg_db = $msg;
        }
        $set = [
            'host_id' => $host_id,
            'level' => $log_level,
            'msg' => $msg_db,
            'log_type' => $log_type,
            'event_type' => $event_type
        ];
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

        $query .= ' WHERE ' . implode(' AND ', $conditions);
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
        self::logged(LogLevel::DEBUG, $msg, $self_caller);
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
        self::logged(LogLevel::INFO, $msg, $self_caller);
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
        self::logged(LogLevel::NOTICE, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     * @return void
     */
    public static function warning(mixed $msg, ?int $self_caller = null): void
    {
        self::logged(LogLevel::WARNING, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function error(mixed $msg, ?int $self_caller = null): void
    {
        self::logged(LogLevel::ERROR, $msg, $self_caller);
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
        self::logged(LogLevel::ALERT, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     * @return void
     */
    public static function critical(mixed $msg, ?int $self_caller = null): void
    {
        self::logged(LogLevel::CRITICAL, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public static function emergency(mixed $msg, ?int $self_caller = null): void
    {
        self::logged(LogLevel::EMERGENCY, $msg, $self_caller);
    }
}
