<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Models\LogSystemModel;

class LogSystemService
{
    /**
     *
     * @var int
     */
    private int $recursionCount = 0;
    private LogSystemModel $logSystemModel;
    private int $maxDbMsg = 254;
    private bool $console = false;
    private string $timezone = 'UTC';
    private array $lng = [];
    private \Config $ncfg;

    public function __construct(\AppContext $ctx)
    {
        $db = $ctx->get('DBManager');
        $this->lng = $ctx->get('lng');
        $this->ncfg = $ctx->get('Config');

        $this->logSystemModel = new LogSystemModel($db);
    }

    /**
     *
     * @param array<string, mixed> $opts
     *
     * @return array<int, array<string, string>>
     */
    public function get(array $opts): array
    {
        return $this->logSystemModel->getSystemDBLogs($opts);
    }

    /**
     *
     * @param int $log_level
     * @param mixed $msg
     * @param int|null $self_caller
     * @return void
     */
    public function logged(int $log_level, mixed $msg, ?int $self_caller = null): void
    {
        if ($self_caller === null) {
            $this->recursionCount = 0;
        } else {
            $this->recursionCount++;
            if ($this->recursionCount > 3) {
                return;
            }
        }

        if ($log_level <= $this->ncfg->get('log_level')) {
            if (is_array($msg)) {
                $msg = print_r($msg, true);
            }
            if ($this->console) {
                echo '[' .
                format_date_now($this->timezone, $this->ncfg->get('datetime_log_format')) .
                '][' . $this->ncfg->get('app_name') . '][' . $log_level . '] ' . $msg . "\n";
            }
            if ($this->ncfg->get('system_log_to_db')) {
                if ($log_level < 7 || $this->ncfg->get('system_log_to_db_debug')) :
                    if (mb_strlen($msg) > $this->maxDbMsg) {
                        $this->debug($this->lng['L_LOGMSG_TOO_LONG'] . '(System Log)', 1);
                        $msg_db = substr($msg, 0, $this->maxDbMsg);
                    } else {
                        $msg_db = $msg;
                    }
                    $this->logSystemModel->insert(['level' => $log_level, 'msg' => $msg_db]);
                endif;
            }

            if ($this->ncfg->get('log_to_file')) {
                $log_file = $this->ncfg->get('log_file');
                $content = '['
                    . format_date_now($this->timezone, $this->ncfg->get('datetime_log_format'))
                    . '][' . $this->ncfg->get('app_name') . ']:[' . $log_level . '] ' . $msg . "\n";

                $file_ready = false;
                $log_dir = dirname($log_file);

                // Ensure directory is writable
                if (!is_dir($log_dir) || !is_writable($log_dir)) {
                    $this->error('Log directory does not exist or is not writable: ' . $log_dir, 1);
                } else {
                    // File does not exist, try to create it
                    if (!file_exists($log_file)) {
                        $effectiveUser = false;
                        if (is_numeric($sysuid = getmyuid())) {
                            $effectiveUser = posix_getpwuid($sysuid);
                        }
                        $userName = $effectiveUser !== false ? $effectiveUser['name'] : 'Unknown';

                        if (!touch($log_file)) {
                            $this->error($this->lng['L_ERR_FILE_CREATE'] . ' effective User: ' . $userName, 1);
                            $this->debug(getcwd(), 1);
                        } else {
                            if (!chown($log_file, $this->ncfg->get('log_file_owner'))) {
                                $this->error($this->lng['L_ERR_FILE_CHOWN'], 1);
                            }
                            if (!chgrp($log_file, $this->ncfg->get('log_file_owner_group'))) {
                                $this->error('L_ERR_FILE_CHGRP', 1);
                            }
                            $file_ready = true;
                        }
                    } else {
                        if (is_writable($log_file)) {
                            $file_ready = true;
                        } else {
                            $this->error('Log file exists but is not writable: ' . $log_file, 1);
                        }
                    }

                    // Append to log only if file is ready
                    if ($file_ready) {
                        if (file_put_contents($log_file, $content, FILE_APPEND) === false) {
                            $this->error('Error opening/writing log to file', 1);
                        }
                    }
                }
            }

            if ($this->ncfg->get('system_log_to_syslog') === 1) {
                openlog(
                    $this->ncfg->get('app_name') . ' ' . $this->ncfg->get('monnet_version'),
                    LOG_NDELAY,
                    LOG_SYSLOG
                );
                syslog($log_level, $msg);
            }
        }
    }

    /**
     * Output log to console
     * @param bool $value
     * @return void
     */
    public function setConsole(bool $value): void
    {
        $this->console = $value;
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public function debug(mixed $msg, ?int $self_caller = null): void
    {
        $this->logged(\LogLevel::DEBUG, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public function info(mixed $msg, ?int $self_caller = null): void
    {
        $this->logged(\LogLevel::INFO, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public function notice(mixed $msg, ?int $self_caller = null): void
    {
        $this->logged(\LogLevel::NOTICE, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     * @return void
     */
    public function warning(mixed $msg, ?int $self_caller = null): void
    {
        $this->logged(\LogLevel::WARNING, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public function error(mixed $msg, ?int $self_caller = null): void
    {
        $this->logged(\LogLevel::ERROR, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public function alert(mixed $msg, ?int $self_caller = null): void
    {
        $this->logged(\LogLevel::ALERT, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     * @return void
     */
    public function critical(mixed $msg, ?int $self_caller = null): void
    {
        $this->logged(\LogLevel::CRITICAL, $msg, $self_caller);
    }

    /**
     *
     * @param mixed $msg
     * @param int|null $self_caller
     *
     * @return void
     */
    public function emergency(mixed $msg, ?int $self_caller = null): void
    {
        $this->logged(\LogLevel::EMERGENCY, $msg, $self_caller);
    }
}
