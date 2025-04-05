<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class CmdHostModel
{
    private \AppContext $ctx;
    private \DBManager $db;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get('DBManager');
    }

    /**
     *
     * @param int $hid
     * @return array<string, mixed>|null
     */
    public function getHostById(int $hid): ?array
    {
        $query = "SELECT * FROM hosts WHERE id = :id";
        $params = ['id' => $hid];

        return $this->db->qfetch($query, $params);
    }

    /**
     * Elimina un host.
     *
     * @param int $hid El ID del host.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public function removeByID(int $hid): bool
    {
        $this->db->delete('hosts', 'id = :id', ['id' => $hid]);
        $this->db->delete('notes', 'host_id = :hid', ['hid' => $hid]);
        $this->db->delete('stats', 'host_id = :hid', ['hid' => $hid]);
        $this->db->delete('hosts_logs', 'host_id = :hid', ['hid' => $hid]);
        $this->db->delete('reports', 'host_id = :hid', ['hid' => $hid]);
        $this->db->delete('ansible_msg', 'host_id = :hid', ['hid' => $hid]);
        $this->db->delete('ports', 'hid = :hid', ['hid' => $hid]);
        $this->db->delete('ansible_vars', 'hid = :hid', ['hid' => $hid]);
        $this->db->delete('tasks', 'hid = :hid', ['hid' => $hid]);

        return true;
    }

    /**
     * Actualiza un host by id.
     *
     * @param int $hid El ID del host.
     * @param array<string, string|int> $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updateByID(int $hid, array $data): bool
    {
        return $this->db->update('hosts', $data, 'id = :id', ['id' => $hid]);
    }

    /**
     * Crea un token para un host.
     *
     * @param int $hid El ID del host.
     * @return bool True si se creó correctamente, False en caso contrario.
     */
    public function createHostToken(int $hid): bool
    {
        $token = bin2hex(random_bytes(16));
        return $this->db->update('hosts', ['token' => $token], 'id = :id', ['id' => $hid]);
    }

    /**
     * Agrega un puerto remoto a un host.
     *
     * @param int $hid El ID del host.
     * @param array<string, string|int> $port_details Los detalles del puerto.
     * @return bool True si se agregó correctamente, False en caso contrario.
     */
    public function addRemoteScanHostPort(int $hid, array $port_details): bool
    {
        $port_details['hid'] = $hid;
        !isset($port_details['service']) ? $port_details['service'] = 'unknonwn' : null;

        return $this->db->insert('ports', $port_details);
    }

    /**
     *
     * @param array<string, string|int> $port_data
     * @return bool
     */
    public function addPort(array $port_data): bool
    {
        return $this->db->insert('ports', $port_data);
    }

    /**
     * Elimina un puerto de un host.
     *
     * @param int $port_id El ID del puerto.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public function deletePort(int $port_id): bool
    {
        return $this->db->delete('ports', 'id = :id', ['id' => $port_id]);
    }

    /**
     * Actualiza un puerto de un host.
     *
     * @param int $port_id El ID del puerto.
     * @param array<string, string|int> $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updatePort(int $port_id, array $data): bool
    {
        return $this->db->update('ports', $data, 'id = :id', ['id' => $port_id]);
    }

    /**
     * Obtiene los puertos remotos de un host.
     *
     * @param int $hid El ID del host.
     * @return array<string, string|int> Los puertos remotos del host.
     */
    public function getRemotePorts(int $hid): array
    {
        $query = "SELECT * FROM ports WHERE hid = :host_id AND scan_type = 1";
        $params = ['host_id' => $hid];

        return $this->db->qfetchAll($query, $params);
    }

    /**
     *
     * @param int $hid
     * @param int $scan_type
     * @return array<string,string|int>
     */
    public function getHostScanPorts(int $hid, int $scan_type = 0): array
    {
        $query = "SELECT * FROM ports WHERE hid = :hid AND scan_type = :scan_type";
        $params = ['hid' => $hid, 'scan_type' => $scan_type];

        $result = $this->db->qfetchAll($query, $params);

        if (!$result || is_bool($result)) {
            return [];
        }
        return $result;
    }

    /**
     * Obtiene las estadísticas de memoria de un host.
     *
     * @param int $target_id El ID del host.
     * @return array Las estadísticas de memoria.
     */

    /*
    public function getMemoryInfo($target_id) {
        $query = "SELECT mem_total, mem_used, mem_free FROM stats WHERE host_id = :host_id";
        $params = ['host_id' => $target_id];

        return $this->db->fetch($query, $params);
    }
    */

    /**
     * Obtiene la carga promedio de un host.
     *
     * @param int $hid El ID del host.
     * @return array<string, mixed>|null La carga promedio.
     */
    public function getLoadAverage(int $hid): ?array
    {
        $query = "SELECT load_avg_1min, load_avg_5min, load_avg_15min FROM stats WHERE host_id = :host_id";
        $params = ['host_id' => $hid];

        return $this->db->qfetch($query, $params);
    }

    /**
     * Obtiene las estadísticas de I/O de un host.
     *
     * @param int $hid El ID del host.
     * @return array<string, mixed>|null Las estadísticas de I/O.
     */
    public function getIOWaitStats(int $hid): ?array
    {
        $query = "SELECT iowait FROM host_metrics WHERE host_id = :host_id";
        $params = ['host_id' => $hid];

        return $this->db->qfetch($query, $params);
    }

    /**
     * Obtiene la información de discos de un host.
     *
     * @param int $hid El ID del host.
     * @return array<string, string|int> La información de discos.
     */
    public function getDisksInfo(int $hid): array
    {
        $query = "SELECT disk_name, disk_total, disk_used, disk_free FROM host_disks WHERE host_id = :host_id";
        $params = ['host_id' => $hid];

        return $this->db->qfetchAll($query, $params);
    }

    /**
     * Actualiza el campo misc de un host.
     *
     * @param int $hid El ID del host.
     * @param array<string, string|int> $misc_data Los datos misc en formato array.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updateMiscByID(int $hid, array $misc_data): bool
    {
        return $this->db->updateJson(
            'hosts',
            'misc',
            $misc_data,
            'id = :id',
            ['id' => $hid]
        );
    }

    /**
     * Obtiene el campo misc de un host.
     *
     * @param int $hid El ID del host.
     * @return array<string, string>|null
     */
    public function getMiscById(int $hid): ?array
    {
        $query = "SELECT misc FROM hosts WHERE id = :id";
        $params = ['id' => $hid];

        return $this->db->qfetch($query, $params);
    }

    /**
     *
     * @return array<string, string|int>
     */
    public function getAlertOn(): array
    {

        $query = "SELECT * FROM hosts WHERE alert = :alert";
        $params = ['alert' => 1];

        $results = $this->db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    /**
     *
     * @return array<string, string|int>
     */
    public function getWarnOn(): array
    {
        $query = "SELECT * FROM hosts WHERE warn = :warn";
        $params = ['warn' => 1];

        $results = $this->db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    /**
     *
     * @return array<string, string|int>
     */
    public function getAgentsHosts(): array
    {
        $query = 'SELECT * FROM hosts WHERE agent_installed = :true';
        $params = ['true' => 1];

        $results = $this->db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    /**
     *
     * @param int $status
     * @return array<string, string|int>
     */
    public function getAgentsByStatus(int $status): array
    {
        $query = 'SELECT * FROM hosts WHERE agent_installed = :true AND online = :online';
        if ($status === 1 || $status === 2) {
            $params = ['true' => 1, 'online' => $status];
        }
        //TODO: agent_missing_pings [misc]
        $results = $this->db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    /**
     *
     * @return array<string, string|int>
     */
    public function getAnsibleHosts(): array
    {
        $query = 'SELECT * FROM hosts WHERE ansible_enabled = :true';
        $params = ['true' => 1];

        $results = $this->db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    /**
     *
     * @return bool
     */
    public function clearAllAlerts(): bool
    {
        $condition = 'alert = :alert_on';

        return $this->db->update('hosts', ['alert' => 0], $condition, ['alert_on' => 1]);
    }
    /**
     *
     * @return bool
     */
    public function clearAllWarns(): bool
    {
        $condition = 'warn = :warn_on';

        return $this->db->update('hosts', ['warn' => 0], $condition, ['warn_on' => 1]);
    }
}
