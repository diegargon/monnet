<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */


namespace App\Controllers;

use App\Models\HostModel;
use App\Models\LogModel;
use App\Views\RefresherView;

class RefresherController
{
    private $ctx;
    private $view;

    public function __construct($ctx, RefresherView $view)
    {
        $this->ctx = $ctx;
        $this->view = $view;
    }

    public function refreshPage(): void
    {
        $hostModel = new HostModel($this->ctx);
        $logModel = new LogModel($this->ctx);

        $user = $this->ctx->get('User');
        $lng = $this->ctx->get('lng');
        $ncfg = $this->ctx->get('Config');

        $data = [
            'conn' => 'success',
            'login' => $user->getId() > 0 ? 'success' : 'fail',
        ];

        if ($data['login'] === 'fail') {
            $this->view->renderJson($data);
            return;
        }

        // Renderizar hosts destacados
        if ($user->getPref('show_highlight_hosts_status')) {
            $highlight_hosts = $hostModel->getHostsView(1);
            $data['highlight_hosts'] = $this->view->renderHighlightHosts(
                $highlight_hosts,
                $lng['L_HIGHLIGHT_HOSTS'],
                'highlight-hosts'
            );
        }

        // Renderizar otros hosts
        if ($user->getPref('show_other_hosts_status')) {
            $other_hosts = $hostModel->getHostsView();
            $data['other_hosts'] = $this->view->renderOtherHosts(
                $other_hosts,
                $lng['L_OTHERS'],
                'other-hosts'
            );
        }

        // Renderizar logs
        if ($user->getPref('show_termlog_status')) {
            $logs = $logModel->getTermLogs();
            $log_lines = $this->formatLogs($logs, $ncfg);
            $data['term_logs'] = $this->view->renderTermLogs($log_lines);
        }

        $this->view->renderJson($data);
    }

    private function formatLogs(array $logs, $ncfg): array
    {
        $log_lines = [];
        foreach ($logs as $log) {
            $date = format_datetime_from_string($log['date'], $ncfg->get('term_date_format'));
            $log_level = (int) $log['level'];
            $loglevelname = LogLevel::getName($log_level);
            $loglevelname = str_replace('LOG_', '', $loglevelname);
            $loglevelname = substr($loglevelname, 0, 4);

            if ($log_level <= 2) {
                $loglevelname = '<span class="color-red">' . $loglevelname . '</span>';
            } elseif ($log_level === 3) {
                $loglevelname = '<span class="color-orange">' . $loglevelname . '</span>';
            } elseif ($log_level === 4) {
                $loglevelname = '<span class="color-yellow">' . $loglevelname . '</span>';
            }

            $log_lines[] = $date . $log['type_mark'] . '[' . $loglevelname . ']' . $log['display_name'] . $log['msg'] . '<br/>';
        }

        return $log_lines;
    }
}