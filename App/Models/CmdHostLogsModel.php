<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class CmdHostLogsModel
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    /**
     * Update...
     *
     * @param int $target_id El ID del host.
     * @param array $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updateByID(int $target_id, array $data): bool
    {
        $db = $this->ctx->get('DBManager');
        return $db->update('hosts_logs', $data, 'id = :id', ['id' => $target_id]);
    }

    /**
     * Return logs based on [$opt]ions
     * @param array<string,mixed> $opts
     * @return array<string,mixed>
     */
    public function getLogsHosts(array $opts = []): array
    {
        $conditions = [];
        $params = [];

        $query = 'SELECT * FROM hosts_logs';

        if (!empty($opts['level'])) {
            $conditions[] = 'level <= :level';
            $params[':level'] = (int) $opts['level'];
        }

        if (!empty($opts['ack'])) {
            $conditions[] = 'ack >= 0';
        } else {
            $conditions[] = 'ack != 1';
        }

        if (isset($opts['host_id'])) {
            $conditions[] = 'host_id = :host_id';
            $params[':host_id'] = (int) $opts['host_id'];
        }

        if (!empty($opts['log_type']) && is_array($opts['log_type'])) {
            $logConditions = [];
            foreach ($opts['log_type'] as $index => $l_type) {
                $key = ":log_type_$index";
                $logConditions[] = "log_type = $key";
                $params[$key] = (int) $l_type;
            }
            $conditions[] = '(' . implode(' OR ', $logConditions) . ')';
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY date DESC';

        if (!empty($opts['limit'])) {
            $query .= ' LIMIT ' . (int) $opts['limit'];
        }

        $db = $this->ctx->get('DBManager');
        return $db->qfetchAll($query, $params);
    }

    /**
     * Obtiene los logs de un host.
     *
     * @param int $host_id El ID del host.
     * @return array Los logs del host.
     */
    public function getHostLogs($host_id) {
        $db = $this->ctx->get('DBManager');
        $query = "SELECT * FROM host_logs WHERE host_id = :host_id ORDER BY date DESC LIMIT 100";
        $params = ['host_id' => $host_id];

        return $db->fetchAll($query, $params);
    }
}
