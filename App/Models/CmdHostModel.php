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
     * @param int $target_id
     * @return array<string, mixed>|null
     */
    public function getHostDetails(int $target_id): ?array
    {
        $query = "SELECT * FROM hosts WHERE id = :id";
        $params = ['id' => $target_id];

        return $this->db->qfetch($query, $params);
    }

    /**
     *
     * @param int $target_id
     * @return array<string, mixed>|null
     */
    public function getHostDetailsStats(int $target_id): ?array
    {
        $query = "SELECT misc FROM hosts WHERE id = :id";
        $params = ['id' => $target_id];

        return $this->db->qfetch($query, $params);
    }
    /**
     * Elimina un host.
     *
     * @param int $target_id El ID del host.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public function removeByID(int $target_id): bool
    {
        return $this->db->delete('hosts', 'id = :id', ['id' => $target_id]);
    }

    /**
     * Actualiza un host by id.
     *
     * @param int $target_id El ID del host.
     * @param array<string, string|int> $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updateByID(int $target_id, array $data): bool
    {
        return $this->db->update('hosts', $data, 'id = :id', ['id' => $target_id]);
    }

    /**
     * Crea un token para un host.
     *
     * @param int $target_id El ID del host.
     * @return bool True si se creó correctamente, False en caso contrario.
     */
    public function createHostToken(int $target_id): bool
    {
        $token = bin2hex(random_bytes(16));
        return $this->db->update('hosts', ['token' => $token], ['id' => $target_id]);
    }

    /**
     * Agrega un puerto remoto a un host.
     *
     * @param int $target_id El ID del host.
     * @param array<string, string|int> $port_details Los detalles del puerto.
     * @return bool True si se agregó correctamente, False en caso contrario.
     */
    public function addRemoteScanHostPort(int $target_id, array $port_details): bool
    {
        $port_details['hid'] = $target_id;
        !isset($port_details['service']) ? $port_details['service'] = 'unknonwn' : null;

        return $this->db->insert('ports', $port_details);
    }

    /**
     * Elimina un puerto de un host.
     *
     * @param int $target_id El ID del puerto.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public function deletePort(int $target_id): bool
    {
        return $this->db->delete('ports', 'id = :id', ['id' => $target_id]);
    }

    /**
     * Actualiza un puerto de un host.
     *
     * @param int $target_id El ID del puerto.
     * @param array<string, string|int> $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updatePort(int $target_id, array $data): bool
    {

        return $this->db->update('ports', $data, 'id = id:', ['id' => $target_id]);
    }

    /**
     * Obtiene los puertos remotos de un host.
     *
     * @param int $target_id El ID del host.
     * @return array<string, string|int> Los puertos remotos del host.
     */
    public function getRemotePorts(int $target_id): array
    {
        $query = "SELECT * FROM ports WHERE hid = :host_id AND scan_type = 1";
        $params = ['host_id' => $target_id];

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
     * @param int $target_id El ID del host.
     * @return array<string, mixed>|null La carga promedio.
     */
    public function getLoadAverage($target_id): ?array
    {
        $query = "SELECT load_avg_1min, load_avg_5min, load_avg_15min FROM stats WHERE host_id = :host_id";
        $params = ['host_id' => $target_id];

        return $this->db->qfetch($query, $params);
    }

    /**
     * Obtiene las estadísticas de I/O de un host.
     *
     * @param int $target_id El ID del host.
     * @return array<string, mixed>|null Las estadísticas de I/O.
     */
    public function getIOWaitStats($target_id): ?array
    {
        $query = "SELECT iowait FROM host_metrics WHERE host_id = :host_id";
        $params = ['host_id' => $target_id];

        return $this->db->qfetch($query, $params);
    }

    /**
     * Obtiene la información de discos de un host.
     *
     * @param int $target_id El ID del host.
     * @return array<string, string|int> La información de discos.
     */
    public function getDisksInfo($target_id): array
    {
        $query = "SELECT disk_name, disk_total, disk_used, disk_free FROM host_disks WHERE host_id = :host_id";
        $params = ['host_id' => $target_id];

        return $this->db->qfetchAll($query, $params);
    }

    /**
     * Actualiza el campo misc de un host.
     *
     * @param int $target_id El ID del host.
     * @param array<string, string|int> $misc_data Los datos misc en formato array.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updateMiscByID(int $target_id, array $misc_data): bool
    {
        return $this->db->updateJson(
            'hosts',
            'misc',
            $misc_data,
            'id = :id',
            ['id' => $target_id]
        );
    }

    /**
     * Obtiene el campo misc de un host.
     *
     * @param int $target_id El ID del host.
     * @return array<string, mixed>  Los datos misc en formato array.
     */
    public function getMisc(int $target_id): array
    {
        $query = "SELECT misc FROM hosts WHERE id = :id";
        $params = ['id' => $target_id];

        $result = $this->db->qfetch($query, $params);

        if ($result && isset($result['misc'])) {
            return json_decode($result['misc'], true);
        }

        return [];
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
