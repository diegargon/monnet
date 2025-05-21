<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Views;

use App\Core\AppContext;
use App\Core\ConfigService;

use App\Services\TemplateService;
use App\Services\HostService;
use App\Services\DateTimeService;
use App\Services\UserService;

class RefresherView
{
    private AppContext $ctx;
    private ConfigService $ncfg;
    private TemplateService $templates;
    private HostService $hostService;
    private DateTimeService $dateTimeService;

    public function __construct(AppContext $ctx, TemplateService $templates)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get(ConfigService::class);
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

        # Limit term max lines
        $term_max_lines = $this->ncfg->get('term_max_lines');
        if (is_array($logs) && count($logs) > $term_max_lines) {
            $term_logs = array_slice($logs, 0, $term_max_lines);
        } else {
            $term_logs = $logs;
        }

        $logs_lines = [];
        $logs_lines = $this->termLogsFormat($logs);

        return [
            'data' => $this->templates->getTpl('term', ['term_logs' => $logs_lines]),
            'cfg' => ['place' => '#right-container'],
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

        foreach ($host_logs as &$log) {
            $host = $this->hostService->getHostById($log['host_id']);
            $log['type_mark'] = '[H]';

            if (!empty($host['display_name'])) {
                $log['display_name'] = '[' . $host['display_name'] . ']';
            } elseif (!empty($host['title'])) {
                $log['display_name'] = '[' . $host['title'] . ']';
            } elseif (!empty($host['hostname'])) {
                $log['display_name'] = '[' . strstr($host['hostname'], '.', true) . ']';
            } else {
                $log['display_name'] = '[' . $host['ip'] . ']';
            }
        }

        return $host_logs;
    }

    public function termSystemLogsFormat(array $system_logs): array
    {
        foreach ($system_logs as &$system_log) {
            $system_log['type_mark'] = '[S]';
            $system_log['display_name'] = '';
        }

        return $system_logs;
    }

    private function termLogsFormat(array $logs): array
    {
        array_walk($logs, function(&$log) {
            if (!empty($log['date'])) {
                $log['date'] = DateTimeService::formatDateString($log['date']);
            }
        });

        $log_lines = [];
        $term_date_format= $this->ncfg->get('term_date_format');
        foreach ($logs as $log) {
            $log_date =  DateTimeService::formatDateString(
                    $log['date'],
                    $term_date_format,
                );
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

            $log_lines[] = $log_date .
                $log['type_mark'] .
                '[' . $loglevelname . ']' .
                $log['display_name'] .
                $log['msg'] .
                '<br/>';
        }

        return $log_lines;
    }

    /**
     * Formatea los datos de los hosts para la vista.
     *
     * @param array<string, mixed> $hosts_view
     * @param object $user
     * @param object $ncfg
     * @return array<string, mixed>
     */
    public function formatHosts(array $hosts_view): array
    {
        $user = $this->ctx->get(UserService::class);
        $theme = $user->getTheme();
        $lng = $this->ctx->get('lng');
        $date_now = new \DateTime(DateTimeService::dateNow(), new \DateTimeZone('UTC'));

        foreach ($hosts_view as $key => $vhost) {
            $hosts_view[$key]['theme'] = $theme;
            $hosts_view[$key]['details'] = $lng['L_IP'] . ': ' . $vhost['ip'] . "\n";

            // Título del host
            if (empty($vhost['title'])) {
                $hosts_view[$key]['title'] = !empty($vhost['hostname']) ? explode('.', $vhost['hostname'])[0] : $vhost['ip'];
            } else {
                if (!empty($vhost['hostname'])) {
                    $hosts_view[$key]['details'] .= $lng['L_HOSTNAME'] . ': ' . $vhost['hostname'] . "\n";
                }
            }

            // Estado online/offline
            $hosts_view[$key]['title_online'] = $vhost['online'] ? $lng['L_S_ONLINE'] : $lng['L_S_OFFLINE'];
            $hosts_view[$key]['host-status'] = $vhost['online'] ? 'led-green-on' : 'led-red-on';

            // Passing Reference
            $this->addMiscData($hosts_view[$key], $vhost);
            $this->addGlowTag($hosts_view[$key], $vhost, $date_now);
            $this->addEventMarks($hosts_view[$key], $vhost, $theme);
        }

        return $hosts_view;
    }

    /**
     * Agrega datos adicionales como fabricante, sistema operativo y tipo de sistema.
     *
     * @param array<string, mixed> $host_view
     * @param array<string, mixed> $vhost
     * @param object $ncfg
     */
    private function addMiscData(array &$host_view, array $vhost): void
    {
        if (!empty($vhost['misc']['manufacture'])) {
            $manufacture = $this->getManufacture($this->ncfg, $vhost['misc']['manufacture']);
            if (is_array($manufacture)) {
                $host_view['manufacture_image'] = $manufacture['manufacture_image'];
                $host_view['manufacture_name'] = $manufacture['name'];
            }
        }

        if (!empty($vhost['misc']['os'])) {
            $os = $this->getOs($this->ncfg, $vhost['misc']['os']);
            if (is_array($os)) {
                $host_view['os_image'] = $os['os_image'];
                $host_view['os_name'] = $os['name'];
            }
        }

        if (!empty($vhost['misc']['os_family'])) {
            $os_family = $this->getOsFamily($this->ncfg, $vhost['misc']['os_family']);
            if (is_array($os_family)) {
                $host_view['os_family_image'] = $os_family['os_family_image'];
                $host_view['os_family_name'] = $os_family['name'];
            }
        }
        if (!empty($vhost['misc']['system_rol'])) {
            $system_rol = $this->getSystemRol($this->ncfg, $vhost['misc']['system_rol']);
            if (is_array($system_rol)) {
                $host_view['system_rol_image'] = $system_rol['system_rol_image'];
                $host_view['system_rol_name'] = $system_rol['name'];
            }
        }
    }

    /**
     * Agrega el tag de "glow" (resaltado) a un host.
     *
     * @param array<string, mixed> $host_view
     * @param array<string, mixed> $vhost
     * @param \DateTime $date_now
     * @param object $ncfg
     */
    private function addGlowTag(array &$host_view, array $vhost, \DateTime $date_now): void
    {
        $host_view['glow_tag'] = '';
        $change_time = new \DateTime($vhost['glow'], new \DateTimeZone('UTC'));
        $diff = $date_now->diff($change_time);
        $minutes_diff = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        if ($minutes_diff > 0 && $minutes_diff <= $this->ncfg->get('glow_time')) {
            if ($host_view['alert']) {
                $host_view['glow_tag'] = 'host-glow-purple';
            } elseif ($host_view['warn']) {
                $host_view['glow_tag'] = 'host-glow-orange';
            } else {
                $host_view['glow_tag'] = $vhost['online'] ? 'host-glow-green' : 'host-glow-red';
            }
        }
    }

    /**
     * Agrega alertas y advertencias a un host.
     *
     * @param array<string, mixed> $host_view
     * @param array<string, mixed> $vhost
     * @param string $theme
     */
    private function addEventMarks(array &$host_view, array $vhost, string $theme): void
    {
        if (empty($vhost['misc']['disable_alarms'])) {
            if ($vhost['alert']) {
                $host_view['alert_mark'] = 'tpl/' . $theme . '/img/alert-mark.png';
            }
            if ($vhost['warn']) {
                $host_view['warn_mark'] = 'tpl/' . $theme . '/img/warn-mark.png';
            }
        }
    }

    /**
     *
     * @param ConfigService $ncfg
     * @param int $id
     * @return array<string, string|int>|bool
     */
    function getManufacture(ConfigService $ncfg, int $id): array|bool
    {
        $theme = $ncfg->get('theme');

        foreach ($ncfg->get('manufacture') as $manufacture) {
            if ($manufacture['id'] == $id) {
                $imgfile = 'tpl/' . $theme . '/img/icons/' . $manufacture['img'];
                if (file_exists($imgfile)) :
                    $manufacture['manufacture_image'] = $imgfile;
                else :
                    $manufacture['manufacture_image'] = 'tpl/' . $theme . '/img/icons/unknown.png';
                endif;
                $manufacture['manufacture_name'] = $manufacture['name'];

                return $manufacture;
            }
        }
        return false;
    }

    /**
     *
     * @param ConfigService $ncfg
     * @param int $id
     * @return array<string, string|int>|bool
     */
    function getOs(ConfigService $ncfg, int $id): array|bool
    {
        $theme = $ncfg->get('theme');

        foreach ($ncfg->get('os') as $os) {
            if ($os['id'] == $id) {
                $imgfile = 'tpl/' . $theme . '/img/icons/' . $os['img'];
                if (file_exists($imgfile)) :
                    $os['os_image'] = $imgfile;
                else :
                    $os['os_image'] = 'tpl/' . $theme . '/img/icons/unknown.png';
                endif;
                $os['os_name'] = $os['name'];

                return $os;
            }
        }
        return false;
    }

    /**
     *
     * @param ConfigService $ncfg
     * @param int $id
     * @return array<string, string|int>|bool
     */
    function getOsFamily(ConfigService $ncfg, int $id): array|bool
    {
        $theme = $ncfg->get('theme');

        foreach ($ncfg->get('os_family') as $osf) {
            if ($osf['id'] == $id) {
                $imgfile = 'tpl/' . $theme . '/img/icons/' . $osf['img'];
                if (file_exists($imgfile)) :
                    $osf['os_family_image'] = $imgfile;
                else :
                    $osf['os_family_image'] = 'tpl/' . $theme . '/img/icons/unknown.png';
                endif;
                $osf['os_family_name'] = $osf['name'];

                return $osf;
            }
        }
        return false;
    }
    /**
     *
     * @param ConfigService $ncfg
     * @param int $id
     * @return array<string, string|int>|bool
     */
    function getSystemRol(ConfigService $ncfg, int $id): array|bool
    {
        $theme = $ncfg->get('theme');

        foreach ($ncfg->get('system_rol') as $system_rol) {
            if ($system_rol['id'] == $id) {
                $imgfile = 'tpl/' . $theme . '/img/icons/' . $system_rol['img'];
                if (file_exists($imgfile)) :
                    $system_rol['system_rol_image'] = $imgfile;
                else :
                    $system_rol['system_rol_image'] = 'tpl/' . $theme . '/img/icons/unknown.png';
                endif;
                $system_rol['system_rol_name'] = $system_rol['name'];

                return $system_rol;
            }
        }
        return false;
    }
}
