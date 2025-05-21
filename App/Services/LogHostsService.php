<?php

/**
 * Service for managing host logs.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Core\AppContext;
use App\Core\DBManager;
use App\Core\ConfigService;

use App\Services\DateTimeService;

use App\Models\LogHostsModel;
use App\Models\HostsModel;

class LogHostsService
{
    private AppContext $ctx;

    private LogHostsModel $logHostsModel;
    private DateTimeService $dateTimeService;
    private ConfigService $ncfg;
    private DBManager $db;

    /** @var int */
    private int $max_db_msg = 254;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get(DBManager::class);
        $this->logHostsModel = new LogHostsModel($this->db);
        $this->dateTimeService = new DateTimeService();
        $this->ncfg = $ctx->get(ConfigService::class);
    }

    /**
     * Register a log entry for a host.
     *
     * @param int $log_level Log level.
     * @param int $host_id Host ID.
     * @param string $msg Log message.
     * @param int $log_type Log type (optional).
     * @param int $event_type Event type (optional).
     * @return void
     */
    public function logHost(
        int $log_level,
        int $host_id,
        string $msg,
        int $log_type = 0,
        int $event_type = 0
    ): void {
        if (mb_strlen($msg) > $this->max_db_msg) {
            $msg_db = mb_substr($msg, 0, $this->max_db_msg);
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
        $this->logHostsModel->insert($set);
    }

    /**
     * Get host logs based on options.
     *
     * @param array<string, mixed> $opts Filter options.
     * @return array<int, array<string, mixed>> List of logs, each log is an associative array.
     */
    public function getLogsHosts(array $opts): array
    {
        return $this->logHostsModel->getLogsHosts($opts);
    }

    /**
     * Get logs for a specific host, formatted for terminal.
     *
     * @param int $target_id Host ID.
     * @param array $command_values Command values (filters).
     * @return array<int, string> Formatted log lines.
     */
    public function getLogs(int $target_id, array $command_values): array
    {
        $opts = [
            'host_id' => $target_id,
            'show_ack' => 1,
        ];

        if (!empty($command_values['log_size']) && is_numeric($command_values['log_size'])) {
            $opts['limit'] = (int) $command_values['log_size'];
        } else {
            $opts['limit'] = (int) $this->ncfg->get('term_max_lines');
        }

        if (
            isset($command_values['log_level']) &&
            is_numeric($command_values['log_level']) &&
            $command_values['log_level'] >= 0
        ) {
            $opts['level'] = $command_values['log_level'];
        }

        $logs =  $this->logHostsModel->getLogsHosts($opts);

        if (empty($logs)) {
            return [];
        }

        return $this->formatHostLogs($logs);
    }

    /**
     * Get events based on the command.
     *
     * @param string $command Requested command.
     * @return array<string, mixed> Event data, including keysToShow and logs.
     */
    public function getEvents(string $command): array
    {
        $log_opts = [
            'limit' => 100,
            'ack' => 0,
        ];
        if ($command === 'showAlarms') :
            $log_opts['log_type'] = [
                \LogType::EVENT_ALERT,
                \LogType::EVENT_WARN,
            ];
        else :
            $log_opts['log_type'] = [
                \LogType::EVENT,
                \LogType::EVENT_ALERT,
                \LogType::EVENT_WARN,
            ];
        endif;

        $tdata['keysToShow'] = ['id', 'host', 'level', 'log_type', 'event_type', 'msg', 'ack', 'date'];
        $tdata['logs'] = $this->formatEventsLogs($this->logHostsModel->getLogsHosts($log_opts));

        return $tdata;
    }

    /**
     * Format event logs for presentation.
     *
     * @param array<int, array<string, mixed>> $logs Logs to format.
     * @return array<int, array<string, mixed>> Formatted logs.
     */
    private function formatEventsLogs(array $logs): array
    {
        foreach ($logs as &$log) {
            $log['host'] = $this->getHostDisplayName($log['host_id']);
            $log['date'] = $this->dateTimeService->formatDateString($log['date'], $this->ncfg->get('datetime_log_format'));
            $log['level'] = \LogLevel::getName($log['level']);
            $log['log_type'] = \LogType::getName($log['log_type']);
            if (\EventType::getName($log['event_type'])) {
                $log['event_type'] = \EventType::getName($log['event_type']);
            }
        }
        return $logs;
    }

    /**
     * Format host logs for terminal presentation.
     *
     * @param array<int, array<string, mixed>> $logs Logs to format.
     * @param string $nl Line separator (optional).
     * @return array<int, string> Formatted log lines.
     */
    private function formatHostLogs(array $logs, string $nl = '<br/>'): array
    {
        $log_lines = [];
        foreach ($logs as $term_log) {
            if (is_numeric($term_log['level'])) {
                $log_level = (int) $term_log['level'];
                $date = $this->dateTimeService->formatDateString($term_log['date'], $this->ncfg->get('term_date_format'));
                $loglevelname = \LogLevel::getName($term_log['level']);
                $loglevelname = str_replace('LOG_', '', $loglevelname);
                $loglevelname = substr($loglevelname, 0, 4);
                if ($log_level <= 2) {
                    $loglevelname = '<span class="color-red">' . $loglevelname . '</span>';
                } elseif ($log_level === 3) {
                    $loglevelname = '<span class="color-orange">' . $loglevelname . '</span>';
                } elseif ($log_level === 4) {
                    $loglevelname = '<span class="color-yellow">' . $loglevelname . '</span>';
                }
                $log_lines[] = $date . '[' . $loglevelname . ']' . $term_log['msg'] . $nl;
            }
        }

        return $log_lines;
    }

    /**
     * Avoid call hostService to avoid cyclic dependency.
     *
     * @param int $host_id Host ID.
     * @return string
     */
    public function getHostDisplayName(int $host_id): string
    {
        $hostModel = new HostsModel($this->db);
        $host = $hostModel->getHostById($host_id);

        if (!empty($host['title'])) {
            return $host['title'];
        } elseif (!empty($host['hostname'])) {
            return ucfirst(explode('.', $host['hostname'])[0]);
        }

        return $host['ip'];
    }
}
