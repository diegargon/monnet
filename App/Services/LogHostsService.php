<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Models\LogHostsModel;
use App\Services\DateTimeService;

class LogHostsService
{
    private \AppContext $ctx;

    private LogHostsModel $logHostsModel;
    private DateTimeService $dateTimeService;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $db = $ctx->get('DBManager');
        $this->logHostsModel = new LogHostsModel($db);
        $this->dateTimeService = new DateTimeService();
    }

    public function getLogsHosts(array $opts)
    {
        return $this->logHostsModel->getLogsHosts($opts);
    }
    /**
     *
     * @param int $target_id
     * @param array $command_values
     * @return array<string, string|int>
     */
    public function getLogs(int $target_id, array $command_values): array
    {
        $ncfg = $this->ctx->get('Config');

        $opts = [
            'host_id' => $target_id,
            'ack' => 1,
        ];

        if (!empty($command_values['log_size']) && is_numeric($command_values['log_size'])) :
            $opts['limit'] = (int) $command_values['log_size'];
        else :
            $opts['limit'] = (int) $ncfg->get('term_max_lines');
        endif;

        if (
            isset($command_values['log_level']) &&
            is_numeric($command_values['log_level']) &&
            $command_values['log_level'] >= 0
        ) :
            $opts['level'] = $command_values['log_level'];
        endif;

        $logs =  $this->logHostsModel->getLogsHosts($opts);

        if (empty($logs)) {
            return [];
        }

        return $this->formatHostLogs($logs);
    }

    /**
     *
     * @param string $command
     * @return array<string, string|int>
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
        $tdata['logs'] = $this->formatEventsLogs($this->logHostsModel->getLogs($log_opts));

        return $tdata;
    }

    /**
     *
     * @param array<string, string|int> $logs
     * @return array<string, string|int>
     */
    private function formatEventsLogs(array $logs): array
    {
        $ncfg = $this->ctx->get('Config');
        $hosts = $this->ctx->get('Hosts');

        foreach ($logs as &$log) {
            $log['host'] = $hosts->getDisplayNameById($log['host_id']);
            $log['date'] = format_datetime_from_string($log['date'], $ncfg->get('datetime_log_format'));
            $log['level'] = \LogLevel::getName($log['level']);
            $log['log_type'] = \LogType::getName($log['log_type']);
            if (\EventType::getName($log['event_type'])) {
                $log['event_type'] = \EventType::getName($log['event_type']);
            }
        }
        return $logs;
    }

    /**
     *
     * @param array<string, string|int> $logs
     * @param string $nl
     * @return array<string, string|int>
     */
    private function formatHostLogs(array $logs, string $nl = '<br/>'): array
    {
        $ncfg = $this->ctx->get('Config');

        $log_lines = [];
        foreach ($logs as $term_log) :
            if (is_numeric($term_log['level'])) :
                $log_level = (int) $term_log['level'];
                $date = $this->dateTimeService->formatDateString($term_log['date'], $ncfg->get('term_date_format'));
                $loglevelname = \LogLevel::getName($term_log['level']);
                $loglevelname = str_replace('LOG_', '', $loglevelname);
                $loglevelname = substr($loglevelname, 0, 4);
                if ($log_level <= 2) :
                    $loglevelname = '<span class="color-red">' . $loglevelname . '</span>';
                elseif ($log_level === 3) :
                    $loglevelname = '<span class="color-orange">' . $loglevelname . '</span>';
                elseif ($log_level === 4) :
                    $loglevelname = '<span class="color-yellow">' . $loglevelname . '</span>';
                endif;
                $log_lines[] = $date . '[' . $loglevelname . ']' . $term_log['msg'] . $nl;
            endif;
        endforeach;

        return $log_lines;
    }
}
