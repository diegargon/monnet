<?php

/**
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
 * Model for managing host logs in the database.
 *
 * Tabla: hosts_logs
 *  - id: int, PRIMARY KEY, AUTO_INCREMENT
 *  - host_id: int, INDEX
 *  - level: tinyint, INDEX, default 7
 *  - log_type: varchar(255), default 0; valores: 0=DEFAULT, 1=EVENT, 2=EVENT_WARN, 3=EVENT_ALERT, 4=BITACORA
 *  - event_type: smallint, nullable, default 0
 *  - msg: char(255), NOT NULL
 *  - ack: tinyint(1), default 0
 *  - date: datetime, INDEX, default CURRENT_TIMESTAMP
 *
 */

namespace App\Models;

use App\Core\DBManager;

class LogHostsModel
{
    private DBManager $db;

    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    /**
     * Add a new log entry to the database.
     *
     * @param array<string, string|int> $data Log data to insert.
     * @return bool True if the log was added successfully, false otherwise.
     */
    public function insert(array $data): bool
    {
        return $this->db->insert('hosts_logs', $data);
    }

    /**
     * Update a log entry by ID.
     *
     * @param int $target_id Log ID.
     * @param array<string, string|int> $data Data to update.
     * @return bool True if updated successfully, false otherwise.
     */
    public function updateByID(int $target_id, array $data): bool
    {
        return $this->db->update('hosts_logs', $data, 'id = :id', ['id' => $target_id]);
    }

    /**
     * Return logs based on the provided options.
     *
     * @param array<string,mixed> $opts Filter options.
     * @return array<int, array<string, mixed>> List of logs, each log is an associative array.
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

        if (!empty($opts['show_ack'])) {
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

        $query .= ' WHERE ' . implode(' AND ', $conditions);

        $query .= ' ORDER BY date DESC';

        if (!empty($opts['limit']) && is_numeric($opts['limit'])) {
            $query .= ' LIMIT ' . (int) $opts['limit'];
        }

        return $this->db->qfetchAll($query, $params);
    }

    /**
     * Devuelve las últimas entradas de bitácora para un host.
     *
     * @param int $host_id
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getBitacoraEntries(int $host_id, int $limit = 50): array
    {
        $query = "SELECT * FROM hosts_logs WHERE host_id = :host_id AND log_type = 4 ORDER BY date DESC LIMIT :limit";
        $params = [
            ':host_id' => $host_id,
            ':limit' => $limit
        ];
        // Algunos drivers no permiten LIMIT como parámetro, así que lo forzamos como entero
        $query = str_replace(':limit', (int)$limit, $query);
        return $this->db->qfetchAll($query, [':host_id' => $host_id]);
    }
}
