<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Views;

use App\Services\TemplateService;
use App\Services\HostService;
use App\Services\DateTimeService;

class RefresherView
{
    private \AppContext $ctx;
    private \Config $ncfg;
    private TemplateService $templates;
    private HostService $hostService;
    private DateTimeService $dateTimeService;

    public function __construct(\AppContext $ctx, TemplateService $templates)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get('Config');
        $this->templates = $templates;
    }

    public function renderHighlightHosts(array $hosts_view, string $title, string $containerId): array
    {
        return [
            'data' => $this->templates->getTpl('hosts-min', [
                'hosts' => $hosts_view,
                'container-id' => $containerId,
                'head-title' => $title,
            ]),
            'cfg' => ['place' => '#host_place'],
        ];
    }

    public function renderOtherHosts(array $hosts_view, string $title, string $containerId): array
    {
        return [
            'data' => $this->templates->getTpl('hosts-min', [
                'hosts' => $hosts_view,
                'container-id' => $containerId,
                'head-title' => $title,
            ]),
            'cfg' => ['place' => '#host_place'],
        ];
    }

    public function renderTermLogs(array $logs): array
    {
        if (!isset($this->dateTimeService)) {
            $this->dateTimeService = new DateTimeService();
        }
        usort($logs, fn($a, $b) => $b['date'] <=>$a['date']);

        array_walk($logs, function(&$log) {
            if (!empty($log['date'])) {
                $log['date'] = $this->dateTimeService->formatDateString($log['date']);
            }
        });

        # Limit term max lines
        $term_max_lines = $this->ncfg->get('term_max_lines');
        if (is_array($logs) && count($logs) > $term_max_lines) {
            $term_logs = array_slice($logs, 0, $term_max_lines);
        } else {
            $term_logs = $logs;
        }

        $logs_lines = $this->termLogsFormat($logs);

        return [
            'data' => $this->templates->getTpl('term', ['term_logs' => $logs_lines]),
            'cfg' => ['place' => '#center-container'],
        ];
    }

    public function renderJson(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function termHostsLogsFormat(array $host_logs): array
    {
        if (!isset($this->hostService)) {
            $this->hostService = new HostService($this->ctx);
        }

        foreach ($host_logs as &$log) :
            $host = $this->hostService->getHostById($log['host_id']);
            $log['type_mark'] = '[H]';

            if (!empty($host['display_name'])) :
                $log['display_name'] = '[' . $host['display_name'] . ']';
            elseif (!empty($host['ip'])) :
                $log['display_name'] = '[' . $host['ip'] . ']';
            else :
                $log['display_name'] = '[' . $log['host_id'] . ']';
            endif;
        endforeach;

        return $host_logs;
    }

    public function termSystemLogsFormat(array $system_logs): array
    {
        foreach ($system_logs as &$system_log) :
            $system_log['type_mark'] = '[S]';
            $system_log['display_name'] = '';
        endforeach;

        return $system_logs;
    }

    private function termLogsFormat(array $logs): array
    {
        $log_lines = [];
        foreach ($logs as $log) {
            $log_level = (int) $log['level'];
            $loglevelname = \LogLevel::getName($log_level);
            $loglevelname = str_replace('LOG_', '', $loglevelname);
            $loglevelname = substr($loglevelname, 0, 4);

            if ($log_level <= 2) {
                $loglevelname = '<span class="color-red">' . $loglevelname . '</span>';
            } elseif ($log_level === 3) {
                $loglevelname = '<span class="color-orange">' . $loglevelname . '</span>';
            } elseif ($log_level === 4) {
                $loglevelname = '<span class="color-yellow">' . $loglevelname . '</span>';
            }

            $log_lines[] = $log['date'] .
                $log['type_mark'] .
                '[' . $loglevelname . ']' .
                $log['display_name'] .
                $log['msg'] .
                '<br/>';
        }

        return $log_lines;
    }
}
