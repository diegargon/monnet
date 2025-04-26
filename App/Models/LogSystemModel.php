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
     * Get system logs filtered by level with a maximum number of results
     *
     * @param int $level Maximum log level to retrieve
     * @param int $max Maximum number of logs to return
     * @return array<int, array<string, mixed>> Array of log entries
     */
    public function getSystemDBLogs(int $level, int $max): array
    {
        return $this->db->select(
            'system_logs',
            ['*'],
            'level <= :level',
            ['level' => $level],
            $max
        );
    }
}
