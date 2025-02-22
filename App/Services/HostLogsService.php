<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Models\CmdHostLogsModel;
use App\Services\DateTimeService;

class HostLogsService {
    private \AppContext $ctx;

    private CmdHostLogsModel $cmdHostLogsModel;
    private DateTimeService $dateTimeService;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->cmdHostLogsModel = new CmdHostLogsModel($ctx);
        $this->dateTimeService = new DateTimeService();
    }


    public function getLogs(int $target_id, array $command_values)
    {
        $cfg = $this->ctx->get('cfg');

        $opts = [
            'host_id' => $target_id,
            'ack' => 1,
        ];

        if (!empty($command_values['log_size']) && is_numeric($command_values['log_size'])) :
            $opts['limit'] = (int) $command_values['log_size'];
        else :
            $opts['limit'] = (int) $cfg['term_max_lines'];
        endif;

        if (
            isset($command_values['log_level']) &&
            is_numeric($command_values['log_level']) &&
            $command_values['log_level'] >= 0
        ) :
            $opts['level'] = $command_values['log_level'];
        endif;

        $logs =  $this->cmdHostLogsModel->getLogsHosts($opts);
        $response = '';
        if (!empty($logs)) {
            $response = $this->formatHostLogs($logs);
        }

        return $response;
    }

    private function formatHostLogs(array $logs, string $nl = '<br/>'): array
    {
        $cfg = $this->ctx->get('cfg');

        $log_lines = [];
        foreach ($logs as $term_log) :
            if (is_numeric($term_log['level'])) :
                $log_level = (int) $term_log['level'];
                $date = $this->dateTimeService->formatDateString($term_log['date'], $cfg['term_date_format']);
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
        //return implode(', ', $log_lines);
    }
}

