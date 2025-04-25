<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Models\HostsModel;

class RefresherService
{
    private \AppContext $ctx;
    private HostsModel $hostsModel;

    public function __construct(\AppContext $ctx) {
        $this->ctx = $ctx;
        $this->hostsModel = new HostsModel($ctx);
    }

    /**
     * Obtiene la vista de hosts según el estado de "highlight".
     *
     * @param int $other_hosts
     * @param int $highlight
     * @return array<string, mixed>
     */
    public function get_hosts_view(int $other_hosts = 1, int $highlight = 0): array
    {
        $hosts = $this->ctx->get('Hosts');
        $user = $this->ctx->get('User');
        $networks = $this->ctx->get('Networks');
        $ncfg = $this->ctx->get('Config');

        $hosts_filter = [];

        if ($highlight == 1) {
            $hosts_filter['highlight'] = 1;
        }

        $user_networks = $user->get_selected_networks();

        if (!empty($user_networks) && count($user_networks) > 0) {
            $hosts_filter['networks'] = $user_networks;
        }

        $valid_cats = $user->getEnabledHostCatId();
        if (count($valid_cats) > 0) {
           $hosts_filter['cats'] = $valid_cats;
        }
        $hosts_view = $this->hostsModel->getFiltered($hosts_filter);

        if (!valid_array($hosts_view)) {
            return [];
        }

        $hosts_view = $this->filter_hosts($hosts_view, $networks);
        $hosts_view = $this->format_hosts($hosts_view, $user, $ncfg);

        order($hosts_view, 'display_name');

        return $hosts_view;
    }

    /**
     *
     * @return array<string, int>
     */
    public function get_hosts_stats(): array
    {
        $ncfg = $this->ctx->get('Config');
        $total = $this->hostsModel->get_totals_stats();
        $online = $total['total_online'];
        $total['total_offline'] = $total['total_hosts'] - $online;
        if ($ncfg->get('ansible')) {
            $total['ansible_off'] = $total['ansible_enabled'] - $total['ansible_online'];
        }

        return $total;
    }
    /**
     * Filtra los hosts donde la configuracion de red esta configurada
     * para que se muestren solo los onlines
     *
     * @param array<string, mixed> $hosts_view
     * @param object $user
     * @param object $networks
     * @return array<string, mixed>
     */
    private function filter_hosts(array $hosts_view, array $networks): array
    {
        foreach ($hosts_view as $key => $host) {
            if (!empty($host['network'])) {
                $network = $networks->getNetworkById($host['network']);
                if ((int)$host['online'] === 0 && (int)$network['only_online'] === 1) {
                    unset($hosts_view[$key]);
                }
            }
        }

        return $hosts_view;
    }

    /**
     * Formatea los datos de los hosts para la vista.
     *
     * @param array<string, mixed> $hosts_view
     * @param object $user
     * @param object $ncfg
     * @return array<string, mixed>
     */
    private function format_hosts(array $hosts_view, $user, $ncfg): array
    {
        $theme = $user->getTheme();
        $lng = $this->ctx->get('lng');
        $date_now = new \DateTime('now', new \DateTimeZone('UTC'));

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
            $hosts_view[$key]['online_image'] = 'tpl/' . $theme . '/img/' . ($vhost['online'] ? 'green2.png' : 'red2.png');

            // Datos adicionales (fabricante, sistema operativo, tipo de sistema)
            $this->add_misc_data($hosts_view[$key], $vhost, $ncfg);

            // Glow (resaltado)
            $this->add_glow_tag($hosts_view[$key], $vhost, $date_now, $ncfg);

            // Alertas y advertencias
            $this->add_alerts_and_warnings($hosts_view[$key], $vhost, $theme);
        }

        // Ordenar por nombre para la vista
        order($hosts_view, 'display_name');

        return $hosts_view;
    }

    /**
     * Agrega datos adicionales como fabricante, sistema operativo y tipo de sistema.
     *
     * @param array<string, mixed> $host_view
     * @param array<string, mixed> $vhost
     * @param object $ncfg
     */
    private function add_misc_data(array &$host_view, array $vhost, $ncfg): void
    {
        if (!empty($vhost['misc']['manufacture'])) {
            $manufacture = get_manufacture_data($ncfg, $vhost['misc']['manufacture']);
            if (is_array($manufacture)) {
                $host_view['manufacture_image'] = $manufacture['manufacture_image'];
                $host_view['manufacture_name'] = $manufacture['name'];
            }
        }

        if (!empty($vhost['misc']['os'])) {
            $os = get_os_data($ncfg, $vhost['misc']['os']);
            if (is_array($os)) {
                $host_view['os_image'] = $os['os_image'];
                $host_view['os_name'] = $os['name'];
            }
        }

        if (!empty($vhost['misc']['system_type'])) {
            $system_type = get_system_type_data($ncfg, $vhost['misc']['system_type']);
            if (is_array($system_type)) {
                $host_view['system_type_image'] = $system_type['system_type_image'];
                $host_view['system_type_name'] = $system_type['name'];
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
    private function add_glow_tag(array &$host_view, array $vhost, \DateTime $date_now, $ncfg): void
    {
        $host_view['glow_tag'] = '';
        $change_time = new \DateTime($vhost['glow'], new \DateTimeZone('UTC'));
        $diff = $date_now->diff($change_time);
        $minutes_diff = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        if ($minutes_diff > 0 && $minutes_diff <= $ncfg->get('glow_time')) {
            $host_view['glow_tag'] = $vhost['online'] ? ' host-glow-green' : ' host-glow-red';
        }
    }

    /**
     * Agrega alertas y advertencias a un host.
     *
     * @param array<string, mixed> $host_view
     * @param array<string, mixed> $vhost
     * @param string $theme
     */
    private function add_alerts_and_warnings(array &$host_view, array $vhost, string $theme): void
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
}
