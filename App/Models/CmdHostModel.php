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

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function getHostDetails($target_id)
    {
        $db = $this->ctx->get('DBManager');
        $query = "SELECT * FROM hosts WHERE id = :id";
        $params = ['id' => $target_id];

        $hostDetails = $db->qfetch($query, $params);

        if ($hostDetails) {
            if (isset($hostDetails['misc'])) {
                $hostDetails['misc'] = json_decode($hostDetails['misc'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $hostDetails['misc'] = [];
                }
            } else {
                $hostDetails['misc'] = [];
            }
        }

        return $hostDetails;
    }
    /**
     * Elimina un host.
     *
     * @param int $target_id El ID del host.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public function remove($target_id)
    {
        $db = $this->ctx->get('DBManager');
        return $db->delete('hosts', ['id' => $target_id]);
    }

    /**
     * Actualiza un host.
     *
     * @param int $target_id El ID del host.
     * @param array $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function update($target_id, $data)
    {
        $db = $this->ctx->get('DBManager');
        return $db->update('hosts', $data, ['id' => $target_id]);
    }

    /**
     * Crea un token para un host.
     *
     * @param int $target_id El ID del host.
     * @return bool True si se creó correctamente, False en caso contrario.
     */
    public function createHostToken($target_id)
    {
        $db = $this->ctx->get('DBManager');
        $token = bin2hex(random_bytes(16));
        return $db->update('hosts', ['token' => $token], ['id' => $target_id]);
    }

    /**
     * Agrega un puerto remoto a un host.
     *
     * @param int $target_id El ID del host.
     * @param array $port_details Los detalles del puerto.
     * @return bool True si se agregó correctamente, False en caso contrario.
     */
    public function addRemoteScanHostPort($target_id, $port_details)
    {
        $db = $this->ctx->get('DBManager');
        $port_details['host_id'] = $target_id;
        return $db->insert('ports', $port_details);
    }

    /**
     * Elimina un puerto de un host.
     *
     * @param int $target_id El ID del puerto.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public function deletePort($target_id)
    {
        $db = $this->ctx->get('DBManager');
        return $db->delete('ports', ['id' => $target_id]);
    }

    /**
     * Actualiza un puerto de un host.
     *
     * @param int $target_id El ID del puerto.
     * @param array $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updatePort($target_id, $data)
    {
        $db = $this->ctx->get('DBManager');
        return $db->update('ports', $data, ['id' => $target_id]);
    }

    /**
     * Obtiene los puertos remotos de un host.
     *
     * @param int $target_id El ID del host.
     * @return array Los puertos remotos del host.
     */
    public function getRemotePorts($target_id)
    {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT * FROM ports WHERE host_id = :host_id AND remote_scan = 1";
        $params = ['host_id' => $target_id];

        return $db->qfetchAll($query, $params);
    }

    /**
     * Obtiene las estadísticas de memoria de un host.
     *
     * @param int $target_id El ID del host.
     * @return array Las estadísticas de memoria.
     */

    /*
    public function getMemoryInfo($target_id) {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT mem_total, mem_used, mem_free FROM stats WHERE host_id = :host_id";
        $params = ['host_id' => $target_id];

        return $db->fetch($query, $params);
    }
    */

    /**
     * Obtiene la carga promedio de un host.
     *
     * @param int $target_id El ID del host.
     * @return array La carga promedio.
     */
    public function getLoadAverage($target_id)
    {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT load_avg_1min, load_avg_5min, load_avg_15min FROM stats WHERE host_id = :host_id";
        $params = ['host_id' => $target_id];

        return $db->qfetch($query, $params);
    }

    /**
     * Obtiene las estadísticas de I/O de un host.
     *
     * @param int $target_id El ID del host.
     * @return array Las estadísticas de I/O.
     */
    public function getIOWaitStats($target_id)
    {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT iowait FROM host_metrics WHERE host_id = :host_id";
        $params = ['host_id' => $target_id];

        return $db->qfetch($query, $params);
    }

    /**
     * Obtiene la información de discos de un host.
     *
     * @param int $target_id El ID del host.
     * @return array La información de discos.
     */
    public function getDisksInfo($target_id)
    {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT disk_name, disk_total, disk_used, disk_free FROM host_disks WHERE host_id = :host_id";
        $params = ['host_id' => $target_id];

        return $db->qfetchAll($query, $params);
    }

    /**
     * Actualiza el campo misc de un host.
     *
     * @param int $target_id El ID del host.
     * @param array $misc_data Los datos misc en formato array.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updateMisc($target_id, $misc_data)
    {
        $db = $this->ctx->get('DBManager');

        // Validar que los datos sean un array
        if (!is_array($misc_data)) {
            throw new \InvalidArgumentException('Los datos misc deben ser un array');
        }

        // Convertir el array a JSON
        $misc_json = json_encode($misc_data);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Error al codificar los datos misc a JSON');
        }

        // Actualizar el campo misc
        return $db->update('hosts', ['misc' => $misc_json], ['id' => $target_id]);
    }

    /**
     * Obtiene el campo misc de un host.
     *
     * @param int $target_id El ID del host.
     * @return array Los datos misc en formato array.
     */
    public function getMisc($target_id)
    {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT misc FROM hosts WHERE id = :id";
        $params = ['id' => $target_id];

        $result = $db->qfetch($query, $params);

        if ($result && isset($result['misc'])) {
            return json_decode($result['misc'], true);
        }

        return [];
    }

    public function getAlertOn() {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT * FROM hosts WHERE alert = :alert";
        $params = ['alert' => 1];

        $results = $db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    public function getWarnOn() {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT * FROM hosts WHERE warn = :warn";
        $params = ['warn' => 1];

        $results = $db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    public function getAgentsHosts() {
        $db = $this->ctx->get('DBManager');

        $query = 'SELECT * FROM hosts WHERE agent_installed = :true';
        $params = ['true' => 1];

        $results = $db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    public function getAgentsByStatus(int $status) {
        $db = $this->ctx->get('DBManager');

        $query = 'SELECT * FROM hosts WHERE agent_installed = :true AND online = :online';
        if ($status === 1 || $status === 2) {
            $params = ['true' => 1, 'online' => $status];
        }
        //TODO: agent_missing_pings [misc]
        $results = $db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    public function getAnsibleHosts() {
        $db = $this->ctx->get('DBManager');

        $query = 'SELECT * FROM hosts WHERE ansible_enabled = :true';
        $params = ['true' => 1];

        $results = $db->qfetchAll($query, $params);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }
}
