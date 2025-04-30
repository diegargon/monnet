<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Services\RefresherService;
use App\Views\RefresherView;
use App\Services\TemplateService;

class RefresherController
{
    private $ctx;
    private $view;
    private \Config $ncfg;
    private RefresherService $refresherService;

    public function __construct($ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get('Config');
        $templates = new TemplateService($ctx);
        $this->view = new RefresherView($ctx, $templates);
        $this->refresherService  = new RefresherService($ctx);
    }

    /**
     *
     * @return void
     */
    public function refreshPage(): void
    {
        $user = $this->ctx->get('User');
        $lng = $this->ctx->get('lng');
        $ncfg = $this->ctx->get('Config');
        $view_highlight = 0;
        $view_other_hosts = 1;

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

        $hosts = $this->refresherService->getHostsView($view_other_hosts, $view_highlight);
        $hosts = $this->view->formatHosts($hosts);

        usort($hosts, function ($a, $b) {
            return strcmp($a['display_name'], $b['display_name']);
        });

        $hosts_highlight = [];
        $hosts_other = [];

        foreach ($hosts as $host) {
            if ($host['highlight']) {
                $hosts_highlight[] = $host;
            } else {
                $hosts_other[] = $host;
            }
        }
        unset($hosts);

        // Renderizar hosts destacados
        if ($view_highlight && count($hosts_highlight) > 0) {
            $data['highlight_hosts'] = $this->view->renderHighlightHosts(
                $hosts_highlight,
                $lng['L_HIGHLIGHT_HOSTS'],
                'highlight-hosts'
            );
        }

        // Renderizar otros hosts
        if ($view_other_hosts && count($hosts_other) > 0) {
            $data['other_hosts'] = $this->view->renderOtherHosts(
                $hosts_other,
                $lng['L_OTHERS'],
                'other-hosts'
            );
        }

        // Renderizar logs

        if ($user->getPref('show_termlog_status')) {
            $hosts_logs = [];
            $system_logs = [];

            $hosts_logs = $this->refresherService->getTermHostsLogs();
            if (!empty($hosts_logs)) {
                $hosts_logs = $this->view->termHostsLogsFormat($hosts_logs);
            }
            if ($this->ncfg->get('term_show_system_logs') && $this->ncfg->get('system_log_to_db')) {
                $system_logs = $this->refresherService->getTermSystemLogs();

                if(!empty($system_logs)) {
                    $system_logs = $this->view->termSystemLogsFormat($system_logs);
                }
            }
            $term_logs = array_merge($hosts_logs, $system_logs);
            $data['term_logs'] = $this->view->renderTermLogs($term_logs);
        }

        // Down Bar details
        $hosts_totals = $this->refresherService->getHostsStats();
        $total_hosts = $hosts_totals['total_hosts'];
        $total_showing = count($hosts_highlight) + count($hosts_other);
        $data['misc']['totals'] = $lng['L_SHOWED'] . ": $total_showing | {$lng['L_TOTAL']}: $total_hosts";
        $timenow = $user->getDateNow($ncfg->get('datetime_format_min'));
        $data['misc']['last_refresher'] = $lng['L_REFRESHED'] . ': ' . $timenow;

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

        if ($hosts_totals['alerts']) {
            $data['footer_dropdown'][] = [
                'value' => $hosts_totals['alerts'] ?? 0,
                'report_type' => 'alerts',
                'desc' => $lng['L_ALERTS'],
                'number-color' => 'red'
            ];
        }

        if ($hosts_totals['warns']) {
            $data['footer_dropdown'][] = [
                'value' => $hosts_totals['warns'] ?? 0,
                'report_type' => 'warns',
                'desc' => $lng['L_WARNS'],
                'number-color' => 'orange'
            ];
        }

        if ($ncfg->get('ansible')) {
            if ($hosts_totals['ansible_hosts']) {
                $data['footer_dropdown'][] = [
                    'value' => $hosts_totals['ansible_hosts'],
                    'report_type' => 'ansible_hosts',
                    'desc' => $lng['L_ANSIBLE_HOSTS'],
                    'number-color' => 'blue'
                ];
            }
            if ($hosts_totals['ansible_hosts_off']) {
                $data['footer_dropdown'][] = [
                    'value' => $hosts_totals['ansible_hosts_off'],
                    'report_type' => 'ansible_hosts_off',
                    'desc' => $lng['L_ANSIBLE_HOSTS_OFF'],
                    'number-color' => 'red'
                ];
            }
            if ($hosts_totals['ansible_hosts_fail']) {
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
                'value' => $hosts_totals['agent_installed'],
                'report_type' => 'agents_hosts',
                'desc' => $lng['L_AGENT_HOSTS'],
                'number-color' => 'blue'
            ];
        }

        if ($hosts_totals['agent_offline']) {
            $data['footer_dropdown'][] = [
                'value' => $hosts_totals['agent_offline'],
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
}
