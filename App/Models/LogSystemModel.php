<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class LogSystemModel
{
    private \DBManager $db;

    public function __construct(\DBManager $db)
    {
        $this->db = $db;
    }

    /**
     *
     * @param array<string, mixed> $data
     * @return bool
     */
    public function insert(array $data): bool
    {
        return $this->db->insert('system_logs', $data);
    }

    /**
     * Get system logs
     *
     * @param array<string, mixed> $opts
     * @return array<int, array<string, mixed>> Array of log entries
     */
    public function getSystemDBLogs(array $opts): array
    {
        $conditions = [];
        $params = [];

        $query = 'SELECT * FROM system_logs';

        if (!empty($opts['level'])) {
            $conditions[] = 'level <= :level';
            $params[':level'] = (int) $opts['level'];
        }

        $query .= ' WHERE ' . implode(' AND ', $conditions);

        $query .= ' ORDER BY date DESC';

        if (!empty($opts['limit'])) {
            $query .= ' LIMIT ' . (int) $opts['limit'];
        }

        return $this->db->qfetchAll($query, $params);
    }
}
