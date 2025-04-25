<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */


namespace App\Controllers;

use App\Models\LogModel;
use App\Services\RefresherService;
use App\Views\RefresherView;

class RefresherController
{
    private $ctx;
    private $view;
    private RefresherService $refreshService;

    public function __construct($ctx, RefresherView $view)
    {
        $this->ctx = $ctx;
        $this->view = $view;
    }

    public function refreshPage(): void
    {
        $logModel = new LogModel($this->ctx);
        $this->refreshService = $this->refreshService;

        $user = $this->ctx->get('User');
        $lng = $this->ctx->get('lng');
        $ncfg = $this->ctx->get('Config');
        $view_highlight = 0;
        $view_other_hosts = 1;
        $hosts_highlight_count = 0;
        $hosts_other_count = 0;
        $hosts_total = 0;
        $total_hosts_on = 0;
        $total_hosts_off = 0;

        $data = [
            'conn' => 'success',
            'login' => $user->getId() > 0 ? 'success' : 'fail',
        ];

        if ($data['login'] === 'fail') {
            $this->view->renderJson($data);
            return;
        }

        if ($user->getPref('show_highlight_hosts_status')) {
            $view_highlight = 1;
        }

        if (!$user->getPref('show_other_hosts_status')) {
            $view_other_hosts = 0;
        }

        $hosts = $refreshService->getHostsView($view_other_hosts, $view_highlight);
        $hosts_total = count($hosts);
        $hosts_highlight = [];
        $hosts_other = [];

        foreach ($hosts as $host) {
            if ($host['online']) {
                $total_hosts_on++;
            } else {
                $total_hosts_off++;
            }
            if ($host['highlight']) {
                $hosts_highlight[] = $host;
            } else {
                $hosts_other[] = $host;
            }
        }
        unset($hosts);

        // Renderizar hosts destacados
        if ($view_highlight && count($hosts_highlight) > 0) {
            $hosts_highlight_count = count($hosts_highlight);
            $data['highlight_hosts'] = $this->view->renderHighlightHosts(
                $hosts_highlight,
                $lng['L_HIGHLIGHT_HOSTS'],
                'highlight-hosts'
            );
        }

        // Renderizar otros hosts
        if ($view_other_hosts && count($hosts_other) > 0) {
            $hosts_other_count = count($hosts_other);
            $data['other_hosts'] = $this->view->renderOtherHosts(
                $hosts_other,
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

        $hosts_totals = $this->refreshService->get_hosts_stats();

        $data['footer_dropdown'][] = [
            'value' => $hosts_totals['total_online'] ?? 0,
            'desc' => $lng['L_HOSTS_ON'],
            'number-color' => 'blue'
        ];
        $data['footer_dropdown'][] = [
            'value' => $hosts_totals['total_offline'] ?? 0,
            'desc' => $lng['L_HOSTS_OFF'],
            'number-color' => 'red'
        ];

        if ($hosts->alerts) {
            $data['footer_dropdown'][] = [
                'value' => $hosts_totals['alerts'] ?? 0,
                'report_type' => 'alerts',
                'desc' => $lng['L_ALERTS'],
                'number-color' => 'red'
            ];
        }

        if ($hosts->warns) {
            $data['footer_dropdown'][] = [
                'value' => $hosts_totals['warns'] ?? 0,
                'report_type' => 'warns',
                'desc' => $lng['L_WARNS'],
                'number-color' => 'orange'
            ];
        }

        if ($ncfg->get('ansible')) {
            //$ansible_hosts_on = $ansible_hosts - $ansible_hosts_off;
            if ($hosts->ansible_hosts) {
                $data['footer_dropdown'][] = [
                    'value' => $hosts_totals['ansible_hosts'],
                    'report_type' => 'ansible_enabled',
                    'desc' => $lng['L_ANSIBLE_HOSTS'],
                    'number-color' => 'blue'
                ];
            }
            if ($hosts->ansible_hosts_off) {
                $data['footer_dropdown'][] = [
                    'value' => $hosts_totals['ansible_hosts_off'],
                    'report_type' => 'ansible_off',
                    'desc' => $lng['L_ANSIBLE_HOSTS_OFF'],
                    'number-color' => 'red'
                ];
            }
            if ($hosts->ansible_hosts_fail) {
                $data['footer_dropdown'][] = [
                    'value' => $hosts_totals['ansible_hosts_fail'],
                    'report_type' => 'ansible_fail',
                    'desc' => $lng['L_ANSIBLE_HOSTS_FAIL'],
                    'number-color' => 'red'
                ];
            }
        }

        if ($hosts_totals['agent_installed']) {
            $data['footer_dropdown'][] = [
                'value' => $hosts_totals['agents_installed'],
                'report_type' => 'agents_hosts',
                'desc' => $lng['L_AGENT_HOSTS'],
                'number-color' => 'blue'
            ];
        }

        if ($hosts_totals['agents_offline']) {
            $data['footer_dropdown'][] = [
                'value' => $hosts_totals['agents_offline'],
                'report_type' => 'agents_hosts_off',
                'desc' => $lng['L_AGENT_HOSTS_OFF'],
                'number-color' => 'red'
            ];
        }

        /*
         *  Host Down Bar
         */

        $cli_last_run = 'Never';
        if ($ncfg->get('cli_last_run')) {
            $cli_last_run = $ncfg->get('cli_last_run');
            $cli_last_run = utc_to_tz($cli_last_run, $user->getTimezone(), $ncfg->get('datetime_format_min'));
            $cli_last_run .= $ncfg->get('cli_last_run_metrics');
        }

        $discovery_last_run = 'Never';
        if ($ncfg->get('discovery_last_run')) {
            $discovery_last_run = $ncfg->get('discovery_last_run');
            $discovery_last_run = utc_to_tz($discovery_last_run, $user->getTimezone(), $ncfg->get('datetime_format_min'));
            $discovery_last_run .= $ncfg->get('discovery_last_run_metrics');
        }

        /* Usado para saber si hay alguien conectado */
        $ncfg->set('refreshing', time());

        $data['misc']['cli_last_run'] = 'CLI ' . strtolower($lng['L_UPDATED']) . ' ' . $cli_last_run;
        $data['misc']['discovery_last_run'] = 'Discovery ' . strtolower($lng['L_UPDATED']) . ' ' . $discovery_last_run;


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