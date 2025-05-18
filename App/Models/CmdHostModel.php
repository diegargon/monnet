<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

use App\Core\AppContext;
use App\Core\DBManager;

class CmdHostModel
{
    private DBManager $db;

    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieves a host by its ID.
     *
     * @param int $hid The ID of the host.
     * @return array<string, mixed>|null The host data or null if not found.
     * @throws \RuntimeException If the query fails.
     */
    public function getHostById(int $hid): ?array
    {
        try {
            $query = "SELECT * FROM hosts WHERE id = :id";
            $params = ['id' => $hid];
            return $this->db->qfetch($query, $params);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error fetching host by ID: " . $e->getMessage(), 0, $e);
        }
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
     * Updates a host by its ID.
     *
     * @param int $hid The ID of the host.
     * @param array<string, mixed> $data The data to update.
     * @return bool True if the update was successful, false otherwise.
     * @throws \RuntimeException If the update fails.
     */
    public function updateByID(int $hid, array $data): bool
    {
        try {
            return $this->db->update('hosts', $data, 'id = :id', ['id' => $hid]);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error updating host by ID: " . $e->getMessage(), 0, $e);
        }
    }

    public function update(array $field, string $condition, $params): bool
    {
        return $this->db->update('hosts', $field, $condition, $params);
    }

    /**
     * Insert a host token
     *
     * @param int $hid El ID del host.
     * @param string token
     * @return bool True si se creó correctamente, False en caso contrario.
     */
    public function submitHostToken(int $hid, string $token): bool
    {
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
     * Adds a port to the database.
     *
     * @param array<string, mixed> $port_data The port data to insert.
     * @return bool True if the insertion was successful, false otherwise.
     * @throws \RuntimeException If the insertion fails.
     */
    public function addPort(array $port_data): bool
    {
        try {
            return $this->db->insert('ports', $port_data);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error adding port: " . $e->getMessage(), 0, $e);
        }
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
     * @throws \RuntimeException Si la actualización falla.
     */
    public function updatePort(int $port_id, array $data): bool
    {
        try {
            return $this->db->update('ports', $data, 'id = :id', ['id' => $port_id]);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error updating port: " . $e->getMessage(), 0, $e);
        }
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
        $success = $this->db->updateJson(
            'hosts',
            'misc',
            $misc_data,
            'id = :id',
            ['id' => $hid]
        );

        if ($success) {
            $this->db->commit();
        }

        return $success;
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
     * @return array<string, mixed>
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
     * @return array<string, mixed>
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
     * @return array<string, mixed>
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
     * @return array<string, mixed>
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
     * @return array<string, mixed>
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
